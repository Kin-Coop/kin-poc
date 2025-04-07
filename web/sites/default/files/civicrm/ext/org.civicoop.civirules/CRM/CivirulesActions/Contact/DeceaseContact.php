<?php

class CRM_CivirulesActions_Contact_DeceaseContact extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contactId = $triggerData->getContactId();

    if (!empty($contactId)) {
      civicrm_api4('Contact', 'update', [
        'values' => [
          'is_deceased' => TRUE,
          'deceased_date' => 'now',
        ],
        'where' => [
          ['id', '=', $contactId],
        ],
        'checkPermissions' => FALSE,
      ]);
    }
  }

  /**
   * Method to return the url for additional form processing for action
   * and return false if none is needed
   *
   * @param int $ruleActionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return FALSE;
  }

}
