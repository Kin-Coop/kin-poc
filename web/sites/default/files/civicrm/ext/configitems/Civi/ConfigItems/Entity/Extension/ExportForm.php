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

namespace Civi\ConfigItems\Entity\Extension;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\Tab;
use CRM_Civiconfig_ExtensionUtil as E;

class ExportForm implements ConfigurationForm, Tab {

  /**
   * @var \Civi\ConfigItems\Entity\Extension\Exporter
   */
  protected $entityExporter;

  public function __construct(Exporter $entityExporter) {
    $this->entityExporter = $entityExporter;
  }

  public function getHelpText() {
    return $this->entityExporter->getHelpText();
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->entityExporter->getEntityDefinition()->getTitlePlural();
  }

  /**
   * @param \CRM_Core_Form $form
   * @param array $configuration
   * @param array $config_item_set
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $configuration, $config_item_set) {
    $entityName = $this->entityExporter->getEntityDefinition()->getName();
    $form->assign('id', $config_item_set['id']);
    $form->assign('helpText', $this->getHelpText());
    $form->assign('entityName', $entityName);
    $form->assign('configuration', $configuration);
    $form->assign('downloadSourceOptions', self::downloadSourceOptions());
  }

  /**
   * Returns the name of the template for the configuration form.
   *
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/Entity/Extension/ExportForm.tpl";
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $config_item_set
   * @return array
   */
  public function processConfiguration($submittedValues, $config_item_set) {
    $entityName = $this->entityExporter->getEntityDefinition()->getName();
    $configuration = [];
    if (isset($config_item_set['configuration']) && isset($config_item_set['configuration'][$entityName])) {
      $configuration = $config_item_set['configuration'][$entityName];
    }
    return $configuration;
  }

  /**
   * This function is called to add tabs to the tabset.
   * Returns the $tabset
   *
   * @param $tabset
   * @param $configuration
   * @param $config_item_set
   * @param bool $reset
   * @return array
   */
  public function getTabs($tabset, $configuration, $config_item_set, $reset=FALSE) {
    $entityName = $this->entityExporter->getEntityDefinition()->getName();
    $url = \CRM_Utils_System::url('civicrm/admin/civiconfig/edit/entity', ['reset' => 1, 'id' => $config_item_set['id'], 'entity' => $entityName]);
    $count = count($configuration);
    $tabset[$entityName] = [
      'title' => $this->entityExporter->getEntityDefinition()->getTitlePlural(),
      'active' => 1,
      'valid' => 1,
      'link' => $url,
      'current' => false,
      'count' => $count,
    ];
    return $tabset;
  }

  public static function downloadSourceOptions() {
    return [
      'git' => E::ts('Gitlab / Github'),
      'zip' => E::ts('ZIP File'),
      'download' => E::ts('CiviCRMs Extension Directory'),
      'uninstall' => E::ts('Uninstall from target system'),
    ];
  }

  public static function validateSource($source, $values) {
    $errors = [];
    if ($source == 'git') {
      if (0 !== substr_compare($values['url'], '.git', - strlen('.git'))) {
        $errors['url'] = E::ts('Not a valid GIT source. The URL should end with .git');
      }
    }
    return $errors;
  }

}
