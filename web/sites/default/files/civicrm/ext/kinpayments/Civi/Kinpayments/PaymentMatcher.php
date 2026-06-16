<?php

namespace Civi\Kinpayments;

/**
 * Matches KinpaymentsPayment records to CiviCRM Contributions.
 *
 * Scoring breakdown (max 100):
 *  - Bank reference exact match to custom_61    : 40 pts  → strong signal, auto-match
 *  - Contact ID prefix in bank reference         : 15 pts  → structural hint
 *  - customer_account_number matches contact
 *    Kin_Groups.Bank_Number (custom_154)         : 30 pts  → reliable once populated
 *  - Amount exact match (required gate)          :  0 pts added, but gates all other scoring
 *  - Date within tolerance (±5 days)             : 15 pts  → scaled by proximity
 *  - Customer reference name similarity          : 15 pts  → fuzzy name match
 *
 * Auto-match threshold  : score >= 60
 * Auto-reject threshold : score <  30
 * Between 30–59         : recorded but left as pending for manual review
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
    // ── Step 1: fast path via bank_number (most reliable) ─────────────────
    if (!empty($payment['customer_account_number'])) {
      $contactId = $this->findContactByBankNumber($payment['customer_account_number']);
      if ($contactId) {
        $contribution = $this->findContributionForContact(
          $contactId,
          (float) $payment['amount'],
          $payment['datetime'],
          $payment['bank_reference'] ?? ''
        );
        if ($contribution) {
          $score = $this->scoreMatch($payment, $contribution, $contactId);
          // Even on the fast path we require a decent score to avoid false positives
          if ($score >= self::SCORE_AUTO_MATCH) {
            return $this->applyMatch($payment, $contribution, $contactId, $score);
          }
        }
      }
    }

    // ── Step 2: candidate search from contributions ────────────────────────
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

    // Ambiguous – record the score but leave as pending for human review
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
   * Return contribution candidates using a broad, indexed filter set.
   * We widen the net here and rely on scoring to narrow it down.
   */
  private function findCandidateContributions(array $payment): array {
    $paymentDate = new \DateTime($payment['datetime']);
    $dateFrom    = (clone $paymentDate)->modify('-' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');
    $dateTo      = (clone $paymentDate)->modify('+' . self::DATE_TOLERANCE_DAYS . ' days')->format('Y-m-d');
    $amount      = (float) $payment['amount'];

    // --- Try contact ID from bank_reference prefix first (18 digits prefix) --
    $prefixContactId = $this->extractContactIdFromReference($payment['bank_reference'] ?? '');

    $query = \Civi\Api4\Contribution::get(FALSE)
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
      ->addWhere('total_amount', '=', $amount)
      ->addWhere('receive_date', '>=', $dateFrom . ' 00:00:00')
      ->addWhere('receive_date', '<=', $dateTo . ' 23:59:59');

    if ($prefixContactId) {
      // Narrow to this contact – much faster
      $query->addWhere('contact_id', '=', $prefixContactId);
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
