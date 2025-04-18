<?php
/**
 * Class for CiviRules ValueComparison Form
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesConditions_Form_FieldValueComparison extends CRM_CivirulesConditions_Form_ValueComparison {

  protected function getEntityOptions() {
    $return = array();
    foreach($this->triggerClass->getProvidedEntities() as $entityDef) {
      if (!empty($entityDef->daoClass) && class_exists($entityDef->daoClass)) {
        $return[$entityDef->entity] = $entityDef->label;
      }
    }
    return $return;
  }

  protected function getEntities() {
    $return = array();
    foreach($this->triggerClass->getProvidedEntities() as $entityDef) {
      if (!empty($entityDef->daoClass) && class_exists($entityDef->daoClass)) {
        $return[$entityDef->entity] = $entityDef->entity;
      }
    }
    return $return;
  }

  protected function getFields() {
    $return = array();
    foreach($this->triggerClass->getProvidedEntities() as $entityDef) {
      if (!empty($entityDef->daoClass) && class_exists($entityDef->daoClass)) {
        $key = $entityDef->entity . '_';
        $className = $entityDef->daoClass;
        if (!is_callable(array($className, 'fields'))) {
          continue;
        }
        $fields = call_user_func(array($className, 'fields'));
        foreach ($fields as $field) {
          $fieldKey = $key . $field['name'];
          if (isset($field['title'])) {
            $label = trim($field['title']);
          } else {
            $label = "";
          }
          if (empty($label)) {
            $label = $field['name'];
          }
          $return[$fieldKey] = $label;
        }
        $customFields = $this->getCustomfieldsForEntity($entityDef->entity);
        foreach($customFields as $customFieldKey => $customFieldLabel) {
          $return[$key.$customFieldKey] = $customFieldLabel;
        }
      }
    }
    return $return;
  }

  protected function getCustomfieldsForEntity($entity) {
    $extends = array($entity);
    if ($entity == 'Contact') {
      $contact_types = civicrm_api3('ContactType', 'get', array());
      foreach($contact_types['values'] as $type) {
        $extends[] = $type['name'];
      }
    }

    $return = array();
    $processedGroups = array();
    foreach($extends as $extend) {
      $customGroups = civicrm_api3('CustomGroup', 'get', array('extends' => $extend, 'options' => array('limit' => 0)));
      foreach($customGroups['values'] as $customGroup) {
        if (in_array($customGroup['id'], $processedGroups)) {
          continue;
        }
        if (!empty($customGroup['is_multiple'])) {
          //do not include multiple custom groups
          continue;
        }
        $return = $return + $this->getCustomFieldPerGroup($customGroup['id'], $customGroup['title']);
        $processedGroups[] = $customGroup['id'];
      }
    }

    return $return;
  }

  protected function getCustomFieldPerGroup($group_id, $group_label) {
    $fields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $group_id, 'options' => array('limit' => 0)));
    $return = array();
    foreach($fields['values'] as $field) {
      $key = 'custom_'.$field['id'];
      $return[$key] = $group_label.': '.$field['label'];
    }
    return $return;
  }


  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    $this->add('hidden', 'rule_condition_id');
    $this->add('select', 'entity', ts('Entity'), $this->getEntityOptions(), true, array('class' => 'crm-select2 huge'));
    $this->add('select', 'field', ts('Field'), $this->getFields(), true, array('class' => 'crm-select2 huge'));
    $this->add('checkbox', 'original_data', ts('Compare with original value (before the change)?'));
    $this->assign('entities', $this->getEntities());
    $this->assign('custom_field_multi_select_html_types', CRM_Civirules_Utils_CustomField::getMultiselectTypes());
  }

  /**
   * Function to add validation condition rules (overrides parent function)
   *
   * @access public
   */
  public function addRules()
  {
    parent::addRules();
    $this->addFormRule(array('CRM_CivirulesConditions_Form_FieldValueComparison', 'validateEntityAndField'));
  }

  public static function validateEntityAndField($fields) {
    $entity = $fields['entity'];
    if (empty($entity)) {
      return array('entity' => ts('Entity could not be empty'));
    }
    if (stripos($fields['field'], $fields['entity'].'_')!==0) {
      return array('entity' => ts('Field is not valid'));
    }
    return true;
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $data = array();
    $defaultValues = parent::setDefaultValues();
    $data = $this->ruleCondition->unserializeParams();
    if (!empty($data['entity'])) {
      $defaultValues['entity'] = $data['entity'];
    }
    if (!empty($data['entity']) && !empty($data['field'])) {
      $defaultValues['field'] = $data['entity'].'_'.$data['field'];
    }
    $defaultValues['original_data'] = $data['original_data'] ?? 0;
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['operator'] = $this->_submitValues['operator'];
    $data['value'] = $this->_submitValues['value'];
    $data['multi_value'] = explode("\r\n", $this->_submitValues['multi_value']);
    $data['entity'] = $this->_submitValues['entity'];
    $data['field'] = substr($this->_submitValues['field'], strlen($data['entity'].'_'));

    if (isset($this->_submitValues['original_data'])) {
      $data['original_data'] = $this->_submitValues['original_data'];
    } else {
      $data['original_data'] = 0;
    }

    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();

    $session = CRM_Core_Session::singleton();
    $session->setStatus(E::ts("Condition '%1' parameters updated for CiviRule '%2'", [1 => $this->condition->label, 2 => $this->rule->label]),
      'Condition parameters updated',
      'success'
    );

    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->rule->id, TRUE);
    CRM_Utils_System::redirect($redirectUrl);  }

  /**
   * Method to set the form title
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'CiviRules Edit Condition parameters';
    $this->assign('ruleConditionHeader',
      E::ts("Edit Condition '%1' for CiviRule '%2'", [ 1 => $this->condition->label, 2 => $this->rule->label])
    );
    CRM_Utils_System::setTitle($title);
  }
}
