<?php
/**
 * Class for CiviRules Contribution Thank You Date Form
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesActions_Contribution_Form_ThankYouDate extends CRM_CivirulesActions_Form_Form {

  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');
    $dateRadioOptions = array('Date of Action Execution', 'xxx days after Action Execution', 'Specific Date');
    $this->addRadio('thank_you_date_radio', ts('Thank You Date will be set to : '), $dateRadioOptions);
    $this->add('text', 'number_of_days', ts('Number of Days after Action Execution'));
    $this->add('datepicker', 'thank_you_date', ts('Thank You Date'), [], FALSE, array('time' => FALSE));
    $timeRadioOptions = array('Specific Time', 'Time of Action Execution');
    $this->addRadio('thank_you_time_radio', ts('Thank You Time will be set to : '), $timeRadioOptions);
    $this->add('datepicker', 'thank_you_time', ts('Thank You Time'), [], FALSE, array('date' => FALSE));
    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $defaultValues['rule_action_id'] = $this->ruleActionId;
    $data = $this->ruleAction->unserializeParams();
    if (!empty($data['number_of_days'])) {
      $defaultValues['number_of_days'] = $data['number_of_days'];
    }
    if (empty($data['thank_you_date_radio'])) {
      $defaultValues['thank_you_date_radio'] = 0;
    } else {
      $defaultValues['thank_you_date_radio'] = $data['thank_you_date_radio'];
    }
    if (empty($data['thank_you_date'])) {
      $defaultValues['thank_you_date'] = date('Y-m-d');
    } else {
      $defaultValues['thank_you_date'] = $data['thank_you_date'];
    }
    if (empty($data['thank_you_time_radio'])) {
      $defaultValues['thank_you_time_radio'] = 0;
    }
    else {
      $defaultValues['thank_you_time_radio'] = $data['thank_you_time_radio'];
    }
    if (empty($data['thank_you_time'])) {
      $defaultValues['thank_you_time'] = null;
      // Handle the legacy case.
      if (empty($data['thank_you_time_radio'])) {
        $defaultValues['thank_you_time'] = '00:00:00';
      }
    } else {
      $defaultValues['thank_you_time'] = $data['thank_you_time'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['thank_you_date_radio'] = $this->_submitValues['thank_you_date_radio'];
    if ($this->_submitValues['thank_you_date_radio'] == 2) {
      $data['thank_you_date'] = $this->_submitValues['thank_you_date'];
    } else {
      $data['thank_you_date'] = null;
    }
    if ($this->_submitValues['thank_you_date_radio'] == 1) {
      $data['number_of_days'] = $this->_submitValues['number_of_days'];
    } else {
      $data['number_of_days'] = 0;
    }
    $data['thank_you_time_radio'] = $this->_submitValues['thank_you_time_radio'];
    if ((int) $this->_submitValues['thank_you_time_radio'] == 0) {
      $data['thank_you_time'] = $this->_submitValues['thank_you_time'];
    } else {
      $data['thank_you_time'] = null;
    }
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }
}
