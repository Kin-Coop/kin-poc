<?php

  class CRM_CivirulesActions_Contribution_CancelOldPendingRC extends CRM_Civirules_Action {

    /**
     * Executes when the rule triggers.
     */
    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
      // Optionally get the specific contribution ID from the trigger
      $contributionId = $triggerData->getEntityId();

      // Call your custom APIv3 action
      civicrm_api3('Contribution', 'Cancelrecurring', [
        // You can pass parameters if your API supports them
         'contribution_id' => $contributionId,
      ]);
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
