<?php

  class CRM_CivirulesActions_Contribution_CancelMembershipRC extends CRM_Civirules_Action {

    /**
     * Executes when the rule triggers.
     */
    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
      // Optionally get the specific contribution ID from the trigger
      $contributionId = $triggerData->getEntityId();

      if($contributionId) {
        try {

          $contribution = \Civi\Api4\Contribution::get(FALSE)
            ->addSelect('id', 'Kin_Contributions.Household.display_name', 'contact_id', 'contribution_recur_id')
            ->addWhere('id', '=', $contributionId)
            ->execute()
            ->first();

          $contacts = \Civi\Api4\Contact::get(FALSE)
            ->addSelect('id', 'email_primary.email')
            ->addWhere('id', '=', $contribution['contact_id'])
            ->execute()
            ->first();

          try {
            // Cancel recurring contribution
            $rcs = \Civi\Api4\ContributionRecur::update(FALSE)
              ->addValue('contribution_status_id', 3) // "Cancelled"
              ->addValue('cancel_reason', 'Automatic civirule after contribution failed for 10 days')
              ->addWhere('id', '=', $contribution['contribution_recur_id'])
              ->execute();

            if($rcs) {

            }
          }
          catch (Exception $e) {
            \Civi::log()->error('Failed to cancel recurring contribution ' . $contribution['contact_id'] . ': ' . $e->getMessage());
          }
        }
        catch (Exception $e) {
          return civicrm_api3_create_error($e->getMessage());
        }
      }
    }

    public function getExtraDataInputUrl($ruleActionId)
    {
      // TODO: Implement getExtraDataInputUrl() method.
      return FALSE;
    }

    /*
    public static function getEntity() {
      return 'Contribution';
    }

    public static function getLabel() {
      return ts('Call Custom API: Cancel Old Pending Recurring');
    }

    public static function getDescription() {
      return ts('Calls the kincoop custom API Contribution.canceloldpendingrecurring.');
    }
    */
  }
