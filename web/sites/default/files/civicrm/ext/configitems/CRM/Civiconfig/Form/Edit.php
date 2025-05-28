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

use Civi\ConfigItems\ConfigurationFormCountable;
use Civi\ConfigItems\Tab;
use CRM_Civiconfig_ExtensionUtil as E;

/**
 * Form controller class
 */
class CRM_Civiconfig_Form_Edit extends CRM_Core_Form {

  /**
   * @var int
   */
  protected $id;

  protected $configItemSet;

  protected $tabs;

  public function preProcess() {
    parent::preProcess();
    $this->setTitle(E::ts('Edit Configuration Set'));
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    $this->loadConfigItemSet();
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'templates/CRM/common/TabHeader.js', 1, 'html-header')
      ->addSetting([
        'tabSettings' => [
          'active' => $this->getCurrentTabName()
        ],
      ]);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    if ($this->_action != CRM_Core_Action::DELETE) {
      $factory = civiconfig_get_entity_factory();
      $entities = [];
      foreach($factory->getEntityList() as $entityName) {
        $entityClass = $factory->getEntityDefinition($entityName);
        $entities[$entityClass->getTitlePlural()] = $entityName;
      }
      $this->add('text', 'name', E::ts('Name'), array('size' => CRM_Utils_Type::HUGE), FALSE);
      $this->add('text', 'title', E::ts('Title'), array('size' => CRM_Utils_Type::HUGE), TRUE);
      $this->add('text', 'version', E::ts('Version'), array('size' => CRM_Utils_Type::EIGHT), TRUE);
      $this->addRule('version', E::ts('Version should be a number'), 'numeric');
      $this->add('textarea', 'description', E::ts('Description'), array('size' => 100, 'maxlength' => 256, 'style' => 'width: 600px;'));
      $this->addCheckBox('entities', E::ts('Entities'), $entities, NULL, ['class' => 'entities_checkbox']);
    }
    if ($this->_action == CRM_Core_Action::ADD) {
      CRM_Utils_System::setTitle(E::ts('Add configuration set'));
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Save & Next'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } elseif ($this->_action == CRM_Core_Action::DELETE) {
      CRM_Utils_System::setTitle(E::ts('Delete configuration set: %1', [1=>$this->configItemSet['title']]));
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Delete'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    } else {
      CRM_Utils_System::setTitle(E::ts('Edit configuration set: %1', [1=>$this->configItemSet['title']]));
      $this->addButtons(array(
        array('type' => 'next', 'name' => E::ts('Save & Next'), 'isDefault' => TRUE,),
        array('type' => 'cancel', 'name' => E::ts('Cancel'))));
    }
    parent::buildQuickForm();
  }

  /**
   * Function to set default values (overrides parent function)
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = array();
    $defaults['id'] = $this->id;
    switch ($this->_action) {
      case CRM_Core_Action::ADD:
        $this->setAddDefaults($defaults);
        break;
      case CRM_Core_Action::UPDATE:
        $this->setUpdateDefaults($defaults);
        break;
    }
    return $defaults;
  }

  /**
   * Function to set default values if action is add
   *
   * @param array $defaults
   * @access protected
   */
  protected function setAddDefaults(&$defaults) {
    $defaults['version'] = '1';
    $factory = civiconfig_get_entity_factory();
    foreach($factory->getEntityList() as $entityName) {
      $defaults['entities'][$entityName] = '1';
    }
    return $defaults;
  }

  /**
   * Function to set default values if action is update
   *
   * @param array $defaults
   * @access protected
   */
  protected function setUpdateDefaults(&$defaults) {
    if (!empty($this->configItemSet) && !empty($this->configItemSet)) {
      $defaults['title'] = $this->configItemSet['title'];
      if (isset($this->configItemSet['name'])) {
        $defaults['name'] = $this->configItemSet['name'];
      }
      if (isset($this->configItemSet['description'])) {
        $defaults['description'] = $this->configItemSet['description'];
      } else {
        $defaults['description'] = '';
      }
      if (isset($this->configItemSet['version'])) {
        $defaults['version'] = $this->configItemSet['version'];
      } else {
        $defaults['version'] = '1';
      }
      if (isset($this->configItemSet['entities'])) {
        $defaults['entities'] = $this->configItemSet['entities'];
      }
    }
  }

  public function postProcess() {
    $session = CRM_Core_Session::singleton();
    if ($this->_action == CRM_Core_Action::DELETE) {
      Civi\Api4\ConfigItemSet::delete()->addWhere('id', '=', $this->id)->execute();
      $session->setStatus(E::ts('Configuration set removed'), E::ts('Removed'), 'success');
      $redirectUrl = $session->popUserContext();
      CRM_Utils_System::redirect($redirectUrl);
    }

    $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig', array('reset' => 1));
    $values = $this->exportValues();
    $params['name'] = $values['name'];
    $params['title'] = $values['title'];
    $params['description'] = $values['description'];
    $params['version'] = $values['version'];
    $params['entities'] = $values['entities'];
    if ($this->id) {
      civicrm_api4('ConfigItemSet', 'update', [
        'values' => $params,
        'where' => [['id', '=', $this->id]],
      ]);
    } else {
      $result = civicrm_api4('ConfigItemSet', 'create', ['values' => $params]);
      $this->id = $result[0]['id'];
    }

    $this->loadConfigItemSet();

    $nextTab = $this->getNextTab();
    if ($nextTab) {
      $redirectUrl = $nextTab['link'];
    }


    CRM_Utils_System::redirect($redirectUrl);
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
    $context = ['config_item_set_id' => $this->id];

    $url = CRM_Utils_System::url('civicrm/admin/civiconfig/edit', ['reset' => 1, 'id' => $this->id, 'action' => 'update']);
    $tabs['info'] = [
      'title' => E::ts('Info'),
      'active' => 1,
      'valid' => 1,
      'link' => $url,
      'current' => false,
    ];

    $factory = civiconfig_get_entity_factory();
    foreach($factory->getEntityListForConfigItemSet($this->configItemSet) as $entityName) {
      $entityClass = $factory->getEntityDefinition($entityName);
      $entityExportForm = null;
      if ($entityClass->getExporterClass() && $entityClass->getExporterClass()->getExportConfigurationForm()) {
        $entityExportForm = $entityClass->getExporterClass()->getExportConfigurationForm();
      }
      if ($entityExportForm instanceof Tab) {
        $entityConfiguration = [];
        if (isset($this->configItemSet['configuration']) && isset($this->configItemSet['configuration'][$entityName])) {
          $entityConfiguration = $this->configItemSet['configuration'][$entityName];
        }
        $tabs = $entityExportForm->getTabs($tabs, $entityConfiguration, $this->configItemSet, $reset);
      }
    }
    foreach($factory->getDecorators() as $decorator) {
      $decoratorName = $decorator->getName();
      $decoratorExportForm = $decorator->getExportConfigurationForm();
      if ($decoratorExportForm && $decoratorExportForm instanceof Tab) {
        $decoratorConfiguration = [];
        if (isset($this->configItemSet['configuration']) && isset($this->configItemSet['configuration'][$decoratorName])) {
          $decoratorConfiguration = $this->configItemSet['configuration'][$decoratorName];
        }
        $tabs = $decoratorExportForm->getTabs($tabs, $decoratorConfiguration, $this->configItemSet, $reset);
      }
    }
    if ($this->getCurrentTabName()) {
      $tabs[$this->getCurrentTabName()]['current'] = true;
    }
    CRM_Utils_Hook::tabset('civicrm/admin/civiconfig/edit', $tabs, $context);
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
    } else {
      $this->assign('config_item_set',FALSE);
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

  /**
   * Function to add validation rules (overrides parent function)
   *
   * @access public
   */
  function addRules() {
    if ($this->_action != CRM_Core_Action::DELETE) {
      $this->addFormRule(array(
        'CRM_Civiconfig_Form_Edit',
        'validateName'
      ));
    }
  }

  /**
   * Function to validate if rule label already exists
   *
   * @param array $fields
   * @return array|bool
   * @access static
   */
  public static function validateName($fields) {
    /*
     * if id not empty, edit mode. Check if changed before check if exists
     */
    $id = false;
    if (!empty($fields['id'])) {
      $id = $fields['id'];
    }
    if (empty($fields['name']) && !empty($fields['name'])) {
      $fields['name'] = CRM_Civiconfig_BAO_ConfigItemSet::checkName($fields['title'], $id);
      if (!CRM_Civiconfig_BAO_ConfigItemSet::isNameValid($fields['name'], $id)) {
        $errors['name'] = E::ts('There is already a config item set with this name');
        return $errors;
      }
    }
    return TRUE;
  }

}
