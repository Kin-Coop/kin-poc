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

namespace Civi\ConfigItems\Entity\OptionGroup;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\Entity\EntityExporter;
use Civi\ConfigItems\FileFormat\EntityImportDataException;
use CRM_Civiconfig_ExtensionUtil as E;

class Exporter implements EntityExporter {

  /**
   * @var \Civi\ConfigItems\Entity\OptionGroup\Definition;
   */
  protected $entityDefinition;

  public function __construct(Definition $entityDefinition) {
    $this->entityDefinition = $entityDefinition;
  }

  /**
   * Returns the help text.
   * Return an empty string if no help is available.
   *
   * @return string
   */
  public function getHelpText() {
    return '';
  }


  /**
   * Exports the entity
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   *
   * @return array
   */
  public function export($configuration, $config_item_set, $directory = '') {
    $data = [];
    try {
      $importData = [];
      if ($this->entityDefinition->getImporterClass()->entityImportDataExists($config_item_set)) {
        $importData = $this->entityDefinition
          ->getImporterClass()
          ->loadEntityImportData($config_item_set);
      }
    } catch (EntityImportDataException $ex) {
      // Do nothing.
    }

    if (isset($configuration['include'])) {
      foreach ($configuration['include'] as $optionGroupName => $item) {
        $data[$optionGroupName] = \Civi\Api4\OptionGroup::get()
          ->addWhere('name', '=', $optionGroupName)
          ->execute()
          ->first();
        unset($data[$optionGroupName]['id']);
        $data[$optionGroupName]['values'] = [];
        $optionValues = \Civi\Api4\OptionValue::get()
          ->addWhere('option_group_id:name', '=', $optionGroupName)
          ->execute();
        foreach ($optionValues as $optionValue) {
          if ($item['select_all_values'] || in_array($optionValue['name'], $item['include']) || in_array($optionValue['name'], $item['remove'])) {
            $data[$optionGroupName]['values'][$optionValue['name']] = $optionValue;
            unset($data[$optionGroupName]['values'][$optionValue['name']]['id']);
            unset($data[$optionGroupName]['values'][$optionValue['name']]['option_group_id']);
            unset($data[$optionGroupName]['values'][$optionValue['name']]['domain_id']);
          }
        }
        unset($importData[$optionGroupName]);
      }
    }
    if (isset($configuration['remove'])) {
      foreach ($configuration['remove'] as $optionGroupName) {
        $data[$optionGroupName] = \Civi\Api4\OptionGroup::get()
          ->addWhere('name', '=', $optionGroupName)
          ->execute()
          ->first();
        unset($data[$optionGroupName]['id']);
        $data[$optionGroupName]['values'] = [];
        $optionValues = \Civi\Api4\OptionValue::get()
          ->addWhere('option_group_id:name', '=', $optionGroupName)
          ->execute();
        foreach ($optionValues as $optionValue) {
            $data[$optionGroupName]['values'][$optionValue['name']] = $optionValue;
            unset($data[$optionGroupName]['values'][$optionValue['name']]['id']);
            unset($data[$optionGroupName]['values'][$optionValue['name']]['option_group_id']);
            unset($data[$optionGroupName]['values'][$optionValue['name']]['domain_id']);
        }
        unset($importData[$optionGroupName]);
      }
    }
    foreach($importData as $optionGroupName => $item) {
      $data[$optionGroupName] = $item;
    }
    $data = $this->entityDefinition->alterEntityDataForExport($data, $configuration, $config_item_set);
    return $data;
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return false|ConfigurationForm
   */
  public function getExportConfigurationForm() {
    return new ExportForm($this);
  }

  /**
   * Returns the entity definition
   *
   * @return \Civi\ConfigItems\Entity\EntityDefinition
   */
  public function getEntityDefinition() {
    return $this->entityDefinition;
  }


}
