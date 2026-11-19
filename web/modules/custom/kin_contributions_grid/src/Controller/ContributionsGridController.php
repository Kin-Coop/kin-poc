<?php

  namespace Drupal\kin_contributions_grid\Controller;

  use Drupal\Core\Access\AccessResult;
  use Drupal\Core\Access\AccessResultInterface;
  use Drupal\Core\Controller\ControllerBase;
  use Drupal\Core\Session\AccountInterface;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

  /**
   * Shows a group's transactions as a member-by-period grid.
   *
   * Table and column names are taken from the existing "contributions" view:
   * - civicrm_value_kin_contribut_9.household_25: the group (a Household
   *   contact) that a transaction belongs to.
   * - civicrm_value_kin_contribut_9.note_65: the transaction's note.
   * - civicrm_value_group_reward_31.reward_type_83: the request (gift) type.
   *
   * Gifts out are stored as negative amounts (the view's "Gift or
   * Contribution" field checks for a minus sign), so every total is a plain
   * sum: money in minus money out.
   */
  class ContributionsGridController extends ControllerBase {

    /**
     * CiviCRM contribution status "Completed": the only status counted.
     */
    const STATUS_COMPLETED = 1;

    /**
     * Custom field ID of the request (gift) type.
     */
    const REQUEST_TYPE_FIELD_ID = 83;

    /**
     * Relationship types that make someone a member of a group.
     *
     * Matched on the relationship type's name or label (person = contact A,
     * group's Household = contact B). If none of these exist, everyone who has
     * ever paid into or out of the group is treated as a member instead.
     */
    const MEMBER_RELATIONSHIPS = ['Household Member of', 'Household Admin of'];

    /**
     * Number of weeks or months shown at a time.
     */
    const PERIODS_SHOWN = 6;

    /**
     * Per-request cache of member and contributor IDs, keyed by group.
     *
     * @var array
     */
    protected $memo = [];

    /**
     * Access: CiviCRM admins, or anyone who belongs to or has paid into the group.
     */
    public function access(AccountInterface $account, $household_id): AccessResultInterface {
      if ($account->hasPermission('administer CiviCRM')) {
        return AccessResult::allowed()->cachePerPermissions();
      }
      if ($account->isAnonymous()) {
        return AccessResult::forbidden()->cachePerUser();
      }
      $this->initCivi();
      $household_id = (int) $household_id;
      $contact_id = (int) \CRM_Core_BAO_UFMatch::getContactId($account->id());
      $allowed = $contact_id > 0 && (
          in_array($contact_id, $this->memberIds($household_id), TRUE)
          || in_array($contact_id, $this->everContributorIds($household_id), TRUE)
        );
      return AccessResult::allowedIf($allowed)->cachePerUser()->setCacheMaxAge(0);
    }

    /**
     * Builds the page.
     */
    public function page(Request $request, $household_id): array {
      $this->initCivi();
      $household_id = (int) $household_id;

      $household = \Civi\Api4\Contact::get(FALSE)
                                     ->addSelect('display_name')
                                     ->addWhere('id', '=', $household_id)
                                     ->addWhere('contact_type', '=', 'Household')
                                     ->execute()
                                     ->first();
      if (!$household) {
        throw new NotFoundHttpException();
      }

      // Filters, read from the URL.
      $period = $request->query->get('period') === 'month' ? 'month' : 'week';
      $contributor = trim((string) $request->query->get('contributor', ''));
      $request_type = (string) $request->query->get('request_type', '');
      $to_input = (string) $request->query->get('to', '');

      // The columns: 6 whole weeks (Monday start) or months, ending with the
      // period that contains the "Up to" date (default: today). No future.
      $today = new \DateTimeImmutable('today');
      $current = $this->periodStart($today, $period);
      $to_date = $this->parseDate($to_input);
      $last = $to_date ? $this->periodStart($to_date, $period) : $current;
      if ($last > $current) {
        $last = $current;
      }
      $first = $this->shift($last, $period, -(self::PERIODS_SHOWN - 1));

      $current_key = $current->format('Y-m-d');
      $periods = [];
      for ($d = $first; $d <= $last; $d = $this->shift($d, $period, 1)) {
        $key = $d->format('Y-m-d');
        $periods[$key] = [
          'key' => $key,
          'label' => $period === 'week' ? 'w/c ' . $d->format('d.m.y') : $d->format('M Y'),
          'current' => $key === $current_key,
        ];
      }

      $end = $this->shift($last, $period, 1);

      // Earlier / Later links step 6 periods at a time, keeping the filters.
      $base_query = array_filter([
        'contributor' => $contributor,
        'request_type' => $request_type,
        'period' => $period,
      ], fn($value) => $value !== '');
      $earlier_query = $base_query + [
          'to' => $this->shift($last, $period, -self::PERIODS_SHOWN)->format('Y-m-d'),
        ];
      $later_query = NULL;
      if ($last < $current) {
        $later_last = $this->shift($last, $period, self::PERIODS_SHOWN);
        $later_query = $later_last >= $current
          ? $base_query
          : $base_query + ['to' => $later_last->format('Y-m-d')];
      }

      // The transactions in range, oldest first.
      $sql = "SELECT c.id, c.contact_id, c.receive_date, c.total_amount,
        k.note_65 AS note, r.reward_type_83 AS request_type
      FROM civicrm_contribution c
      INNER JOIN civicrm_value_kin_contribut_9 k ON k.entity_id = c.id
      LEFT JOIN civicrm_value_group_reward_31 r ON r.entity_id = c.id
      WHERE k.household_25 = %1
        AND c.contribution_status_id = %2
        AND c.receive_date >= %3
        AND c.receive_date < %4";
      $params = [
        1 => [$household_id, 'Integer'],
        2 => [self::STATUS_COMPLETED, 'Integer'],
        3 => [$first->format('Y-m-d H:i:s'), 'String'],
        4 => [$end->format('Y-m-d H:i:s'), 'String'],
      ];
      if ($request_type !== '') {
        $sql .= " AND r.reward_type_83 = %5";
        $params[5] = [$request_type, 'String'];
      }
      $sql .= " ORDER BY c.receive_date, c.id";
      $dao = \CRM_Core_DAO::executeQuery($sql, $params);

      $types = $this->requestTypeOptions();
      $cells = [];
      $in_range = [];
      while ($dao->fetch()) {
        $date = new \DateTimeImmutable($dao->receive_date);
        $key = $this->periodStart($date, $period)->format('Y-m-d');
        $amount = (float) $dao->total_amount;
        $type_value = trim((string) $dao->request_type, \CRM_Core_DAO::VALUE_SEPARATOR);
        $cid = (int) $dao->contact_id;
        $cells[$cid][$key][] = [
          'amount' => $amount,
          'date' => $date->format('d/m'),
          'type' => $types[$type_value] ?? $type_value,
          'note' => trim((string) $dao->note),
          'gift' => $amount < 0,
        ];
        $in_range[$cid] = TRUE;
      }

      // Real totals: everything each person has ever paid in or taken out,
      // not just the periods on screen. Same filters as the history page.
      $all_time = $this->allTimeTotals($household_id, $request_type);

      // The rows: every member, plus anyone else with a transaction in range.
      $member_ids = $this->memberIds($household_id);
      $using_relationships = !empty($member_ids);
      if (!$using_relationships) {
        $member_ids = $this->everContributorIds($household_id);
      }
      $row_ids = array_values(array_unique(array_merge($member_ids, array_keys($in_range))));
      $names = $this->displayNames(array_values(array_unique(array_merge($row_ids, array_keys($all_time)))));
      $matches = fn($cid) => $contributor === ''
                             || mb_stripos($names[$cid] ?? ('Contact ' . $cid), $contributor) !== FALSE;

      $rows = [];
      $column_sums = array_fill_keys(array_keys($periods), 0.0);
      foreach ($row_ids as $cid) {
        if (!$matches($cid)) {
          continue;
        }
        $row_cells = [];
        foreach ($cells[$cid] ?? [] as $key => $items) {
          foreach ($items as $item) {
            $column_sums[$key] += $item['amount'];
          }
          $row_cells[$key] = array_map(fn($item) => ['amount' => $this->money($item['amount'])] + $item, $items);
        }
        $rows[] = [
          'name' => $names[$cid] ?? ('Contact ' . $cid),
          'left' => $using_relationships && !in_array($cid, $member_ids, TRUE),
          'cells' => $row_cells,
          'total' => $this->money($all_time[$cid] ?? 0.0),
        ];
      }
      usort($rows, fn($a, $b) => strcasecmp($a['name'], $b['name']));

      // The group's real total includes former members who aren't shown as rows.
      $grand = 0.0;
      foreach ($all_time as $cid => $sum) {
        if ($matches($cid)) {
          $grand += $sum;
        }
      }

      $column_totals = array_map(fn($sum) => round($sum, 2) == 0 ? '' : $this->money($sum), $column_sums);

      return [
        '#title' => $household['display_name'] . ': contributions by date',
        '#theme' => 'kin_contributions_grid',
        '#household_id' => $household_id,
        '#periods' => array_values($periods),
        '#rows' => $rows,
        '#column_totals' => $column_totals,
        '#grand_total' => $this->money($grand),
        '#range_label' => $first->format('j M Y') . ' – ' . $end->modify('-1 day')->format('j M Y'),
        '#earlier_query' => $earlier_query,
        '#later_query' => $later_query,
        '#filters' => [
          'contributor' => $contributor,
          'request_type' => $request_type,
          'period' => $period,
          'to' => $to_date ? $to_input : '',
        ],
        '#request_types' => $types,
        '#attached' => ['library' => ['kin_contributions_grid/grid']],
        '#cache' => ['max-age' => 0],
      ];
    }

    /**
     * Starts CiviCRM so its API and database classes can be used.
     */
    protected function initCivi(): void {
      \Drupal::service('civicrm')->initialize();
    }

    /**
     * Each contact's all-time net total for the group, keyed by contact ID.
     */
    protected function allTimeTotals(int $household_id, string $request_type): array {
      $sql = "SELECT c.contact_id, SUM(c.total_amount) AS total
      FROM civicrm_contribution c
      INNER JOIN civicrm_value_kin_contribut_9 k ON k.entity_id = c.id
      LEFT JOIN civicrm_value_group_reward_31 r ON r.entity_id = c.id
      WHERE k.household_25 = %1
        AND c.contribution_status_id = %2";
      $params = [
        1 => [$household_id, 'Integer'],
        2 => [self::STATUS_COMPLETED, 'Integer'],
      ];
      if ($request_type !== '') {
        $sql .= " AND r.reward_type_83 = %3";
        $params[3] = [$request_type, 'String'];
      }
      $sql .= " GROUP BY c.contact_id";
      $dao = \CRM_Core_DAO::executeQuery($sql, $params);
      $totals = [];
      while ($dao->fetch()) {
        $totals[(int) $dao->contact_id] = (float) $dao->total;
      }
      return $totals;
    }

    /**
     * Contact IDs of current members, through household relationships.
     */
    protected function memberIds(int $household_id): array {
      if (!isset($this->memo['members'][$household_id])) {
        $ids = [];
        $type_ids = \Civi\Api4\RelationshipType::get(FALSE)
                                               ->addSelect('id')
                                               ->addClause('OR',
                                                 ['name_a_b', 'IN', self::MEMBER_RELATIONSHIPS],
                                                 ['label_a_b', 'IN', self::MEMBER_RELATIONSHIPS]
                                               )
                                               ->execute()
                                               ->column('id');
        if ($type_ids) {
          $result = \Civi\Api4\Relationship::get(FALSE)
                                           ->addSelect('contact_id_a')
                                           ->addWhere('contact_id_b', '=', $household_id)
                                           ->addWhere('relationship_type_id', 'IN', $type_ids)
                                           ->addWhere('is_active', '=', TRUE)
                                           ->execute();
          foreach ($result as $relationship) {
            $ids[] = (int) $relationship['contact_id_a'];
          }
        }
        $this->memo['members'][$household_id] = array_values(array_unique($ids));
      }
      return $this->memo['members'][$household_id];
    }

    /**
     * Contact IDs of everyone who has ever paid into or out of the group.
     */
    protected function everContributorIds(int $household_id): array {
      if (!isset($this->memo['contributors'][$household_id])) {
        $ids = [];
        $dao = \CRM_Core_DAO::executeQuery(
          "SELECT DISTINCT c.contact_id
          FROM civicrm_contribution c
          INNER JOIN civicrm_value_kin_contribut_9 k ON k.entity_id = c.id
          WHERE k.household_25 = %1",
          [1 => [$household_id, 'Integer']]
        );
        while ($dao->fetch()) {
          $ids[] = (int) $dao->contact_id;
        }
        $this->memo['contributors'][$household_id] = $ids;
      }
      return $this->memo['contributors'][$household_id];
    }

    /**
     * Display names keyed by contact ID.
     */
    protected function displayNames(array $ids): array {
      $names = [];
      if ($ids) {
        $result = \Civi\Api4\Contact::get(FALSE)
                                    ->addSelect('id', 'display_name')
                                    ->addWhere('id', 'IN', $ids)
                                    ->execute();
        foreach ($result as $contact) {
          $names[(int) $contact['id']] = $contact['display_name'];
        }
      }
      return $names;
    }

    /**
     * Request type labels keyed by stored value.
     */
    protected function requestTypeOptions(): array {
      $options = [];
      $field = \Civi\Api4\CustomField::get(FALSE)
                                     ->addSelect('option_group_id')
                                     ->addWhere('id', '=', self::REQUEST_TYPE_FIELD_ID)
                                     ->execute()
                                     ->first();
      if (!empty($field['option_group_id'])) {
        $result = \Civi\Api4\OptionValue::get(FALSE)
                                        ->addSelect('value', 'label')
                                        ->addWhere('option_group_id', '=', $field['option_group_id'])
                                        ->addWhere('is_active', '=', TRUE)
                                        ->addOrderBy('weight')
                                        ->execute();
        foreach ($result as $option) {
          $options[(string) $option['value']] = $option['label'];
        }
      }
      return $options;
    }

    /**
     * Reads a Y-m-d date from the URL, or NULL if it isn't one.
     */
    protected function parseDate(string $value): ?\DateTimeImmutable {
      $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
      return $date ?: NULL;
    }

    /**
     * The Monday (or the 1st of the month) on or before a date.
     */
    protected function periodStart(\DateTimeImmutable $date, string $period): \DateTimeImmutable {
      $date = $date->setTime(0, 0);
      if ($period === 'month') {
        return $date->modify('first day of this month');
      }
      return $date->modify('-' . ((int) $date->format('N') - 1) . ' days');
    }

    /**
     * Moves a period start forwards or backwards by whole periods.
     */
    protected function shift(\DateTimeImmutable $date, string $period, int $count): \DateTimeImmutable {
      return $date->modify(($count >= 0 ? '+' : '') . $count . ($period === 'month' ? ' months' : ' weeks'));
    }

    /**
     * Formats an amount as £50.00 or −£30.00.
     */
    protected function money(float $amount): string {
      $amount = round($amount, 2);
      return ($amount < 0 ? '−' : '') . '£' . number_format(abs($amount), 2);
    }

  }
