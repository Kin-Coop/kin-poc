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

use Civi\ConfigItems\Entity\ImportDirectly;
use CRM_Civiconfig_ExtensionUtil as E;

/**
 * Form controller class
 */
class CRM_Civiconfig_Form_ImportEntity extends CRM_Civiconfig_Form_Import {

  /**
   * @var Civi\ConfigItems\Entity\EntityImporter;
   */
  protected $entityImporterClass;

  /**
   * @var string
   */
  protected $entityName;

  public function preProcess() {
    $this->entityName = CRM_Utils_Request::retrieve('entity', 'String', $this, TRUE);
    $factory = civiconfig_get_entity_factory();
    $this->entityImporterClass = $factory->getEntityDefinition($this->entityName)->getImporterClass();
    $this->assign('entityName', $this->entityName);
    parent::preProcess();
  }

  public function buildQuickForm() {
    $this->add('hidden', 'id');
    if ($this->isTabContent()) {
      $this->assign('configuration_template', $this->entityImporterClass->getImportConfigurationForm()
        ->getConfigurationTemplateFileName());
      $configuration = [];
      if (isset($this->configItemSet['import_configuration']) && isset($this->configItemSet['import_configuration'][$this->entityName])) {
        $configuration = $this->configItemSet['import_configuration'][$this->entityName];
      }
      $this->entityImporterClass->getImportConfigurationForm()
        ->buildConfigurationForm($this, $configuration, $this->configItemSet);

      CRM_Utils_System::setTitle(E::ts('Edit configuration set: %1', [1 => $this->configItemSet['title']]));

      $nextButtonLabel = E::ts('Next');
      if ($this->getNextTab() === NULL) {
        // Is last tab.
        $nextButtonLabel = E::ts('Import');
      } elseif ($this->entityImporterClass instanceof ImportDirectly) {
        $nextButtonLabel = $this->entityImporterClass->getNextButtonTitle();
      }

      $this->addButtons([
        ['type' => 'next', 'name' => $nextButtonLabel, 'isDefault' => TRUE,],
        ['type' => 'cancel', 'name' => E::ts('Cancel')]
      ]);
    }
  }

  /**
   * Function to set default values (overrides parent function)
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = [];
    $defaults['id'] = $this->id;
    return $defaults;
  }

  public function postProcess() {
    $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig/import/run', array('reset' => 1, 'id' => $this->id));
    $submittedValues = $this->getSubmitValues();
    $this->configItemSet['import_configuration'][$this->entityName] = $this->entityImporterClass->getImportConfigurationForm()->processConfiguration($submittedValues, $this->configItemSet);
    $values['import_configuration'] = $this->configItemSet['import_configuration'];
    civicrm_api4('ConfigItemSet', 'update', [
      'values' => $values,
      'where' => [['id', '=', $this->id]],
    ]);
    $this->loadConfigItemSet();
    if ($this->entityImporterClass instanceof ImportDirectly && $this->entityImporterClass->importDirectly($this->configItemSet['import_configuration'], $this->configItemSet)) {
      $entityName = $this->entityImporterClass->getEntityDefinition()->getName();
      $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig/import/run', array('reset' => 1, 'id' => $this->id, 'entity' => $entityName));
    } else {
      $nextTab = $this->getNextTab();
      if ($nextTab) {
        $redirectUrl = $nextTab['link'];
      }
    }

    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * @return string
   */
  protected function getCurrentTabName() {
    return $this->entityName;
  }

}
