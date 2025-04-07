<?php
/**
 * Class for CiviRules Condition Cmsuser HasAccount Form
 *
 * @author Matthew Wire (MJW) <mjw@mjwconsult.co.uk>
 * @license AGPL-3.0
 */
use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesConditions_Cmsuser_Form_HasAccount extends CRM_CivirulesConditions_Form_Form {

  /**
   * @var string the entity name
   */
  protected $ruleConditionEntity;

  /**
   * @var string the name of the field for the options
   */
  protected $ruleConditionField;

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    $this->ruleConditionEntity = CRM_Utils_Request::retrieveValue('entity', 'String', NULL, TRUE);
    $this->add('hidden', 'rule_condition_id');
    $this->add('hidden', 'entity');

    $this->add('select', 'operator', ts('Operator'),
      CRM_CivirulesConditions_Cmsuser_HasAccount::getOperatorOptions(), TRUE);

    $this->addButtons([
      ['type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,],
      ['type' => 'cancel', 'name' => ts('Cancel')]
    ]);
  }

  public function getOperators() {
    return [
      0 => E::ts('Has CMS Account'),
      1 => E::ts('Does not have CMS Account')
    ];
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $defaultValues['entity'] = $this->ruleConditionEntity;
    $data = unserialize($this->ruleCondition->condition_params);

    if (!empty($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   */
  public function postProcess() {
    $this->ruleConditionEntity = $this->_submitValues['entity'];
    $data['operator'] = $this->_submitValues['operator'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }

  /**
   * Returns a help text for this condition.
   * The help text is shown to the administrator who is configuring the condition.
   *
   * @return string
   */
  protected function getHelpText() {
    return E::ts('This condition checks if the contact has (not) got a CMS account.');
  }

}
