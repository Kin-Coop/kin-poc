<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Api4\Action\KinpaymentsPayment\MatchPaymentsAction;

/**
 * KinpaymentsPayment entity.
 *
 * Extends the auto-generated APIv4 entity to expose the custom
 * MatchPayments action alongside the standard CRUD operations.
 */
class KinpaymentsPayment extends Generic\DAOEntity {

  /**
   * Match pending KinpaymentsPayment records to CiviCRM Contributions.
   *
   * @param bool $checkPermissions
   * @return \Civi\Api4\Action\KinpaymentsPayment\MatchPaymentsAction
   */
  public static function matchPayments(bool $checkPermissions = TRUE): MatchPaymentsAction {
    return (new MatchPaymentsAction(static::getEntityName(), __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

}
