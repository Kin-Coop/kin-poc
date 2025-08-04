<?php
/**
 * Form controller class to manage CiviRule/RuleCondition
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

use CRM_Civirules_ExtensionUtil as E;
use Civi\Api4\CiviRulesCondition;
use Civi\Api4\CiviRulesRuleCondition;

class CRM_Civirules_Form_RuleCondition extends CRM_Core_Form {

  /**
   * @var \CRM_Civirules_BAO_CiviRulesRule
   */
  protected \CRM_Civirules_BAO_CiviRulesRule $rule;

  /**
   * @var \CRM_Civirules_BAO_CiviRulesTrigger
   */
  protected \CRM_Civirules_BAO_CiviRulesTrigger $trigger;

  /**
   * @var \CRM_Civirules_Trigger
   */
  protected \CRM_Civirules_Trigger $triggerObject;

  /**
   * @var ?int
   */
  protected ?int $ruleId = NULL;

  /**
   * Function to buildQuickForm (extends parent function)
   *
   * @access public
   */
  function buildQuickForm() {
    $this->setFormTitle();
    $this->createFormElements();
    parent::buildQuickForm();
  }

  /**
   * Function to perform processing before displaying form (overrides parent function)
   *
   * @access public
   */
  function preProcess() {
    $this->ruleId = CRM_Utils_Request::retrieve('rid', 'Integer');
    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->ruleId, TRUE);
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext($redirectUrl);
    $this->assign('countRuleConditions', CRM_Civirules_BAO_RuleCondition::countConditionsForRule($this->ruleId));
    if ($this->_action == CRM_Core_Action::DELETE) {
      $ruleConditionId = CRM_Utils_Request::retrieve('id', 'Integer');
      if (!empty($ruleConditionId)) {
        CiviRulesRuleCondition::delete(FALSE)
          ->addWhere('id', '=', $ruleConditionId)
          ->execute();
      }
      CRM_Utils_System::redirect($redirectUrl);
      return;
    }

    $this->rule = new CRM_Civirules_BAO_Rule();
    $this->rule->id = $this->ruleId;
    $this->rule->find(TRUE);
    $this->trigger = new CRM_Civirules_BAO_Trigger();
    $this->trigger->id = $this->rule->trigger_id;
    $this->trigger->find(TRUE);

    $this->triggerObject = CRM_Civirules_BAO_Trigger::getPostTriggerObjectByClassName($this->trigger->class_name, TRUE);
    $this->triggerObject->setTriggerId($this->trigger->id);
  }

  /**
   * Function to perform post save processing (extends parent function)
   *
   * @access public
   */
  function postProcess() {
    $session = CRM_Core_Session::singleton();
    $saveParams = [
      'rule_id' => $this->_submitValues['rule_id'],
      'condition_id' => $this->_submitValues['rule_condition_select']
    ];
    if (isset($this->_submitValues['rule_condition_link_select'])) {
      $saveParams['condition_link'] = $this->_submitValues['rule_condition_link_select'];
    }

    // Use save not writeRecord to make sure "weight" is set correctly
    $ruleCondition = CiviRulesRuleCondition::save(FALSE)
      ->setRecords([$saveParams])
      ->execute()
      ->first();

    $condition = CRM_Civirules_BAO_Condition::getConditionObjectById($ruleCondition['condition_id'], true);
    $redirectUrl = $condition->getExtraDataInputUrl($ruleCondition['id']);
    if (empty($redirectUrl)) {
      $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id=' . $this->_submitValues['rule_id'], TRUE);
      if (empty($this->ruleConditionId)) {
        $session->setStatus('Condition added to CiviRule ' . CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->_submitValues['rule_id']),
          'Condition added', 'success');
      }
    } else {
      // Redirect to action configuration (required to redirect popup without closing
      CRM_Utils_System::redirect($redirectUrl);
    }

    // This will allow popup to close
    $session->pushUserContext($redirectUrl);
  }

  protected function buildConditionList() {
    $conditions = CiviRulesCondition::get(FALSE)
      ->addOrderBy('label', 'ASC')
      ->execute()
      ->indexBy('id')
      ->column('label');
    foreach($conditions as $conditionID => $conditionLabel) {
      if ($this->doesConditionWorkWithTrigger($conditionID)) {
        $conditionOptions[$conditionID] = $conditionLabel;
      }
    }
    return $conditionOptions ?? [];
  }

  /**
   * Returns whether the condition works with the trigger
   *
   * @param int $conditionID
   *
   * @return bool
   */
  protected function doesConditionWorkWithTrigger(int $conditionID): bool {
    try {
      $conditionClass = CRM_Civirules_BAO_Condition::getConditionObjectById($conditionID, FALSE);
      if (!$conditionClass) {
        return FALSE;
      }
    } catch (Exception $e) {
      return FALSE;
    }
    if (!$conditionClass->doesWorkWithTrigger($this->triggerObject, $this->rule)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Function to add the form elements
   *
   * @access protected
   */
  protected function createFormElements() {
    $this->add('hidden', 'rule_id');
    $this->add('hidden', 'rid');
    $this->add('hidden', 'action');
    /*
     * add select list only if it is not the first condition
     */
    $linkList = CRM_Civirules_BAO_CiviRulesRuleCondition::getConditionLinkOptions();
    $this->add('select', 'rule_condition_link_select', E::ts('Select Link Operator'), $linkList);
    $foundConditions = $this->buildConditionList();
    if (!empty($foundConditions)) {
      $conditionList = [' - select - '] + $foundConditions;
      asort($conditionList);
    }
    else {
      $conditionList = [' - select - '];
    }
    $this->add('select', 'rule_condition_select', E::ts('Select Condition'), $conditionList, true, ['class' => 'crm-select2 huge']);

    $this->addButtons([
      ['type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,],
      ['type' => 'cancel', 'name' => E::ts('Cancel')]
    ]);
  }

  public function setDefaultValues() {
    $defaults['rule_id'] = $this->ruleId;
    $defaults['rid'] = $this->ruleId;
    $defaults['action'] = 'add';
    return $defaults;
  }

  /**
   * Function to set the form title based on action and data coming in
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'CiviRules Add Condition';
    $this->assign('ruleConditionHeader', 'Add Condition to CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleId));
    CRM_Utils_System::setTitle($title);
  }

  /**
   * Function to add validation condition rules (overrides parent function)
   *
   * @access public
   */
  public function addRules() {
    $this->addFormRule(['CRM_Civirules_Form_RuleCondition', 'validateRuleCondition']);
    $this->addFormRule(['CRM_Civirules_Form_RuleCondition', 'validateConditionEntities']);
  }

  /**
   * @param $fields
   * @return array|bool
   */
  static function validateConditionEntities($fields) {
    $conditionClass = CRM_Civirules_BAO_Condition::getConditionObjectById($fields['rule_condition_select'], false);
    if (!$conditionClass) {
      $errors['rule_condition_select'] = E::ts('Not a valid condition, condition class is missing');
      return $errors;
    }

    $rule = new CRM_Civirules_BAO_Rule();
    $rule->id = $fields['rule_id'];
    $rule->find(TRUE);
    $trigger = new CRM_Civirules_BAO_Trigger();
    $trigger->id = $rule->trigger_id;
    $trigger->find(TRUE);

    $triggerObject = CRM_Civirules_BAO_Trigger::getPostTriggerObjectByClassName($trigger->class_name, TRUE);
    $triggerObject->setTriggerId($trigger->id);

    if (!$conditionClass->doesWorkWithTrigger($triggerObject, $rule)) {
      $errors['rule_condition_select'] = E::ts('This condition is not available with trigger %1', [1 => $trigger->label]);
      return $errors;
    }

    return true;
  }

  /**
   * Function to validate value of rule condition form
   *
   * @param array $fields
   * @return array|bool
   * @access public
   * @static
   */
  static function validateRuleCondition($fields) {
    if (isset($fields['rule_condition_link_select']) && empty($fields['rule_condition_link_select'])) {
      $errors['rule_condition_link_select'] = E::ts('Link Operator can only be AND or OR');
      return $errors;
    }
    if (isset($fields['rule_condition_select']) && empty($fields['rule_condition_select'])) {
      $errors['rule_condition_select'] = E::ts('Condition has to be selected, press CANCEL if you do not want to add a condition');
      return $errors;
    }
    return TRUE;
  }
}
