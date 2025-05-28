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

class CRM_Civiconfig_Form_EditCustomGroup extends CRM_Core_Form {

  /**
   * @var Civi\ConfigItems\Entity\CustomGroup\Exporter;
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
  protected $current_custom_group;

  public function preProcess() {
    parent::preProcess();
    $this->entityName = 'CustomGroup';
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
    $this->current_custom_group = CRM_Utils_Request::retrieve('custom_group_name', 'String');
    CRM_Utils_System::setTitle(E::ts('Add custom group for configuration set: %1', [1=>$this->configItemSet['title']]));
    if ($this->current_custom_group) {
      CRM_Utils_System::setTitle(E::ts('Edit custom group for configuration set: %1', [1=>$this->configItemSet['title']]));
    }
    if ($this->_action & CRM_Core_Action::DELETE) {
      $configuration = $this->configItemSet['configuration'][$this->entityName];

      if (isset($configuration['remove']) && in_array($this->current_custom_group, $configuration['remove'])) {
        if (($key = array_search($this->current_custom_group, $configuration['remove'])) !== false) {
          unset($configuration['remove'][$key]);
        }
      } elseif (isset($configuration['include']) && isset($configuration['include'][$this->current_custom_group])) {
        unset($configuration['include'][$this->current_custom_group]);
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
    $this->add('hidden', 'custom_group_name');
    $radioButtons = [
      'include' => E::ts('Include'),
      'remove' => E::ts('Mark as removed'),
    ];
    $customGroups = [];
    $customGroupIdToName = [];
    $customFields = [];
    foreach (\Civi\Api4\CustomGroup::get()->addOrderBy('title', 'ASC')->execute() as $customGroup) {
      if ($this->isCustomGroupAvailable($customGroup['name'])) {
        $customGroups[$customGroup['name']] = $customGroup['title'];
        $customGroupIdToName[$customGroup['id']] = $customGroup['name'];
        $customFields[$customGroup['name']] = [];
      }
    }
    $this->add('select', 'custom_group', E::ts('Custom Group'), $customGroups, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge40',
      'placeholder' => E::ts('- select -'),
    ));
    $this->addRadio('custom_group_select', E::ts('Select'), $radioButtons, ['allowClear' => true], NULL, TRUE, []);
    $this->addYesNo('propose_remove', E::ts('Propose to remove custom fields'), TRUE, TRUE);
    $this->addYesNo('select_all_fields', E::ts('Include all fields'), TRUE, TRUE);
    foreach (\Civi\Api4\CustomField::get()->addOrderBy('weight', 'ASC')->execute() as $customField) {
      $customGroupName = $customGroupIdToName[$customField['custom_group_id']];
      $customFieldLabel = E::ts('%1 (Name: %2)', [1=>$customField['label'], 2=>$customField['name']]);
      $customFieldname = \CRM_Utils_String::munge($customGroupName) . '_' . \CRM_Utils_String::munge($customField['name']);
      $this->addRadio($customFieldname, $customFieldLabel, $radioButtons, ['allowClear' => true], NULL, FALSE, []);
      $customFields[$customGroupName][] = $customFieldname;
    }
    $this->assign('custom_groups', $customGroups);
    $this->assign('custom_fields', $customFields);
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
    $defaults['select_all_fields'] = '1';
    $defaults['id'] = $this->id;
    if ($this->current_custom_group) {
      $defaults['custom_group_name'] = $this->current_custom_group;
      $defaults['custom_group'] = $this->current_custom_group;
      $configuration = $this->configItemSet['configuration'][$this->entityName];
      if (isset($configuration['remove']) && in_array($this->current_custom_group, $configuration['remove'])) {
        $defaults['custom_group_select'] = 'remove';
      } elseif (isset($configuration['include']) && isset($configuration['include'][$this->current_custom_group])) {
        $defaults['custom_group_select'] = 'include';
        $defaults['propose_remove'] = $configuration['include'][$this->current_custom_group]['propose_remove'];
        $defaults['select_all_fields'] = $configuration['include'][$this->current_custom_group]['select_all_fields'];
        foreach($configuration['include'][$this->current_custom_group]['include'] as $customFieldName) {
          $elementName = \CRM_Utils_String::munge($this->current_custom_group) . '_' . \CRM_Utils_String::munge($customFieldName);
          $defaults[$elementName] = 'include';
        }
        foreach($configuration['include'][$this->current_custom_group]['remove'] as $customFieldName) {
          $elementName = \CRM_Utils_String::munge($this->current_custom_group) . '_' . \CRM_Utils_String::munge($customFieldName);
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
    $this->entityName = 'CustomGroup';
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    CRM_Utils_System::redirect($redirectUrl);
  }

  public function postProcess() {
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    $submittedValues = $this->getSubmitValues();
    $customGroupName = $submittedValues['custom_group'];
    $configuration = $this->configItemSet['configuration'][$this->entityName];

    if (isset($configuration['remove']) && in_array($this->current_custom_group, $configuration['remove'])) {
      if (($key = array_search($this->current_custom_group, $configuration['remove'])) !== false) {
        unset($configuration['remove'][$key]);
      }
    } elseif (isset($configuration['include']) && isset($configuration['include'][$this->current_custom_group])) {
      unset($configuration['include'][$this->current_custom_group]);
    }

    if (isset($submittedValues['custom_group_select']) && $submittedValues['custom_group_select'] == 'remove') {
      $configuration['remove'][] = $customGroupName;
    } else {
      $configuration['include'][$customGroupName] = [
        'propose_remove' => $submittedValues['propose_remove'],
        'select_all_fields' => $submittedValues['select_all_fields'],
        'include' => [],
        'remove' => [],
      ];

      foreach (\Civi\Api4\CustomField::get()->addWhere('custom_group_id:name', '=', $customGroupName)->execute() as $customField) {
        $customFieldName = \CRM_Utils_String::munge($customGroupName) . '_' . \CRM_Utils_String::munge($customField['name']);
        if ($submittedValues['select_all_fields'] || (isset($submittedValues[$customFieldName]) && $submittedValues[$customFieldName] == 'include')) {
          $configuration['include'][$customGroupName]['include'][] = $customField['name'];
        } elseif (isset($submittedValues[$customFieldName]) && $submittedValues[$customFieldName] == 'remove') {
          $configuration['include'][$customGroupName]['remove'][] = $customField['name'];
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

  protected function isCustomGroupAvailable($custom_group_name) {
    if ($custom_group_name == $this->current_custom_group) {
      return TRUE;
    }
    $configuration = $this->configItemSet['configuration'][$this->entityName];
    if (isset($configuration['remove']) && in_array($custom_group_name, $configuration['remove'])) {
      return FALSE;
    }
    if (isset($configuration['include']) && isset($configuration['include'][$custom_group_name])) {
      return FALSE;
    }
    return TRUE;
  }

}
