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
use Civi\ConfigItems\Entity\EntityImporter;
use Civi\ConfigItems\FileFormat\EntityImportDataException;
use CRM_Civiconfig_ExtensionUtil as E;

class Importer implements EntityImporter {

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
   * Returns the entity definition
   *
   * @return \Civi\ConfigItems\Entity\EntityDefinition
   */
  public function getEntityDefinition() {
    return $this->entityDefinition;
  }

  /**
   * Returns the import configuration form.
   * Returns false if this entity does not have a configuration for import.
   *
   * @return false|ConfigurationForm
   */
  public function getImportConfigurationForm() {
    return new ImportForm($this);
  }

  /**
   * Add tasks to the import queue.
   *
   * You can add multiple tasks, for example if a task might take long, such as
   * installing an extension you can add a task for each extension. This way we
   * prevent browser timeouts.
   *
   * @param \Civi\ConfigItems\QueueService $queue
   * @param $configuration
   * @param $config_item_set
   *
   * @return void
   */
  public function addImportTasksToQueue(\Civi\ConfigItems\QueueService $queue, $configuration, $config_item_set) {
    if ($this->entityImportDataExists($config_item_set)) {
      $callback = [static::class, 'runImportTask'];
      $params = [
        $configuration,
        $config_item_set,
        $this->entityDefinition->getName()
      ];
      $entityTitle = $this->entityDefinition->getTitlePlural();
      $queue->addCallbackToCurrentTask($entityTitle, $callback, $params);
    }
  }

  /**
   * Import data.
   *
   * @param $configuration
   * @param $config_item_set
   * @param \CRM_Queue_TaskContext $ctx
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public function import($configuration, $config_item_set, \CRM_Queue_TaskContext $ctx) {
    $entityData = $this->loadEntityImportData($config_item_set);
    $entityData = $this->entityDefinition->alterEntityDataForImport($entityData, $configuration, $config_item_set);
    foreach($configuration['include'] as $customGroupName => $toInclude) {
      if ($toInclude && isset($entityData[$customGroupName]['id'])) {
        // Update
        $custom_group_id = $entityData[$customGroupName]['id'];
        $params = [];
        $params['values'] = $entityData[$customGroupName];
        unset($params['values']['fields']);
        unset($params['values']['existing_fields']);
        $params['where'][] = ['id', '=', $custom_group_id];
        try {
          civicrm_api4('CustomGroup', 'update', $params);
        } catch (\API_Exception $ex) {
          \CRM_Core_Session::setStatus($ex->getMessage(), E::ts("Could not update custom group %1", [1=>$entityData[$customGroupName]['title']]), 'error');
        }
      } elseif ($toInclude) {
        // Add
        $params = [];
        $params['values'] = $entityData[$customGroupName];
        unset($params['values']['fields']);
        unset($params['values']['existing_fields']);
        try {
          $result = civicrm_api4('CustomGroup', 'create', $params);
          $custom_group_id = $result->first()['id'];
        } catch (\API_Exception $ex) {
          \CRM_Core_Session::setStatus($ex->getMessage(), E::ts("Could not add custom group %1", [1=>$entityData[$customGroupName]['title']]), 'error');
        }
      }
      foreach($configuration['include_fields'][$customGroupName] as $customFieldName => $toIncludeValue) {
        if ($custom_group_id && $toIncludeValue && isset($entityData[$customGroupName]['fields'][$customFieldName]['id'])) {
          // Update
          $params = [];
          $params['values'] = $entityData[$customGroupName]['fields'][$customFieldName];
          $params['values']['custom_group_id'] = $custom_group_id;
          if (isset($params['values']['option_group_name'])) {
            $params['values']['option_group_id'] = $this->optionGroupNameToId($params['values']['option_group_name']);
            unset($params['values']['option_group_name']);
          }
          $params['where'][] = ['id', '=', $entityData[$customGroupName]['fields'][$customFieldName]['id']];
          try {
            civicrm_api4('CustomField', 'update', $params);
          } catch (\API_Exception $ex) {
            \CRM_Core_Session::setStatus($ex->getMessage(), E::ts("Could not update custom field %1 in custom group %2", [1=>$entityData[$customGroupName]['values'][$customFieldName]['title'], 2=>$entityData[$customGroupName]['title']]), 'error');
          }
        } elseif ($custom_group_id && $toIncludeValue) {
          // Add
          $params = [];
          $params['values'] = $entityData[$customGroupName]['fields'][$customFieldName];
          if (isset($params['values']['option_group_name'])) {
            $params['values']['option_group_id'] = $this->optionGroupNameToId($params['values']['option_group_name']);
            unset($params['values']['option_group_name']);
          }
          $params['values']['custom_group_id'] = $custom_group_id;
          try {
            civicrm_api4('CustomField', 'create', $params);
          } catch (\API_Exception $ex) {
            \CRM_Core_Session::setStatus($ex->getMessage(), E::ts("Could not add custom field %1 in custom group %2", [1=>$entityData[$customGroupName]['values'][$customFieldName]['title'], 2=>$entityData[$customGroupName]['title']]), 'error');
          }
        }
      }
      foreach($configuration['remove_fields'][$customGroupName] as $customFieldName => $toRemoveValue) {
        if ($toRemoveValue && isset($entityData[$customGroupName]['existing_fields'][$customFieldName]['id'])) {
          // Delete
          $params = [];
          $params['where'][] = ['id', '=', $entityData[$customGroupName]['existing_fields'][$customFieldName]['id']];
          try {
            civicrm_api4('CustomField', 'delete', $params);
          } catch (\API_Exception $ex) {
            \CRM_Core_Session::setStatus($ex->getMessage(), E::ts("Could not delete custom field %1 in custom group %2", [1=>$entityData[$customGroupName]['fields'][$customFieldName]['title'], 2=>$entityData[$customGroupName]['title']]), 'error');
          }
        }
      }
    }
    foreach($configuration['remove'] as $customGroupName => $toBeRemoved) {
      if ($toBeRemoved && isset($entityData[$customGroupName]['id'])) {
        try {
          civicrm_api4('CustomGroup', 'delete', ['where' => [['id', '=', $entityData[$customGroupName]['id']]]]);
        } catch (\API_Exception $ex) {
          \CRM_Core_Session::setStatus($ex->getMessage(), E::ts("Could not delete custom group %1", [1=>$entityData[$customGroupName]['title']]), 'error');
        }
      }
    }
  }

  /**
   * Run the import task.
   *
   * @param $configuration
   * @param $config_item_set
   * @param $entityName
   * @param \CRM_Queue_TaskContext $ctx
   * @throws \Civi\API\Exception\NotImplementedException
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public static function runImportTask($configuration, $config_item_set, $entityName, \CRM_Queue_TaskContext $ctx) {
    $factory = civiconfig_get_entity_factory();
    /**
     * @var \Civi\ConfigItems\Entity\CustomGroup\Importer
     */
    $importer = $factory->getEntityDefinition($entityName)->getImporterClass();
    $importer->import($configuration, $config_item_set, $ctx);
  }

  /**
   * Checks whether import entity data exists.
   *
   * @param $config_item_set
   *
   * @return bool
   */
  public function entityImportDataExists($config_item_set) {
    if (empty($config_item_set['import_file_format'])) {
      return FALSE;
    }
    $fileFactory = civiconfig_get_fileformat_factory();
    $fileFormat = $fileFactory->getFileFormatClass($config_item_set['import_file_format']);
    try {
      $entityData = $fileFormat->loadEntityImportData($config_item_set, $this->entityDefinition->getName(), $this->getEntityDefinition()->getFileName());
      if (empty($entityData)) {
        return FALSE;
      }
    } catch (EntityImportDataException $ex) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Load the entity data.
   *
   * @param $config_item_set
   *
   * @return array
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public function loadEntityImportData($config_item_set) {
    $entityName = $this->entityDefinition->getName();
    $fileFactory = civiconfig_get_fileformat_factory();
    if (empty($config_item_set['import_file_format'])) {
      return [];
    }
    $fileFormat = $fileFactory->getFileFormatClass($config_item_set['import_file_format']);
    $exportConfig = $config_item_set['configuration'][$entityName];
    $entityData = $fileFormat->loadEntityImportData($config_item_set, $this->entityDefinition->getName(), $this->getEntityDefinition()->getFileName());
    $entityData = $this->checkIfCustomGroupsExists($entityData, $exportConfig);
    return $entityData;
  }

  /**
   * Check records in entity data whether they exists and if so add their ID to $entityData.
   *
   * @param $entityData
   *
   * @return array
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function checkIfCustomGroupsExists($entityData, $exportConfig) {
    foreach($entityData as $name => $data) {
      $result = civicrm_api4('CustomGroup', 'get', [
        'select' => ['id'],
        'where' => [['name', '=', $data['name']]],
        'orderBy' => ['id' => 'ASC'],
        'limit' => 1
      ]);
      if ($result->count()) {
        $id = $result->first()['id'];
        $entityData[$name]['id'] = $id;
        $customGroupExportConfig = $exportConfig['include'][$name];
        $customFields = civicrm_api4('CustomField', 'get', [
          'where' => [['custom_group_id', '=', $id]],
          'orderBy' => ['id' => 'ASC'],
          'limit' => 0
        ]);
        if (!isset($entityData[$name]['fields'])) {
          $entityData[$name]['fields'] = [];
        }
        $entityData[$name]['existing_fields'] = [];
        foreach($customFields as $customField) {
          if (isset($entityData[$name]['fields'][$customField['name']])) {
            $entityData[$name]['fields'][$customField['name']]['id'] = $customField['id'];
          } elseif (isset($customGroupExportConfig['propose_remove']) && $customGroupExportConfig['propose_remove']) {
            $entityData[$name]['existing_fields'][$customField['name']] = $customField;
          }
        }
      }
    }
    return $entityData;
  }

  /**
   * Returns the name of an option group
   *
   * @param $option_group_name
   *
   * @return mixed
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function optionGroupNameToId($option_group_name) {
    $optionGropup = \Civi\Api4\OptionGroup::get()->addWhere('name', '=', $option_group_name)->execute()->first();
    return $optionGropup['id'];
  }


}
