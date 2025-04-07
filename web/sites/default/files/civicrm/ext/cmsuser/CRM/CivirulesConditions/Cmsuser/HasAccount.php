<?php

use CRM_Cmsuser_ExtensionUtil as E;

/**
 * Class for CiviRules Cmsuser HasAccount condition
 *
 * @author Matthew Wire (MJW) <mjw@mjwconsult.co.uk>
 * @license AGPL-3.0
 */
class CRM_CivirulesConditions_Cmsuser_HasAccount extends CRM_Civirules_Condition {

  protected $conditionParams = [];

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   */
  public function setRuleConditionData(array $ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = [];
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * This method returns TRUE or FALSE when an condition is valid or not
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   *
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData): bool {
    $entityID = $triggerData->getContactId();

    if (empty($entityID)) {
      return FALSE;
    }

    // Cmsuser.get returns no values if no ufmatch record found
    try {
      $cmsuser = civicrm_api3('Cmsuser', 'get', ['contact_id' => $entityID]);
    }
    catch (Exception $e) {
      $cmsuser = FALSE;
    }

    switch ($this->conditionParams['operator']) {
      case 0:
        // Contact has a CMS account
        if (!empty($cmsuser['id'])) {
          return TRUE;
        }

      case 1:
        // Contact does not have a CMS account
        if (empty($cmsuser['id'])) {
          return TRUE;
        }
    }

    return FALSE;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   *
   * @return bool|string
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirules/conditions/cmsuser_hasaccount',
      "rule_condition_id={$ruleConditionId}&entity=contact");
  }

  /**
   * Method to get operators
   *
   * @return array
   */
  public static function getOperatorOptions() {
    return [
      0 => E::ts('Has CMS Account'),
      1 => E::ts('Does not have CMS Account')
    ];
  }

    /**
   * This function validates whether this condition works with the selected trigger.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   *
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    if ($trigger->doesProvideEntity('Contact')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   */
  public function userFriendlyConditionParams() {
    switch ($this->conditionParams['operator']) {
      case 0:
        $operator = self::getOperatorOptions()[0];
        break;

      case 1:
        $operator = self::getOperatorOptions()[1];
        break;
    }

    return "Contact {$operator}";
  }

}
