<?php

use CRM_Cmsuser_ExtensionUtil as E;

/**
 * Class for CiviRule Action Cmsuser.create
 */
class CRM_CivirulesActions_Cmsuser_Create extends CRM_CivirulesActions_Generic_Api {

  /**
   * Method to get the api entity to process in this CiviRule action
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Cmsuser';
  }

  /**
   * Method to get the api action to process in this CiviRule action
   *
   * @return string
   */
  protected function getApiAction() {
    return 'create';
  }

  /**
   * Returns an array with parameters used for processing an action
   *
   * @param array $params
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   *
   * @return array
   */
  protected function alterApiParameters($params, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $params['contact_id'] = $triggerData->getContactId();
    return $params;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   *
   * @return bool|string
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return FALSE;
  }

  /**
   * This function validates whether this action works with the selected trigger.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $entities = $trigger->getProvidedEntities();
    if (isset($entities['Contact'])) {
      return TRUE;
    }
    return FALSE;
  }

}
