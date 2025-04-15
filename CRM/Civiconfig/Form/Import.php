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
use Civi\ConfigItems\Tab;

/**
 * Form controller class
 */
class CRM_Civiconfig_Form_Import extends CRM_Core_Form {

  /**
   * @var int
   */
  protected $id;

  protected $configItemSet;

  protected $tabs;

  public function preProcess() {
    parent::preProcess();

    $this->setTitle(E::ts('Import Configuration Set'));

    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->loadConfigItemSet();
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'templates/CRM/common/TabHeader.js', 1, 'html-header')
      ->addSetting([
        'tabSettings' => [
          'active' => $this->getCurrentTabName()
        ],
      ]);
    if ($this->_action & CRM_Core_Action::UPDATE) {
      $nextTab = $this->getNextTab();
      if ($nextTab) {
        CRM_Utils_System::redirect($nextTab['link']);
      }
      if (empty($this->configItemSet['entities'])) {
        CRM_Core_Session::setStatus(E::ts('Nothing to import'), '', 'Info');
        $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig', array('reset' => 1));
        CRM_Utils_System::redirect($redirectUrl);
      }
    }
  }

  public function buildQuickForm() {
    parent::buildQuickForm();
    $factory = civiconfig_get_fileformat_factory();
    $this->add('select', 'file_format', E::ts('Select File Format'), $factory->getTypes(), true, array(
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
    ));
    $this->add('file', 'file', E::ts('Import file (CSV)'), [], TRUE);
    $this->addRule('file', E::ts('Upload a file.'), 'uploadedfile');
    $this->addCheckBox('overwrite', E::ts('When config item set already exists'), ['1' => E::ts('Overwrite')], NULL, NULL, NULL, NULL,'<br />', TRUE);

    $this->addButtons(array(
      array('type' => 'upload', 'name' => E::ts('Upload & Next'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))));
  }

  function setDefaultValues() {
    $defaults = [];
    $factory = civiconfig_get_fileformat_factory();
    $types = $factory->getTypes();
    if (count($types) == 1) {
      $defaults['file_format'] = array_key_first($types);
    }

    return $defaults;
  }

  public function postProcess() {
    $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig', array('reset' => 1));
    $factory = civiconfig_get_fileformat_factory();
    $file_format = $this->_submitValues['file_format'];
    $file = $this->_submitFiles['file']['tmp_name'];
    $importer = $factory->getFileFormatClass($file_format);
    if ($config_item_set = $importer->validate($file)) {
      $config_item_set = $importer->upload($file, $config_item_set);
      $config_item_set['import_file_format'] = $file_format;
      if (isset($config_item_set['id']) && $config_item_set['id']) {
        $this->id = $config_item_set['id'];
        unset($config_item_set['id']);
        civicrm_api4('ConfigItemSet', 'update', [
          'values' => $config_item_set,
          'where' => [['id', '=', $this->id]],
        ]);
      } else {
        $result = civicrm_api4('ConfigItemSet', 'create', ['values' => $config_item_set]);
        $this->id = $result[0]['id'];
      }

      $this->loadConfigItemSet();
      $nextTab = $this->getNextTab();
      if ($nextTab) {
        $redirectUrl = $nextTab['link'];
      }
    }

    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Overridden parent method to add validation rules
   *
   */
  public function addRules() {
    if ($this->controller->getPrint() || $this->_action & CRM_Core_Action::ADD) {
      $this->addFormRule(['CRM_Civiconfig_Form_Import', 'validateFile']);
    }
  }

  /**
   * Method to validate if file has the correct ext and can be opened
   *
   * @param $fields
   * @param $files
   * @return bool|array
   * @throws
   */
  public static function validateFile($fields, $files) {
    $errors = [];
    $factory = civiconfig_get_fileformat_factory();
    $file_format = $fields['file_format'];
    $file = $files['file']['tmp_name'];
    $importer = $factory->getFileFormatClass($file_format);
    $overwrite = false;
    if (isset($fields['overwrite'][1]) && $fields['overwrite'][1]) {
      $overwrite = true;
    }
    if ($config_item_set = $importer->validate($file)) {
      if (isset($config_item_set['id']) && !empty($config_item_set['id']) && !$overwrite) {
        $errors['file'] = E::ts('Imported file already exists.');
      }
    }
    if (count($errors)) {
      return $errors;
    }
    return TRUE;
  }

  /**
   * Returns a list with entities and their export configuration form.
   *
   * @param bool $reset
   * @return array
   * @throws \CRM_Civiconfig_EntityException
   */
  protected function getTabs($reset=FALSE) {
    $tabs = [];
    $context = ['config_item_set_id' => null];

    $active = 1;
    if ($this->_action & CRM_Core_Action::ADD) {
      $active = 0;
    }

    $url = CRM_Utils_System::url('civicrm/admin/civiconfig/import', ['reset' => 1, 'action' => 'add']);
    $tabs['upload'] = [
      'title' => E::ts('Upload'),
      'active' => 1,
      'valid' => $active ? 0 : 1,
      'link' => $url,
      'current' => false,
    ];

    $factory = civiconfig_get_entity_factory();
    if(empty($this->configItemSet)){
      foreach ($factory->getEntityListForConfigItemSet($this->configItemSet) as $entityName) {
        $entityClass = $factory->getEntityDefinition($entityName);
        if (!$entityClass->getImporterClass() || !$entityClass->getImporterClass()
            ->getImportConfigurationForm()) {
          continue;
        }
        if (empty($this->configItemSet) || !$entityClass->getImporterClass()
            ->entityImportDataExists($this->configItemSet)) {
          continue;
        }
        $entityImportForm = $entityClass->getImporterClass()
          ->getImportConfigurationForm();
        if ($entityImportForm instanceof Tab) {
          $entityConfiguration = [];
          if (isset($this->configItemSet['configuration']) && isset($this->configItemSet['configuration'][$entityName])) {
            $entityConfiguration = $this->configItemSet['configuration'][$entityName];
          }
          $tabs = $entityImportForm->getTabs($tabs, $entityConfiguration, $this->configItemSet, $reset);
        }
      }
      foreach ($factory->getDecorators() as $decorator) {
        $decoratorName = $decorator->getName();
        $decoratorImportForm = $decorator->getImportConfigurationForm();
        if ($decoratorImportForm && $decoratorImportForm instanceof Tab) {
          $decoratorConfiguration = [];
          if (isset($this->configItemSet['configuration']) && isset($this->configItemSet['configuration'][$decoratorName])) {
            $decoratorConfiguration = $this->configItemSet['configuration'][$decoratorName];
          }
          $tabs = $decoratorImportForm->getTabs($tabs, $decoratorConfiguration, $this->configItemSet, $reset);
        }
      }
    }
    if ($this->getCurrentTabName()) {
      $tabs[$this->getCurrentTabName()]['current'] = true;
    }
    CRM_Utils_Hook::tabset('civicrm/admin/civiconfig/import', $tabs, $context);
    return $tabs;
  }

  protected function getNextTab() {
    $nextTab = false;
    foreach($this->tabs as $tabName => $tab) {
      if ($nextTab) {
        return $tab;
      } elseif ($tabName == $this->getCurrentTabName() || empty($this->getCurrentTabName())) {
        $nextTab = true;
      }
    }
    return null;
  }

  /**
   * @return string
   */
  protected function getCurrentTabName() {
    return null;
  }

  /**
   * Load the config item set.
   * @throws \API_Exception
   * @throws \CRM_Civiconfig_EntityException
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function loadConfigItemSet() {
    $this->configItemSet = null;
    if ($this->id) {
      $this->configItemSet = Civi\Api4\ConfigItemSet::get()
        ->addWhere('id', '=', $this->id)
        ->setLimit(1)
        ->execute()
        ->first();
      $this->assign('config_item_set', $this->configItemSet);
    }
    $this->tabs = $this->getTabs(TRUE);
    $this->assign('tabHeader', $this->tabs);
  }

  /**
   * @return string
   */
  public function getTemplateFileName() {
    if ($this->isTabContent()) {
      return parent::getTemplateFileName();
    }
    else {
      // hack lets suppress the form rendering for now
      self::$_template->assign('isForm', FALSE);
      return 'CRM/common/TabHeader.tpl';
    }
  }

  /**
   * Returns whether we are displaying the tab contents.
   *
   * @return bool
   */
  protected function isTabContent() {
    if ($this->controller->getPrint() || $this->getVar('id') <= 0 || $this->_action & CRM_Core_Action::DELETE || $this->isSubmitted()) {
      return TRUE;
    }
    return FALSE;
  }

}
