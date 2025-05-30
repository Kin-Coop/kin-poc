<?php
/**
 * @author Alain Benbassat (CiviCooP) <alain.benbassat@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_CivirulesActions_Participant_Form_UpdateStatus extends CRM_CivirulesActions_Form_Form {


  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');
    $this->add('select', 'status_id', ts('Status'), array('' => ts('-- please select --')) + CRM_Event_PseudoConstant::participantStatus(), true);
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
    $data = !empty($this->ruleAction->action_params) ? unserialize($this->ruleAction->action_params) : [];
    if (!empty($data['status_id'])) {
      $defaultValues['status_id'] = $data['status_id'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['status_id'] = $this->_submitValues['status_id'];
    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }

}
