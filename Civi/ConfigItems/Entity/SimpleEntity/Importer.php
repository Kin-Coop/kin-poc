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

namespace Civi\ConfigItems\Entity\SimpleEntity;

use Civi\ConfigItems\Entity\EntityImporter;
use Civi\ConfigItems\FileFormat\EntityImportDataException;
use CRM_Civiconfig_ExtensionUtil as E;

class Importer implements EntityImporter {

  /**
   * @var \Civi\ConfigItems\Entity\SimpleEntity\Definition
   */
  protected $entityDefinition;

  /**
   * @var \Civi\ConfigItems\ConfigurationForm
   */
  protected $form;

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
    return $this->entityDefinition->getImportHelpText();
  }

  /**
   * Load the entity data.
   *
   * @param $config_item_set
   * @return array
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public function loadEntityImportData($config_item_set) {
    $fileFactory = civiconfig_get_fileformat_factory();
    if (empty($config_item_set['import_file_format'])) {
      return [];
    }
    $fileFormat = $fileFactory->getFileFormatClass($config_item_set['import_file_format']);
    $entityData = $fileFormat->loadEntityImportData($config_item_set, $this->entityDefinition->getName(), $this->getEntityDefinition()->getFileName());
    foreach($this->entityDefinition->getExporterClass()->getGroups() as $group => $groupTitle) {
      if (isset($entityData[$group])) {
        $entityData[$group] = $this->checkEntityDataForExistence($entityData[$group]);
      }
    }
    return $entityData;
  }

  /**
   * Load the entity data.
   *
   * @param $config_item_set
   * @return bool
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
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
   * Check records in entity data whether they exists and if so add their ID to $entityData.
   *
   * @param $entityData
   *
   * @return array
   * @throws \API_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  protected function checkEntityDataForExistence($entityData) {
    $entity = $this->entityDefinition->getApiEntityName();
    foreach($entityData as $name => $data) {
      $result = civicrm_api4($entity, 'get', [
        'select' => [$this->getEntityDefinition()->getIdAttribute()],
        'where' => [[$this->entityDefinition->getNameAttribute(), '=', $data[$this->entityDefinition->getNameAttribute()]]],
        'orderBy' => [$this->getEntityDefinition()->getIdAttribute() => 'ASC'],
        'limit' => 1
      ]);
      if ($result->count()) {
        $entityData[$name][$this->getEntityDefinition()->getIdAttribute()] = $result->first()[$this->getEntityDefinition()->getIdAttribute()];
      }
    }
    return $entityData;
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
   * @return false|\Civi\ConfigItems\ConfigurationForm
   */
  public function getImportConfigurationForm() {
    if (!$this->form) {
      $this->form = new ImportForm($this);
    }
    return $this->form;
  }

  /**
   * @return array
   */
  public function getGroups() {
    return [
      'include' => E::ts('Include %1', [1=>$this->entityDefinition->getTitlePlural()]),
      'remove' => E::ts('Removed %1', [1=>$this->entityDefinition->getTitlePlural()]),
    ];
  }

  /**
   * @param $group
   * @param $data
   *
   * @return array
   */
  public function getOptions($group, $data) {
    if ($group == 'include' && isset($data[$this->entityDefinition->getIdAttribute()])) {
      return [
        0 => E::ts('Do not update'),
        1 => E::ts('Update'),
      ];
    } elseif ($group == 'include' && !isset($data[$this->entityDefinition->getIdAttribute()])) {
      return [
        1 => E::ts('Add'),
        0 => E::ts('Do not add'),
      ];
    } elseif ($group == 'remove' && isset($data[$this->entityDefinition->getIdAttribute()])) {
      return  [
        0 => E::ts('Keep'),
        1 => E::ts('Remove')
      ];
    }
    return [];
  }

  /**
   * @param $group
   * @param $data
   *
   * @return string|void
   */
  public function getDefaultOption($group, $data) {
    if ($group == 'include' && isset($data[$this->entityDefinition->getIdAttribute()])) {
      return '0';
    } elseif ($group == 'include' && !isset($data[$this->entityDefinition->getIdAttribute()])) {
      return '1';
    } elseif ($group == 'remove') {
      return  '0';
    }
  }

  /**
   * Add tasks to the import queue.
   *
   * You can add multiple tasks, for example if a task might take long, such as installing
   * an extension you can add a task for each extension. This way we prevent browser timeouts.
   *
   * @param \Civi\ConfigItems\QueueService $queue
   * @param $configuration
   * @param $config_item_set
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
    $entityName = $this->entityDefinition->getApiEntityName();
    $nameAttribute = $this->entityDefinition->getNameAttribute();
    $titleAttribute = $this->entityDefinition->getTitleAttribute();
    $groups = $this->entityDefinition->getExporterClass()->getGroups();
    foreach($groups as $group => $groupTitle) {
      foreach ($entityData[$group] as $data) {
        if (isset($configuration[$group]) && isset($configuration[$group][$data[$nameAttribute]]) && $configuration[$group][$data[$nameAttribute]]) {
          $apiAction = $this->getApiAction($group, $data);
          if ($apiAction) {
            $params = $this->getApiParams($group, $data, $configuration[$group][$data[$nameAttribute]]);
            try {
              civicrm_api4($entityName, $apiAction, $params);
            } catch (\API_Exception $ex) {
              \CRM_Core_Session::setStatus($ex->getMessage(), E::ts("Could not %1 '%2' %3", [1=>$apiAction, 2=>$data[$titleAttribute], 3=>$this->entityDefinition->getTitleSingle()]), 'error');
            }
          }
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
     * @var \Civi\ConfigItems\Entity\SimpleEntity\Importer
     */
    $importer = $factory->getEntityDefinition($entityName)->getImporterClass();
    $importer->import($configuration, $config_item_set, $ctx);
  }

  /**
   * @param $group
   * @param $data
   *
   * @return string|void
   */
  protected function getApiAction($group, $data) {
    if ($group == 'include' && isset($data[$this->entityDefinition->getIdAttribute()])) {
      return 'update';
    } elseif ($group == 'include' && !isset($data[$this->entityDefinition->getIdAttribute()])) {
      return 'create';
    } elseif ($group == 'remove' && isset($data[$this->entityDefinition->getIdAttribute()])) {
      return 'delete';
    }
  }

  /**
   * Return the api parameters for import.
   * @param $group
   * @param $data
   * @param $configurationValue
   *
   * @return array
   */
  protected function getApiParams($group, $data, $configurationValue) {
    $ignoredAttributes = $this->getIgnoredAttributes();
    $idAttribute = $this->entityDefinition->getIdAttribute();
    $apiAction = $this->getApiAction($group, $data);
    $params = [];
    switch($apiAction) {
      case 'update':
        if (isset($data[$idAttribute]) && $data[$idAttribute]) {
          $id = $data[$idAttribute];
          $params['where'][] = [$idAttribute, '=', $id];
        }
        foreach ($ignoredAttributes[$group][$configurationValue] as $ignoredAttribute) {
          unset($data[$ignoredAttribute]);
        }
        foreach($data as $key => $val) {
          if ($val === null) {
            $data[$key] = '';
          }
        }
        $params['values'] = $data;
        break;
      case 'create':
        foreach ($ignoredAttributes[$group][$configurationValue] as $ignoredAttribute) {
          unset($data[$ignoredAttribute]);
        }
        foreach($data as $key => $val) {
          if ($val === null) {
            $data[$key] = '';
          }
        }
        $params['values'] = $data;
        break;
      case 'delete':
        if (isset($data[$idAttribute]) && $data[$idAttribute]) {
          $id = $data[$idAttribute];
          $params['where'][] = [$idAttribute, '=', $id];
        }
        break;
    }
    return $params;
  }

  /**
   * Returns attributes which should not be exported.
   *
   * Contains the ID attribute by default.
   *
   * @return array
   */
  protected function getIgnoredAttributes() {
    $ignored = [];
    $ignored['include'][1][] = $this->entityDefinition->getIdAttribute();
    $ignored['include'][1][] = 'domain_id';
    return $ignored;
  }

}
