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

namespace Civi\ConfigItems\Entity\CustomGroup;

use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\Entity\EntityExporter;
use Civi\ConfigItems\FileFormat\EntityImportDataException;
use CRM_Civiconfig_ExtensionUtil as E;

class Exporter implements EntityExporter {

  /**
   * @var \Civi\ConfigItems\Entity\CustomGroup\Definition;
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
      foreach ($configuration['include'] as $customGroupName => $item) {
        $data[$customGroupName] = \Civi\Api4\CustomGroup::get()
          ->addWhere('name', '=', $customGroupName)
          ->execute()
          ->first();
        unset($data[$customGroupName]['id']);
        unset($data[$customGroupName]['created_id']);
        unset($data[$customGroupName]['created_date']);
        $data[$customGroupName]['fields'] = [];
        $customFields = \Civi\Api4\CustomField::get()
          ->addWhere('custom_group_id:name', '=', $customGroupName)
          ->execute();
        foreach ($customFields as $customField) {
          if ($item['select_all_fields'] || in_array($customField['name'], $item['include']) || in_array($customField['name'], $item['remove'])) {
            $data[$customGroupName]['fields'][$customField['name']] = $customField;
            unset($data[$customGroupName]['fields'][$customField['name']]['id']);
            unset($data[$customGroupName]['fields'][$customField['name']]['custom_group_id']);
            if (isset($data[$customGroupName]['fields'][$customField['name']]['option_group_id'])) {
              $data[$customGroupName]['fields'][$customField['name']]['option_group_name'] = $this->optionGroupIdToName($data[$customGroupName]['fields'][$customField['name']]['option_group_id']);
              unset($data[$customGroupName]['fields'][$customField['name']]['option_group_id']);
            }
          }
        }
        unset($importData[$customGroupName]);
      }
    }
    if (isset($configuration['remove'])) {
      foreach ($configuration['remove'] as $customGroupName) {
        $data[$customGroupName] = \Civi\Api4\CustomGroup::get()
          ->addWhere('name', '=', $customGroupName)
          ->execute()
          ->first();
        unset($data[$customGroupName]['id']);
        $data[$customGroupName]['fields'] = [];
        $customFields = \Civi\Api4\CustomField::get()
          ->addWhere('custom_group_id:name', '=', $customGroupName)
          ->execute();
        foreach ($customFields as $customField) {
          $data[$customGroupName]['fields'][$customField['name']] = $customField;
          unset($data[$customGroupName]['fields'][$customField['name']]['id']);
          unset($data[$customGroupName]['fields'][$customField['name']]['custom_group_id']);
          if (isset($data[$customGroupName]['fields'][$customField['name']]['option_group_id'])) {
            $data[$customGroupName]['fields'][$customField['name']]['option_group_name'] = $this->optionGroupIdToName($data[$customGroupName]['fields'][$customField['name']]['option_group_id']);
            unset($data[$customGroupName]['fields'][$customField['name']]['option_group_id']);
          }
        }
        unset($importData[$customGroupName]);
      }
    }
    foreach($importData as $customGroupName => $item) {
      $data[$customGroupName] = $item;
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

  /**
   * Returns the name of an option group
   *
   * @param $option_group_id
   *
   * @return mixed
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function optionGroupIdToName($option_group_id) {
    $optionGropup = \Civi\Api4\OptionGroup::get()->addWhere('id', '=', $option_group_id)->execute()->first();
    return $optionGropup['name'];
  }


}
