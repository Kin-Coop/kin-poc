<?php

namespace Civi\Kinpayments;

/**
 * Matches KinpaymentsPayment records to CiviCRM Contributions.
 *
 * Matching pipeline (in priority order):
 *
 *  Step 1 — Direct bank_reference → custom_61 lookup (score 95, bypass scoring)
 *            If bank_reference exactly matches any contribution's
 *            Unique_Contribution_Reference field, that contribution and its
 *            contact are used immediately. No further checks needed.
 *
 *  Step 2 — Fast path via bank account number (custom_154)
 *            If customer_account_number already matches a contact's stored
 *            Bank_Number, narrow the search to that contact, then score.
 *
 *  Step 3 — Scored candidate search
 *            Fetch contributions by amount (hard gate) and date window (±5 days).
 *            If the bank_reference prefix looks like a contact ID, try narrowing
 *            to that contact first. If the prefix is absent, zero, or doesn't
 *            match any contact (legacy / malformed references), fall back to a
 *            full broad search across all contacts — the prefix is a hint, not
 *            a hard filter.
 *
 * Scoring breakdown (max 100):
 *  - Bank reference exact match to custom_61    : 40 pts
 *  - Contact ID prefix in bank reference         : 15 pts
 *  - customer_account_number matches custom_154  : 30 pts
 *  - Amount exact match                          :  0 pts (hard gate only)
 *  - Date proximity (±5 days, linear decay)      : 15 pts
 *  - Customer reference name similarity          : 15 pts
 *
 * Auto-match threshold  : score >= 60
 * Auto-reject threshold : score <  30
 * Between 30–59         : score stored, left as pending for manual review
 */
class PaymentMatcher {

  // ── Status option values ──────────────────────────────────────────────────
  const STATUS_PENDING     = 1;
  const STATUS_UNMATCHED   = 2;
  const STATUS_MATCHED     = 3;

  // ── Thresholds ────────────────────────────────────────────────────────────
  const SCORE_AUTO_MATCH   = 60;
  const SCORE_AUTO_REJECT  = 30;

  // Days either side of the payment date we will still consider a contribution
  const DATE_TOLERANCE_DAYS = 5;

  // Custom field API names
  const FIELD_UNIQUE_REF   = 'Unique_Contribution_ID.Unique_Contribution_Reference'; // custom_61
  const FIELD_BANK_NUMBER  = 'Kin_Groups.Bank_Number'; // custom_154

  /**
   * @var array  Runtime options
   */
  private array $options;

  public function __construct(array $options = []) {
    $this->options = array_merge([
      'include_unmatched' => FALSE,  // also reprocess status=2 records
      'dry_run'           => FALSE,  // score and log but don't write
    ], $options);
  }

  // ── Public entry-points ──────────────────────────────────────────────────

  /**
   * Process all pending (and optionally unmatched) KinpaymentsPayment records.
   *
   * @return array  Summary counts keyed by result type.
   */
  public function matchAll(): array {
    $statuses = [self::STATUS_PENDING];
    if ($this->options['include_unmatched']) {
      $statuses[] = self::STATUS_UNMATCHED;
    }

    $payments = \Civi\Api4\KinpaymentsPayment::get(FALSE)
      ->addWhere('payment_status_id', 'IN', $statuses)
      ->execute();

    $summary = ['matched' => 0, 'unmatched' => 0, 'pending' => 0, 'errors' => 0];

    foreach ($payments as $payment) {
      try {
        $result = $this->matchOne($payment);
        $summary[$result]++;
      }
      catch (\Exception $e) {
        \Civi::log()->error('KinpaymentsPayment matching error for payment #' . $payment['id'] . ': ' . $e->getMessage());
        $summary['errors']++;
      }
    }

    return $summary;
  }

  /**
   * Attempt to match a single KinpaymentsPayment record.
   *
   * @param array $payment  KinpaymentsPayment record.
   * @return string  'matched' | 'unmatched' | 'pending'
   */
  public function matchOne(array $payment): string {
    $bankRef = trim($payment['bank_reference'] ?? '');

    // ── Step 1: direct bank_reference → custom_61 lookup ─────────────────
    // This is the highest-confidence path. If bank_reference exactly matches
    // a contribution's Unique_Contribution_Reference, we accept it immediately
    // with a score of 95 (not 100 — we can't rule out data entry errors where
    // two contributions share the same reference, but it's near-certain).
    if ($bankRef !== '') {
      $directMatch = $this->findContributionByUniqueRef($bankRef, $payment);
      if ($directMatch) {
        return $this->applyMatch($payment, $directMatch, (int) $directMatch['contact_id'], 95);
      }
    }

    // ── Step 2: fast path via stored bank account number (custom_154) ─────
    // Once a contact's bank account number has been recorded on a previous
    // match, we can go straight to them.
    if (!empty($payment['customer_account_number'])) {
      $contactId = $this->findContactByBankNumber($payment['customer_account_number']);
      if ($contactId) {
        $contribution = $this->findContributionForContact(
          $contactId,
          (float) $payment['amount'],
          $payment['datetime'],
          $bankRef
        );
        if ($contribution) {
          $score = $this->scoreMatch($payment, $contribution, $contactId);
          if ($score >= self::SCORE_AUTO_MATCH) {
            return $this->applyMatch($payment, $contribution, $contactId, $score);
          }
        }
      }
    }

    // ── Step 3: scored candidate search ───────────────────────────────────
    // Fetch by amount + date. The prefix contact ID is a hint: if it resolves
    // to a real contact we search that contact first; if not (legacy / malformed
    // reference) we search broadly across all contacts.
    $candidates = $this->findCandidateContributions($payment);

    if (empty($candidates)) {
      return $this->applyNoMatch($payment);
    }

    // Score every candidate and pick the best
    $best      = NULL;
    $bestScore = 0;

    foreach ($candidates as $candidate) {
      $candidateContactId = (int) $candidate['contact_id'];
      $score = $this->scoreMatch($payment, $candidate, $candidateContactId);
      if ($score > $bestScore) {
        $bestScore = $score;
        $best      = $candidate;
      }
    }

    if ($bestScore >= self::SCORE_AUTO_MATCH) {
      return $this->applyMatch($payment, $best, (int) $best['contact_id'], $bestScore);
    }

    if ($bestScore < self::SCORE_AUTO_REJECT) {
      return $this->applyNoMatch($payment, $bestScore);
    }

    // Ambiguous – record the score but leave as pending for manual review
    if (!$this->options['dry_run']) {
      \Civi\Api4\KinpaymentsPayment::update(FALSE)
        ->addWhere('id', '=', $payment['id'])
        ->addValue('match_score', $bestScore)
        ->execute();
    }
    return 'pending';
  }

  // ── Candidate retrieval ──────────────────────────────────────────────────

  /**
   * Look up a contribution directly by its Unique_Contribution_Reference
   * (custom_61) matching bank_reference exactly.
   *
   * When exactly one contribution matches, it is returned immediately.
   *
   * When multiple contributions share the same reference (data entry error, or
   * a contact making the same type of contribution repeatedly), we tie-break:
   *  1. Prefer contributions whose amount matches the payment amount exactly.
   *  2. Among those, prefer the one with the closest receive_date.
   *  3. If no amount match exists, pick the closest date overall.
   *
   * Returns NULL if no contributions carry this reference.
   *
   * @param string $bankRef  The bank_reference value to look up.
   * @param array  $payment  The KinpaymentsPayment record (used for tie-breaking).
   */
  private function findContributionByUniqueRef(string $bankRef, array $payment): ?array {
    if ($bankRef === '') {
      return NULL;
    }

    $paymentDate = new \DateTime($payment['datetime']);
    $dateFrom    = (clone $paymentDate)->modify('-' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');
    $dateTo      = (clone $paymentDate)->modify('+' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');
    $amount      = (float) $payment['amount'];

    $results = \Civi\Api4\Contribution::get(FALSE)
      ->addSelect(
        'id',
        'contact_id',
        'total_amount',
        'receive_date',
        'contribution_status_id',
        self::FIELD_UNIQUE_REF,
        'contact_id.first_name',
        'contact_id.last_name',
        'contact_id.display_name',
        'contact_id.' . self::FIELD_BANK_NUMBER
      )
      ->addWhere(self::FIELD_UNIQUE_REF, '=', $bankRef)
      ->addWhere('total_amount', '=', $amount)
      ->addWhere('receive_date', '>=', $dateFrom . ' 00:00:00')
      ->addWhere('receive_date', '<=', $dateTo . ' 23:59:59')
      //->addWhere('contribution_status_id', '=', 2) // Pending contributions only
      ->execute()
      ->getArrayCopy();

    if (empty($results)) {
      return NULL;
    }

    if (count($results) === 1) {
      return $results[0];
    }

    // Multiple hits — tie-break by amount match then date proximity.
    $paymentAmount = (float) $payment['amount'];
    $paymentDate   = new \DateTime($payment['datetime']);

    $amountMatches = array_filter($results, fn($r) => (float) $r['total_amount'] === $paymentAmount);
    $pool          = !empty($amountMatches) ? array_values($amountMatches) : $results;

    usort($pool, function (array $a, array $b) use ($paymentDate): int {
      $daysA = abs((int) (new \DateTime($a['receive_date']))->diff($paymentDate)->days);
      $daysB = abs((int) (new \DateTime($b['receive_date']))->diff($paymentDate)->days);
      return $daysA <=> $daysB;
    });

    return $pool[0];
  }

  /**
   * Return contribution candidates using a broad, indexed filter set.
   * Scoring narrows the field; this method casts a wide net.
   *
   * Strategy for the bank_reference prefix:
   *  - If the prefix looks like a contact ID AND that contact exists in CiviCRM,
   *    restrict the search to that contact (fast, precise).
   *  - If the prefix is absent, zero, or the contact doesn't exist (legacy or
   *    malformed reference), fall back to a full search on amount + date only.
   *    The prefix is a hint, not a hard filter.
   */
  private function findCandidateContributions(array $payment): array {
    $paymentDate = new \DateTime($payment['datetime']);
    $dateFrom    = (clone $paymentDate)->modify('-' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');
    $dateTo      = (clone $paymentDate)->modify('+' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');
    $amount      = (float) $payment['amount'];

    $selectFields = [
      'id',
      'contact_id',
      'total_amount',
      'receive_date',
      'contribution_status_id',
      self::FIELD_UNIQUE_REF,
      'contact_id.first_name',
      'contact_id.last_name',
      'contact_id.display_name',
      'contact_id.' . self::FIELD_BANK_NUMBER,
    ];

    // Determine whether the prefix resolves to a real contact
    $prefixContactId = $this->extractContactIdFromReference($payment['bank_reference'] ?? '');
    $narrowToContact = NULL;

    if ($prefixContactId) {
      $exists = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('id')
        ->addWhere('id', '=', $prefixContactId)
        ->addWhere('is_deleted', '=', FALSE)
        ->setLimit(1)
        ->execute()
        ->count();

      if ($exists) {
        $narrowToContact = $prefixContactId;
      }
      // If the prefix doesn't match any contact, $narrowToContact stays NULL
      // and we do a broad search below.
    }

    $query = \Civi\Api4\Contribution::get(FALSE)
      ->addSelect(...$selectFields)
      ->addWhere('total_amount', '=', $amount)
      ->addWhere('receive_date', '>=', $dateFrom . ' 00:00:00')
      ->addWhere('receive_date', '<=', $dateTo . ' 23:59:59');

    if ($narrowToContact !== NULL) {
      $query->addWhere('contact_id', '=', $narrowToContact);
    }

    return $query->execute()->getArrayCopy();
  }

  /**
   * Find contributions for a known contact (bank number matched).
   */
  private function findContributionForContact(int $contactId, float $amount, string $datetime, string $bankRef): ?array {
    $paymentDate = new \DateTime($datetime);
    $dateFrom    = (clone $paymentDate)->modify('-' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');
    $dateTo      = (clone $paymentDate)->modify('+' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');

    $results = \Civi\Api4\Contribution::get(FALSE)
      ->addSelect(
        'id',
        'contact_id',
        'total_amount',
        'receive_date',
        'contribution_status_id',
        self::FIELD_UNIQUE_REF,
        'contact_id.first_name',
        'contact_id.last_name',
        'contact_id.display_name',
        'contact_id.' . self::FIELD_BANK_NUMBER
      )
      ->addWhere('contact_id', '=', $contactId)
      ->addWhere('total_amount', '=', $amount)
      ->addWhere('receive_date', '>=', $dateFrom . ' 00:00:00')
      ->addWhere('receive_date', '<=', $dateTo . ' 23:59:59')
      ->execute()
      ->getArrayCopy();

    if (empty($results)) {
      return NULL;
    }

    // If multiple, prefer one where the unique ref matches
    foreach ($results as $r) {
      if (!empty($r[self::FIELD_UNIQUE_REF]) && !empty($bankRef) &&
        strtolower(trim($r[self::FIELD_UNIQUE_REF])) === strtolower(trim($bankRef))) {
        return $r;
      }
    }

    return $results[0];
  }

  // ── Scoring ──────────────────────────────────────────────────────────────

  /**
   * Calculate a 0–100 match score between a payment and a contribution.
   *
   * The amount is a hard gate (must match exactly) enforced upstream in the
   * candidate query, so we don't score it here – instead we allocate the full
   * 100 points across the remaining signals.
   */
  private function scoreMatch(array $payment, array $contribution, int $contactId): int {
    $score = 0;

    // ── Signal 1: Bank reference → Unique_Contribution_Reference (max 40) ──
    $uniqueRef  = $contribution[self::FIELD_UNIQUE_REF] ?? '';
    $bankRef    = trim($payment['bank_reference'] ?? '');
    if ($uniqueRef && $bankRef) {
      if (strtolower($uniqueRef) === strtolower($bankRef)) {
        $score += 40; // exact
      }
      elseif (stripos($bankRef, $uniqueRef) !== FALSE || stripos($uniqueRef, $bankRef) !== FALSE) {
        $score += 20; // partial / substring
      }
    }

    // ── Signal 2: Contact ID prefix in bank_reference (max 15) ─────────────
    $prefixContactId = $this->extractContactIdFromReference($bankRef);
    if ($prefixContactId && $prefixContactId === $contactId) {
      $score += 15;
    }

    // ── Signal 3: Bank account number → custom_154 (max 30) ─────────────────
    $bankNumber     = $contribution['contact_id.' . self::FIELD_BANK_NUMBER] ?? '';
    $accountNumber  = trim($payment['customer_account_number'] ?? '');
    if ($bankNumber && $accountNumber && strtolower($bankNumber) === strtolower($accountNumber)) {
      $score += 30;
    }

    // ── Signal 4: Date proximity (max 15) ────────────────────────────────────
    if (!empty($contribution['receive_date']) && !empty($payment['datetime'])) {
      $contribDate  = new \DateTime($contribution['receive_date']);
      $paymentDate  = new \DateTime($payment['datetime']);
      $diffDays     = abs((int) $contribDate->diff($paymentDate)->days);
      if ($diffDays === 0) {
        $score += 15;
      }
      elseif ($diffDays <= self::DATE_TOLERANCE_DAYS) {
        // Linear decay: 5 days away → 3 pts, 1 day away → 12 pts
        $score += (int) round(15 * (1 - ($diffDays / self::DATE_TOLERANCE_DAYS)));
      }
    }

    // ── Signal 5: Customer reference name similarity (max 15) ────────────────
    $customerRef  = trim($payment['customer_reference'] ?? '');
    $displayName  = trim($contribution['contact_id.display_name'] ?? '');
    if ($customerRef && $displayName) {
      $nameSimilarity = $this->nameMatchScore($customerRef, $displayName, $contribution);
      $score += (int) round(15 * $nameSimilarity); // 0.0–1.0 → 0–15
    }

    return min(100, $score);
  }

  /**
   * Returns 0.0–1.0 representing how well the bank customer reference
   * matches the contact name.
   *
   * Handles:
   *  - Initials (e.g. "MACKAY E" vs "Emily Mackay")
   *  - Reversed surname/forename order
   *  - Partial matches
   */
  private function nameMatchScore(string $customerRef, string $displayName, array $contribution): float {
    $refNorm  = strtolower(trim($customerRef));
    $dispNorm = strtolower(trim($displayName));

    // Exact full-name match
    if ($refNorm === $dispNorm) {
      return 1.0;
    }

    // Normalise to tokens
    $refTokens  = preg_split('/\s+/', $refNorm);
    $nameTokens = preg_split('/\s+/', $dispNorm);

    // Try surname match (bank refs usually lead with surname)
    $refSurname = $refTokens[0] ?? '';
    $firstName  = strtolower(trim($contribution['contact_id.first_name'] ?? ''));
    $lastName   = strtolower(trim($contribution['contact_id.last_name'] ?? ''));

    $surnameMatch = ($refSurname && $lastName && (
        $refSurname === $lastName ||
        levenshtein($refSurname, $lastName) <= 2
      ));

    // Check initial(s) in ref against first name(s)
    $initialMatch = FALSE;
    if (count($refTokens) > 1) {
      foreach (array_slice($refTokens, 1) as $token) {
        if (strlen($token) === 1 && $firstName && $token[0] === $firstName[0]) {
          $initialMatch = TRUE;
          break;
        }
        // Full first-name token
        if (strlen($token) > 1 && (
            $token === $firstName ||
            levenshtein($token, $firstName) <= 2
          )) {
          $initialMatch = TRUE;
          break;
        }
      }
    }

    if ($surnameMatch && $initialMatch) {
      return 0.9;
    }
    if ($surnameMatch) {
      return 0.6;
    }

    // Fallback: similar_text percentage
    similar_text($refNorm, $dispNorm, $percent);
    return min(1.0, $percent / 100);
  }

  // ── Applying results ─────────────────────────────────────────────────────

  private function applyMatch(array $payment, array $contribution, int $contactId, int $score): string {
    if ($this->options['dry_run']) {
      \Civi::log()->info(sprintf(
        '[DryRun] KinpaymentsPayment #%d → Contribution #%d (contact %d) score=%d',
        $payment['id'], $contribution['id'], $contactId, $score
      ));
      return 'matched';
    }

    // Update KinpaymentsPayment
    \Civi\Api4\KinpaymentsPayment::update(FALSE)
      ->addWhere('id', '=', $payment['id'])
      ->addValue('contribution_id',    $contribution['id'])
      ->addValue('contact_id',         $contactId)
      ->addValue('payment_status_id',  self::STATUS_MATCHED)
      ->addValue('match_score',        $score)
      ->execute();

    // Update Contribution to Completed if it is still Pending (status 2 = Pending in CiviCRM)
    if ((int) $contribution['contribution_status_id'] === 2) {
      \Civi\Api4\Contribution::update(FALSE)
        ->addWhere('id', '=', $contribution['id'])
        ->addValue('contribution_status_id', 1) // 1 = Completed
        ->execute();
    }

    // Populate Bank_Number on contact if not already set
    $accountNumber = trim($payment['customer_account_number'] ?? '');
    $existingBankNumber = $contribution['contact_id.' . self::FIELD_BANK_NUMBER] ?? '';
    if ($accountNumber && !$existingBankNumber) {
      \Civi\Api4\Contact::update(FALSE)
        ->addWhere('id', '=', $contactId)
        ->addValue(self::FIELD_BANK_NUMBER, $accountNumber)
        ->execute();
    }

    \Civi::log()->info(sprintf(
      'KinpaymentsPayment #%d matched to Contribution #%d (contact %d) score=%d',
      $payment['id'], $contribution['id'], $contactId, $score
    ));

    return 'matched';
  }

  private function applyNoMatch(array $payment, int $score = 0): string {
    if (!$this->options['dry_run']) {
      \Civi\Api4\KinpaymentsPayment::update(FALSE)
        ->addWhere('id', '=', $payment['id'])
        ->addValue('payment_status_id', self::STATUS_UNMATCHED)
        ->addValue('match_score',       $score)
        ->execute();
    }
    return 'unmatched';
  }

  // ── Helpers ──────────────────────────────────────────────────────────────

  /**
   * Extract the contact ID from a bank reference like "518-1654R".
   * The contact ID is the numeric segment before the first dash.
   */
  private function extractContactIdFromReference(string $ref): ?int {
    if (preg_match('/^(\d+)-/', $ref, $m)) {
      return (int) $m[1];
    }
    return NULL;
  }

  /**
   * Find a contact whose Kin_Groups.Bank_Number (custom_154)
   * matches the given account number.
   */
  private function findContactByBankNumber(string $accountNumber): ?int {
    if (empty($accountNumber)) {
      return NULL;
    }

    $results = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id')
      ->addWhere(self::FIELD_BANK_NUMBER, '=', $accountNumber)
      ->setLimit(1)
      ->execute();

    return $results->count() ? (int) $results->first()['id'] : NULL;
  }

}
