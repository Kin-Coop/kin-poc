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

class ImportForm implements ConfigurationForm, Tab {

  /**
   * @var \Civi\ConfigItems\Entity\Extension\Importer
   */
  protected $entityImporter;

  public function __construct(Importer $entityImporter) {
    $this->entityImporter = $entityImporter;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->entityImporter->getEntityDefinition()->getTitlePlural();
  }

  /**
   * @return string
   */
  public function getHelpText() {
    return $this->entityImporter->getHelpText();
  }

  /**
   * @param \CRM_Core_Form $form
   * @param array $configuration
   * @param array $config_item_set
   */
  public function buildConfigurationForm(\CRM_Core_Form $form, $configuration, $config_item_set) {
    $entityData = $this->entityImporter->loadEntityImportData($config_item_set);
    $form->assign('helpText', $this->getHelpText());
    $defaults = [];
    $elementNames = [];
    foreach($entityData as $key => $extension) {
      $options = $this->getOptions($extension);
      if (count($options) > 1) {
        $name = \CRM_Utils_String::munge($extension['key']);
        $elementNames[$extension['key']] = $name;
        $form->addRadio($name, $extension['key'], $options, ['allowClear' => FALSE], '<br />', TRUE);
        if (isset($configuration[$extension['key']])) {
          $defaults[$name] = $configuration[$extension['key']];
        } else {
          $defaults[$name] = $this->getDefaultOption($extension);
        }
      }
    }
    $form->assign('elementNames', $elementNames);
    $form->assign('extensions', $entityData);
    $form->setDefaults($defaults);
    $form->assign('downloadSourceOptions', ExportForm::downloadSourceOptions());
  }

  protected function getOptions($extension) {
    if ($extension['download_source'] == 'uninstall' && isset($extension['path'])) {
      return [
        '0' => E::ts('Keep'),
        'uninstall' => E::ts('Uninstall and remove')
      ];
    } elseif ($extension['download_source'] == 'uninstall') {
      return [
        '0' => E::ts('Extension not installed'),
      ];
    } if (isset($extension['path'])) {
      if (isset($extension['current_version']) && !empty($extension['branch']) && version_compare($extension['branch'], $extension['current_version']) == 1) {
        return [
          '0' => E::ts('Keep'),
          'upgrade' => E::ts('Upgrade')
        ];
      } elseif (isset($extension['current_version']) && !empty($extension['branch']) && version_compare($extension['branch'], $extension['current_version']) == -1) {
        return [
          '0' => E::ts('Keep'),
          'downgrade' => E::ts('Downgrade')
        ];
      } else {
        return [
          '0' => E::ts('Keep'),
          'replace' => E::ts('Replace')
        ];
      }
    } else {
      return [
        '0' => E::ts('Do not install'),
        'install' => E::ts('Install extension')
      ];
    }
  }

  protected function getDefaultOption($extension) {
    if ($extension['download_source'] == 'uninstall' && isset($extension['path'])) {
      return '0';
    } elseif ($extension['download_source'] == 'uninstall') {
      return '0';
    } if (isset($extension['path'])) {
      if (isset($extension['current_version']) && !empty($extension['branch']) && version_compare($extension['branch'], $extension['current_version']) == 1) {
        return 'upgrade';
      } else {
        return '0';
      }
    } else {
      return 'install';
    }
  }

  /**
   * Returns the name of the template for the configuration form.
   *
   * @return string
   */
  public function getConfigurationTemplateFileName() {
    return "CRM/ConfigItems/Entity/Extension/ImportForm.tpl";
  }

  /**
   * Process the submitted values and create a configuration array
   *
   * @param $submittedValues
   * @param array $config_item_set
   *
   * @return array
   */
  public function processConfiguration($submittedValues, $config_item_set) {
    $entityData = $this->entityImporter->loadEntityImportData($config_item_set);
    $config = [];
    foreach($entityData as $extension) {
      $name = \CRM_Utils_String::munge($extension['key']);
      if (isset($submittedValues[$name])) {
        $config[$extension['key']] = $submittedValues[$name];
      } else {
        $config[$extension['key']] = 0;
      }
    }
    return $config;
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
    $entityName = $this->entityImporter->getEntityDefinition()->getName();
    $url = \CRM_Utils_System::url('civicrm/admin/civiconfig/import/entity', ['reset' => 1, 'id' => $config_item_set['id'], 'entity' => $entityName]);
    $count = count($configuration);
    if ($count) {
      $tabset[$entityName] = [
        'title' => $this->entityImporter->getEntityDefinition()
          ->getTitlePlural(),
        'active' => 1,
        'valid' => 1,
        'link' => $url,
        'current' => FALSE,
        'count' => $count,
      ];
    }
    return $tabset;
  }


}
