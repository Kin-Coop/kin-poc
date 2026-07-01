<?php

  use CRM_Kinrules_ExtensionUtil as E;

  /**
   * Builds a flat CSV export of all CiviRules rules, one row per condition
   * and per action (with the rule's core data repeated on every row).
   */
  class CRM_Kinrules_Export {

    /**
     * Column order for the CSV.
     */
    const COLUMNS = [
      'rule_id',
      'label',
      'name',
      'is_enabled',
      'trigger',
      'tags',
      'help_text',
      'description',
      'created_date',
      'modified_date',
      'row_type',
      'item_id',
      'item_name',
      'item_label',
      'item_human_readable',
    ];

    /**
     * Generate the CSV as a string.
     *
     * @return string
     */
    public static function toCsv() {
      $rules = self::getRules();

      $fh = fopen('php://temp', 'r+');
      fputcsv($fh, self::COLUMNS);

      foreach ($rules as $rule) {
        $base = [
          'rule_id'       => $rule['id'],
          'label'         => $rule['label'],
          'name'          => $rule['name'],
          'is_enabled'    => $rule['is_active'] ? 'Enabled' : 'Disabled',
          'trigger'       => $rule['trigger_label'],
          'tags'          => $rule['tags'],
          'help_text'     => $rule['help_text'],
          'description'   => $rule['description'],
          'created_date'  => $rule['created_date'],
          'modified_date' => $rule['modified_date'],
        ];

        $conditions = self::getConditions($rule['id']);
        $actions = self::getActions($rule['id']);

        // If a rule somehow has no conditions and no actions, still emit one
        // row so the rule appears in the export.
        if (empty($conditions) && empty($actions)) {
          self::writeRow($fh, $base, 'rule', NULL, NULL, NULL, NULL);
          continue;
        }

        foreach ($conditions as $c) {
          self::writeRow($fh, $base, 'condition', $c['id'], $c['name'], $c['label'], $c['human_readable']);
        }
        foreach ($actions as $a) {
          self::writeRow($fh, $base, 'action', $a['id'], $a['name'], $a['label'], $a['human_readable']);
        }
      }

      rewind($fh);
      $csv = stream_get_contents($fh);
      fclose($fh);
      return $csv;
    }

    /**
     * Write a single CSV row, mapping the associative arrays onto COLUMNS.
     */
    private static function writeRow($fh, array $base, $rowType, $itemId, $itemName, $itemLabel, $human) {
      $row = $base + [
          'row_type'            => $rowType,
          'item_id'             => $itemId,
          'item_name'           => $itemName,
          'item_label'          => $itemLabel,
          'item_human_readable' => $human,
        ];
      // Re-order to match COLUMNS exactly.
      $ordered = [];
      foreach (self::COLUMNS as $col) {
        $ordered[] = $row[$col] ?? '';
      }
      fputcsv($fh, $ordered);
    }

    /**
     * Fetch all rules with trigger label and concatenated tags, ordered by
     * enabled, then trigger, then tag, then id.
     *
     * @return array
     */
    public static function getRules() {
      // Concatenate tag labels per rule via GROUP_CONCAT so we can both display
      // and sort by them. CiviRules tags are stored as option values: the
      // civirule_rule_tag.rule_tag_id column holds the civicrm_option_value
      // *value* (not its id) within the 'civirule_rule_tag' option group, and
      // the human-readable tag is that option value's label.
      $sql = "
      SELECT
        r.id              AS id,
        r.label           AS label,
        r.name            AS name,
        r.help_text       AS help_text,
        r.description     AS description,
        r.created_date    AS created_date,
        r.modified_date   AS modified_date,
        r.is_active       AS is_active,
        t.label           AS trigger_label,
        GROUP_CONCAT(DISTINCT ov.label ORDER BY ov.label SEPARATOR ', ') AS tags
      FROM civirule_rule r
      LEFT JOIN civirule_trigger t
        ON r.trigger_id = t.id
      LEFT JOIN civirule_rule_tag rt
        ON rt.rule_id = r.id
      LEFT JOIN civicrm_option_group og
        ON og.name = 'civirule_rule_tag'
      LEFT JOIN civicrm_option_value ov
        ON ov.value = rt.rule_tag_id
       AND ov.option_group_id = og.id
      GROUP BY r.id
      ORDER BY
        r.is_active DESC,
        t.label ASC,
        tags ASC,
        r.id ASC
    ";

      $dao = CRM_Core_DAO::executeQuery($sql);
      $rules = [];
      while ($dao->fetch()) {
        $rules[] = [
          'id'            => $dao->id,
          'label'         => $dao->label,
          'name'          => $dao->name,
          'help_text'     => $dao->help_text,
          'description'   => $dao->description,
          'created_date'  => $dao->created_date,
          'modified_date' => $dao->modified_date,
          'is_active'     => $dao->is_active,
          'trigger_label' => $dao->trigger_label,
          'tags'          => $dao->tags ?? '',
        ];
      }
      return $rules;
    }

    /**
     * Fetch the conditions for a rule, with a human-readable description for
     * each produced by the condition class itself.
     *
     * @param int $ruleId
     * @return array
     */
    public static function getConditions($ruleId) {
      $sql = "
      SELECT
        rc.id        AS rule_condition_id,
        rc.condition_link AS condition_link,
        c.id         AS condition_id,
        c.name       AS condition_name,
        c.label      AS condition_label,
        c.class_name AS class_name
      FROM civirule_rule_condition rc
      INNER JOIN civirule_condition c
        ON rc.condition_id = c.id
      WHERE rc.rule_id = %1
      ORDER BY rc.id ASC
    ";
      $dao = CRM_Core_DAO::executeQuery($sql, [1 => [$ruleId, 'Integer']]);

      $conditions = [];
      while ($dao->fetch()) {
        $human = self::describeCondition($dao->class_name, $dao->rule_condition_id);
        $conditions[] = [
          'id'             => $dao->condition_id,
          'name'           => $dao->condition_name,
          'label'          => $dao->condition_label,
          'human_readable' => $human,
        ];
      }
      return $conditions;
    }

    /**
     * Fetch the actions for a rule, with a human-readable description for each
     * produced by the action class itself.
     *
     * @param int $ruleId
     * @return array
     */
    public static function getActions($ruleId) {
      $sql = "
      SELECT
        ra.id        AS rule_action_id,
        a.id         AS action_id,
        a.name       AS action_name,
        a.label      AS action_label,
        a.class_name AS class_name
      FROM civirule_rule_action ra
      INNER JOIN civirule_action a
        ON ra.action_id = a.id
      WHERE ra.rule_id = %1
      ORDER BY ra.id ASC
    ";
      $dao = CRM_Core_DAO::executeQuery($sql, [1 => [$ruleId, 'Integer']]);

      $actions = [];
      while ($dao->fetch()) {
        $human = self::describeAction($dao->class_name, $dao->rule_action_id);
        $actions[] = [
          'id'             => $dao->action_id,
          'name'           => $dao->action_name,
          'label'          => $dao->action_label,
          'human_readable' => $human,
        ];
      }
      return $actions;
    }

    /**
     * Ask the condition class to describe itself in human-readable terms.
     *
     * Every CiviRules condition class extends CRM_CiviRules_Condition and
     * implements userFriendlyConditionParams(), which is exactly what the
     * CiviRules admin UI uses to render the "what does this do" text. That
     * method already resolves IDs to labels (contribution status, membership
     * type, message template, etc).
     *
     * @param string $className
     * @param int $ruleConditionId
     * @return string
     */
    private static function describeCondition($className, $ruleConditionId) {
      if (empty($className) || !class_exists($className)) {
        return '(unknown condition class: ' . $className . ')';
      }
      try {
        // Load the civirule_rule_condition row directly rather than relying on
        // a BAO::getValues() helper, whose signature varies across CiviRules
        // versions. setRuleConditionData() expects an associative array of the
        // rule-condition row (it reads 'id', 'rule_id', 'condition_id',
        // 'condition_params', 'condition_link', etc).
        $row = self::fetchRow('civirule_rule_condition', $ruleConditionId);
        if (!$row) {
          return '(rule condition row not found: ' . $ruleConditionId . ')';
        }
        /** @var CRM_Civirules_Condition $object */
        $object = new $className();
        $object->setRuleConditionData($row);
        $text = $object->userFriendlyConditionParams();
        return is_string($text) ? trim(strip_tags($text)) : '';
      }
      catch (Throwable $e) {
        return '(error describing condition: ' . $e->getMessage() . ')';
      }
    }

    /**
     * Ask the action class to describe itself in human-readable terms.
     *
     * Every CiviRules action class extends CRM_CiviRules_Action and implements
     * userFriendlyConditionParams() (the method is named the same on actions),
     * which the admin UI uses to render the action description, resolving IDs
     * such as message template, group, tag, activity type to their labels.
     *
     * @param string $className
     * @param int $ruleActionId
     * @return string
     */
    private static function describeAction($className, $ruleActionId) {
      if (empty($className) || !class_exists($className)) {
        return '(unknown action class: ' . $className . ')';
      }
      try {
        // Load the civirule_rule_action row directly (see describeCondition for
        // rationale). setRuleActionData() expects the rule-action row array.
        $row = self::fetchRow('civirule_rule_action', $ruleActionId);
        if (!$row) {
          return '(rule action row not found: ' . $ruleActionId . ')';
        }
        /** @var CRM_Civirules_Action $object */
        $object = new $className();
        $object->setRuleActionData($row);
        // Actions expose the human-readable text via userFriendlyConditionParams()
        // (named the same as on conditions). Some versions/classes may not, so
        // fall back gracefully.
        if (method_exists($object, 'userFriendlyConditionParams')) {
          $text = $object->userFriendlyConditionParams();
          return is_string($text) ? trim(strip_tags($text)) : '';
        }
        return '';
      }
      catch (Throwable $e) {
        return '(error describing action: ' . $e->getMessage() . ')';
      }
    }

    /**
     * Fetch a single row from a civirule table as an associative array.
     *
     * @param string $table  Whitelisted table name.
     * @param int $id
     * @return array|null
     */
    private static function fetchRow($table, $id) {
      // Whitelist to keep the interpolated table name safe.
      $allowed = ['civirule_rule_condition', 'civirule_rule_action'];
      if (!in_array($table, $allowed, TRUE)) {
        return NULL;
      }
      $dao = CRM_Core_DAO::executeQuery(
        "SELECT * FROM {$table} WHERE id = %1",
        [1 => [$id, 'Integer']]
      );
      if ($dao->fetch()) {
        return $dao->toArray();
      }
      return NULL;
    }

  }
