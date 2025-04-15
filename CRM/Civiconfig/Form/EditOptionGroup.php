<?php
/**
 * Copyright (C) 2021  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use CRM_Civiconfig_ExtensionUtil as E;

class CRM_Civiconfig_Form_EditOptionGroup extends CRM_Core_Form {

  /**
   * @var Civi\ConfigItems\Entity\OptionGroup\Exporter;
   */
  protected $exporter;

  /**
   * @var string
   */
  protected $entityName;

  /**
   * @var int
   */
  protected $id;

  protected $configItemSet;

  /**
   * @var string
   */
  protected $current_option_group;

  public function preProcess() {
    parent::preProcess();
    $this->entityName = 'OptionGroup';
    $factory = civiconfig_get_entity_factory();
    $this->exporter = $factory->getEntityDefinition($this->entityName)->getExporterClass();
    $this->setTitle(E::ts('Edit Configuration Set'));
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    if ($this->id) {
      $this->configItemSet = Civi\Api4\ConfigItemSet::get()
        ->addWhere('id', '=', $this->id)
        ->setLimit(1)
        ->execute()
        ->first();
      $this->assign('config_item_set', $this->configItemSet);
    }
    $this->current_option_group = CRM_Utils_Request::retrieve('option_group_name', 'String');
    CRM_Utils_System::setTitle(E::ts('Add option group for configuration set: %1', [1=>$this->configItemSet['title']]));
    if ($this->current_option_group) {
      CRM_Utils_System::setTitle(E::ts('Edit option group for configuration set: %1', [1=>$this->configItemSet['title']]));
    }
    if ($this->_action & CRM_Core_Action::DELETE) {
      $configuration = $this->configItemSet['configuration'][$this->entityName];

      if (isset($configuration['remove']) && in_array($this->current_option_group, $configuration['remove'])) {
        if (($key = array_search($this->current_option_group, $configuration['remove'])) !== false) {
          unset($configuration['remove'][$key]);
        }
      } elseif (isset($configuration['include']) && isset($configuration['include'][$this->current_option_group])) {
        unset($configuration['include'][$this->current_option_group]);
      }

      $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
      $this->configItemSet['configuration'][$this->entityName] = $configuration;
      $values['configuration'] = $this->configItemSet['configuration'];
      civicrm_api4('ConfigItemSet', 'update', [
        'values' => $values,
        'where' => [['id', '=', $this->id]],
      ]);
      CRM_Utils_System::redirect($redirectUrl);
    }
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    $this->add('hidden', 'option_group_name');
    $radioButtons = [
      'include' => E::ts('Include'),
      'remove' => E::ts('Mark as removed'),
    ];
    $optionGroups = [];
    $optionGroupIdToName = [];
    $optionValues = [];
    foreach (\Civi\Api4\OptionGroup::get()->addOrderBy('title', 'ASC')->execute() as $optionGroup) {
      if ($this->isOptionGroupAvailable($optionGroup['name'])) {
        $optionGroups[$optionGroup['name']] = $optionGroup['title'];
        $optionGroupIdToName[$optionGroup['id']] = $optionGroup['name'];
        $optionValues[$optionGroup['name']] = [];
      }
    }
    $this->add('select', 'option_group', E::ts('Option Group'), $optionGroups, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge40',
      'placeholder' => E::ts('- select -'),
    ));
    $this->addRadio('option_group_select', E::ts('Select'), $radioButtons, ['allowClear' => true], NULL, TRUE, []);
    $this->addYesNo('propose_remove', E::ts('Propose to remove option values'), TRUE, TRUE);
    $this->addYesNo('select_all_values', E::ts('Include all values'), TRUE, TRUE);
    foreach (\Civi\Api4\OptionValue::get()->addOrderBy('weight', 'ASC')->execute() as $optionValue) {
      $optionGroupName = $optionGroupIdToName[$optionValue['option_group_id']];
      $optionLabel = E::ts('%1 (Value: %2)', [1=>$optionValue['label'], 2=>$optionValue['value']]);
      $optionValueName = \CRM_Utils_String::munge($optionGroupName) . '_' . \CRM_Utils_String::munge($optionValue['name']);
      $this->addRadio($optionValueName, $optionLabel, $radioButtons, ['allowClear' => true], NULL, FALSE, []);
      $optionValues[$optionGroupName][] = $optionValueName;
    }
    $this->assign('option_groups', $optionGroups);
    $this->assign('option_values', $optionValues);
    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save & Next'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))));

    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    CRM_Utils_System::appendBreadCrumb([['title' => E::ts('Edit Configuration Set'), 'url' => $redirectUrl]]);
  }

  /**
   * Function to set default values (overrides parent function)
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['propose_remove'] = '0';
    $defaults['select_all_values'] = '1';
    $defaults['id'] = $this->id;
    if ($this->current_option_group) {
      $defaults['option_group_name'] = $this->current_option_group;
      $defaults['option_group'] = $this->current_option_group;
      $configuration = $this->configItemSet['configuration'][$this->entityName];
      if (isset($configuration['remove']) && in_array($this->current_option_group, $configuration['remove'])) {
        $defaults['option_group_select'] = 'remove';
      } elseif (isset($configuration['include']) && isset($configuration['include'][$this->current_option_group])) {
        $defaults['option_group_select'] = 'include';
        $defaults['propose_remove'] = $configuration['include'][$this->current_option_group]['propose_remove'];
        $defaults['select_all_values'] = $configuration['include'][$this->current_option_group]['select_all_values'];
        foreach($configuration['include'][$this->current_option_group]['include'] as $optionValueName) {
          $elementName = \CRM_Utils_String::munge($this->current_option_group) . '_' . \CRM_Utils_String::munge($optionValueName);
          $defaults[$elementName] = 'include';
        }
        foreach($configuration['include'][$this->current_option_group]['remove'] as $optionValueName) {
          $elementName = \CRM_Utils_String::munge($this->current_option_group) . '_' . \CRM_Utils_String::munge($optionValueName);
          $defaults[$elementName] = 'remove';
        }
      }
    }
    return $defaults;
  }

  /**
   * Function that can be defined in Form to override or.
   * perform specific action on cancel action
   */
  public function cancelAction() {
    $this->entityName = 'OptionGroup';
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    CRM_Utils_System::redirect($redirectUrl);
  }

  public function postProcess() {
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    $submittedValues = $this->getSubmitValues();
    $optionGroupName = $submittedValues['option_group'];
    $configuration = $this->configItemSet['configuration'][$this->entityName];

    if (isset($configuration['remove']) && in_array($this->current_option_group, $configuration['remove'])) {
      if (($key = array_search($this->current_option_group, $configuration['remove'])) !== false) {
        unset($configuration['remove'][$key]);
      }
    } elseif (isset($configuration['include']) && isset($configuration['include'][$this->current_option_group])) {
      unset($configuration['include'][$this->current_option_group]);
    }

    if (isset($submittedValues['option_group_select']) && $submittedValues['option_group_select'] == 'remove') {
      $configuration['remove'][] = $optionGroupName;
    } else {
      $configuration['include'][$optionGroupName] = [
        'propose_remove' => $submittedValues['propose_remove'],
        'select_all_values' => $submittedValues['select_all_values'],
        'include' => [],
        'remove' => [],
      ];

      foreach (\Civi\Api4\OptionValue::get()->addWhere('option_group_id:name', '=', $optionGroupName)->execute() as $optionValue) {
        $optionValueName = \CRM_Utils_String::munge($optionGroupName) . '_' . \CRM_Utils_String::munge($optionValue['name']);
        if ($submittedValues['select_all_values'] || (isset($submittedValues[$optionValueName]) && $submittedValues[$optionValueName] == 'include')) {
          $configuration['include'][$optionGroupName]['include'][] = $optionValue['name'];
        } elseif (isset($submittedValues[$optionValueName]) && $submittedValues[$optionValueName] == 'remove') {
          $configuration['include'][$optionGroupName]['remove'][] = $optionValue['name'];
        }
      }
    }

    $this->configItemSet['configuration'][$this->entityName] = $configuration;
    $values['configuration'] = $this->configItemSet['configuration'];
    civicrm_api4('ConfigItemSet', 'update', [
      'values' => $values,
      'where' => [['id', '=', $this->id]],
    ]);
    CRM_Utils_System::redirect($redirectUrl);
  }

  protected function isOptionGroupAvailable($option_group_name) {
    if ($option_group_name == $this->current_option_group) {
      return TRUE;
    }
    $configuration = $this->configItemSet['configuration'][$this->entityName];
    if (isset($configuration['remove']) && in_array($option_group_name, $configuration['remove'])) {
      return FALSE;
    }
    if (isset($configuration['include']) && isset($configuration['include'][$option_group_name])) {
      return FALSE;
    }
    return TRUE;
  }

}
