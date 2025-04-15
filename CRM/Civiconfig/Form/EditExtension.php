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

class CRM_Civiconfig_Form_EditExtension extends CRM_Core_Form {

  /**
   * @var Civi\ConfigItems\Entity\EntityExporter;
   */
  protected $entityExporterClass;

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
  protected $extensionKey;

  protected $extension;

  public function preProcess() {
    parent::preProcess();
    $this->entityName = 'Extension';
    $factory = civiconfig_get_entity_factory();
    $this->entityExporterClass = $factory->getEntityDefinition($this->entityName)->getExporterClass();
    $this->setTitle(E::ts('Edit Configuration Set'));
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->extensionKey = CRM_Utils_Request::retrieve('extension', 'String');
    if ($this->id) {
      $this->configItemSet = Civi\Api4\ConfigItemSet::get()
        ->addWhere('id', '=', $this->id)
        ->setLimit(1)
        ->execute()
        ->first();
      $this->assign('config_item_set', $this->configItemSet);
      if ($this->extensionKey && isset($this->configItemSet['configuration']) && isset($this->configItemSet['configuration'][$this->entityName]) && isset($this->configItemSet['configuration'][$this->entityName][$this->extensionKey])) {
        $this->extension = $this->configItemSet['configuration'][$this->entityName][$this->extensionKey];
      }
    }
    if ($this->_action & CRM_Core_Action::DELETE) {
      $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
      if ($this->extensionKey && isset($this->configItemSet['configuration']) && isset($this->configItemSet['configuration'][$this->entityName]) && isset($this->configItemSet['configuration'][$this->entityName][$this->extensionKey])) {
        unset($this->configItemSet['configuration'][$this->entityName][$this->extensionKey]);
        $values['configuration'] = $this->configItemSet['configuration'];
        civicrm_api4('ConfigItemSet', 'update', [
          'values' => $values,
          'where' => [['id', '=', $this->id]],
        ]);
      }
      CRM_Utils_System::redirect($redirectUrl);
    }
  }

  public function buildQuickForm() {
    $downloadSourceOptions = \Civi\ConfigItems\Entity\Extension\ExportForm::downloadSourceOptions();
    $this->add('hidden', 'id');
    $this->add('text', 'key', E::ts('Name'), ['class' => 'huge40'],true);
    $this->add('select', 'download_source', E::ts('Download source'), $downloadSourceOptions, true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge40',
      'placeholder' => E::ts('- select -'),
    ));
    $this->add('text', 'url', E::ts('URL'), ['class' => 'huge40'],false);
    $this->add('text', 'branch', E::ts('Branch / Tag'), ['class' => 'huge40'],false);
    $this->addFormRule(array('CRM_Civiconfig_Form_EditExtension', 'validateSource'));

    CRM_Utils_System::setTitle(E::ts('Add extension for configuration set: %1', [1=>$this->configItemSet['title']]));
    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save & Next'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))));

    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    CRM_Utils_System::appendBreadCrumb([['title' => E::ts('Edit Configuration Set'), 'url' => $redirectUrl]]);
  }

  /**
   * Here's our custom validation callback
   */
  public static function validateSource($values) {
    $errors = \Civi\ConfigItems\Entity\Extension\ExportForm::validateSource($values['download_source'], $values);
    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Function to set default values (overrides parent function)
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    if (!empty($this->extension)) {
      $defaults = $this->extension;
    }
    $defaults['id'] = $this->id;
    return $defaults;
  }

  /**
   * Function that can be defined in Form to override or.
   * perform specific action on cancel action
   */
  public function cancelAction() {
    $this->entityName = 'Extension';
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    CRM_Utils_System::redirect($redirectUrl);
  }

  public function postProcess() {
    $redirectUrl = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $this->id, 'entity' => $this->entityName]);
    $submittedValues = $this->getSubmitValues();
    $key = $submittedValues['key'];
    $extension['key'] = $key;
    $extension['download_source'] = $submittedValues['download_source'];
    if ($extension['download_source'] == 'git') {
      $extension['url'] = $submittedValues['url'];
      $extension['branch'] = $submittedValues['branch'];
    } elseif ($extension['download_source'] == 'zip') {
      $extension['url'] = $submittedValues['url'];
    }
    $this->configItemSet['configuration'][$this->entityName][$key] = $extension;
    $values['configuration'] = $this->configItemSet['configuration'];
    civicrm_api4('ConfigItemSet', 'update', [
      'values' => $values,
      'where' => [['id', '=', $this->id]],
    ]);
    CRM_Utils_System::redirect($redirectUrl);
  }

}
