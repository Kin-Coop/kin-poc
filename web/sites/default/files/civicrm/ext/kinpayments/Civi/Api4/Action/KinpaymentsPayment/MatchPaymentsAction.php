<?php

namespace Civi\Api4\Action\KinpaymentsPayment;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Kinpayments\PaymentMatcher;

/**
 * Match pending KinpaymentsPayment records to CiviCRM Contributions.
 *
 * Usage via APIv4:
 *   \Civi\Api4\KinpaymentsPayment::matchPayments()
 *     ->setIncludeUnmatched(true)
 *     ->execute();
 *
 * Or via drush / scheduled job (APIv3 wrapper also provided):
 *   drush cvapi KinpaymentsPayment.match_payments include_unmatched=1
 */
class MatchPaymentsAction extends AbstractAction {

  /**
   * Also reprocess records with status = 2 (Not Matched).
   *
   * @var bool
   */
  protected bool $includeUnmatched = FALSE;

  /**
   * Score and log matches without writing any changes to the database.
   *
   * @var bool
   */
  protected bool $dryRun = FALSE;

  public function _run(Result $result): void {
    $matcher = new PaymentMatcher([
      'include_unmatched' => $this->includeUnmatched,
      'dry_run'           => $this->dryRun,
    ]);

    $summary = $matcher->matchAll();

    $result->exchangeArray([$summary]);
  }

  // ── Getters / Setters (APIv4 convention) ─────────────────────────────────

  public function getIncludeUnmatched(): bool {
    return $this->includeUnmatched;
  }

  public function setIncludeUnmatched(bool $includeUnmatched): static {
    $this->includeUnmatched = $includeUnmatched;
    return $this;
  }

  public function getDryRun(): bool {
    return $this->dryRun;
  }

  public function setDryRun(bool $dryRun): static {
    $this->dryRun = $dryRun;
    return $this;
  }

}
