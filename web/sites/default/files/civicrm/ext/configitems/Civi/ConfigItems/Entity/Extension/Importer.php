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

use Civi\ConfigItems\Entity\EntityImporter;
use Civi\ConfigItems\Entity\ImportDirectly;
use Civi\ConfigItems\FileFormat\EntityImportDataException;
use CRM_Civiconfig_ExtensionUtil as E;

class Importer implements EntityImporter, ImportDirectly {

  /**
   * @var \Civi\ConfigItems\Entity\Extension\Definition;
   */
  protected $entityDefinition;

  /**
   * @var \Civi\ConfigItems\Entity\Extension\ExportForm
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
    return '';
  }

  /**
   * Returns the import configuration form.
   * Returns false if this entity does not have a configuration for import.
   *
   * @return false|\Civi\ConfigItems\Entity\Extension\ImportForm
   */
  public function getImportConfigurationForm() {
    if (!$this->form) {
      $this->form = new ImportForm($this);
    }
    return $this->form;
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
      $runUpgrades = false;
      $runFinishTask = false;
      foreach($configuration as $key => $action) {
        if (!$action) {
          continue;
        }
        $callback = [static::class, 'runImportExtensionTask'];
        $params = [
          $key,
          $action,
          $config_item_set,
          $this->entityDefinition->getName()
        ];
        if ($action == 'replace') {
          $runUpgrades = true;
          $title = E::ts('Replace extension %1', [1 => $key]);
        } elseif ($action == 'upgrade') {
          $runUpgrades = true;
          $title = E::ts('Upgrade extension %1', [1 => $key]);
        } elseif ($action == 'downgrade') {
          $title = E::ts('Downgrade extension %1', [1=>$key]);
        } elseif ($action == 'Install') {
          $title = E::ts('Install extension %1', [1=>$key]);
        } if ($action == 'uninstall') {
          $title = E::ts('Uninstall extension %1', [1=>$key]);
        }
        $queue->addNewTask($title, $callback, $params);
        $runFinishTask = true;
      }
      if ($runUpgrades) {
        $callback = [static::class, 'prepareExtensionUpgrades'];
        $params = [
          $configuration,
          $config_item_set,
          $this->entityDefinition->getName()
        ];
        $title = E::ts('Prepare extension upgrades');
        $queue->addNewTask($title, $callback, $params);
      }
      if ($runFinishTask) {
        $callback = [static::class, 'finishExtensionImport'];
        $params = [
          $configuration,
          $config_item_set,
          $this->entityDefinition->getName()
        ];
        $title = E::ts('Finish extension import');
        $queue->addNewTask($title, $callback, $params);
      }
    }
  }

  /**
   * Returns a redirect url
   *
   * @param $configuration
   * @param $config_item_set
   *
   * @return string
   */
  public function getRedirectUrl($configuration, $config_item_set) {
    $entityName = $this->entityDefinition->getName();
    return \CRM_Utils_System::url('civicrm/admin/civiconfig/import/entity', ['reset' => 1, 'id' => $config_item_set['id'], 'entity' => $entityName, 'action' => 'update']);
  }

  /**
   * @param $configuration
   * @param $config_item_set
   *
   * @return bool
   */
  public function importDirectly($configuration, $config_item_set) {
    if ($this->entityImportDataExists($config_item_set)) {
      return TRUE;
    }
    return FALSE;
  }


  /**
   * Run the task to import an extension.
   *
   * @param $key
   * @param $action
   * @param $config_item_set
   * @param $entityName
   * @param \CRM_Queue_TaskContext $ctx
   *
   * @throws \CRM_Civiconfig_EntityException
   */
  public static function runImportExtensionTask($key, $action, $config_item_set, $entityName, \CRM_Queue_TaskContext $ctx) {
    $factory = civiconfig_get_entity_factory();
    /**
     * @var \Civi\ConfigItems\Entity\SimpleEntity\Importer
     */
    $importer = $factory->getEntityDefinition($entityName)->getImporterClass();
    $importer->importExtension($key, $action, $config_item_set, $ctx);
  }

  /**
   * Install/Upgrade or Uninstall extensions.
   *
   * @param $key
   * @param $action
   * @param $config_item_set
   * @param \CRM_Queue_TaskContext $ctx
   *
   * @throws \CRM_Extension_Exception
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public function importExtension($key, $action, $config_item_set, \CRM_Queue_TaskContext $ctx) {
    $fileFactory = civiconfig_get_fileformat_factory();
    $fileFormat = $fileFactory->getFileFormatClass($config_item_set['import_file_format']);
    $extensionSystem = \CRM_Extension_System::singleton();
    $entityData = $this->loadEntityImportData($config_item_set);
    if (!isset($entityData[$key])) {
      return;
    }
    $extension = $entityData[$key];
    if ($action == 'uninstall') {
      $extensionSystem->getManager()->uninstall($key);
      \CRM_Utils_File::cleanDir($extension['path'], TRUE, FALSE);
    } elseif ($action == 'install') {
      switch ($extension['download_source']) {
        case 'git':
          $extensionPath = Installer::downloadFromGit($key, $extension['url'], $extension['branch']);
          break;
        case 'zip':
          $extensionPath = Installer::downloadZip($key, $extension['url']);
          break;
        case 'download':
          $extensionPath = Installer::downloadFromCiviCRM($key);
          break;
      }
      $extensionInfo = \CRM_Extension_Info::loadFromFile($extensionPath . DIRECTORY_SEPARATOR . \CRM_Extension_Info::FILENAME);

      $requiredExtensions = $extensionSystem->getManager()->findInstallRequirements([$key], $extensionInfo);
      foreach ($requiredExtensions as $requiredExtension) {
        if ($extensionSystem->getManager()->getStatus($requiredExtension) !== \CRM_Extension_Manager::STATUS_INSTALLED && $requiredExtension !== $extensionInfo->key) {
          throw new EntityImportDataException("Could not install Extension with name " . $key . ". Error: unmet extension requirements: " . json_encode($requiredExtension));
        }
      }
      $extensionSystem->getManager()->refresh();
      $extensionSystem->getManager()->install($key);
    } elseif ($action == 'replace' || $action == 'downgrade' || $action == 'upgrade') {
      $directory = $fileFormat->getImportDirectory($config_item_set);
      $directory .= DIRECTORY_SEPARATOR . 'extensions';
      if (\CRM_Utils_File::createDir($directory, FALSE) === FALSE) {
        throw new EntityImportDataException("Could not replace Extension with name " . $key . ". Error: could not create directory at: " . $directory);
      }
      switch ($extension['download_source']) {
        case 'git':
          $extensionPath = Installer::downloadFromGit($key, $extension['url'], $extension['branch'], $directory);
          break;
        case 'zip':
          $extensionPath = Installer::downloadZip($key, $extension['url'], $directory);
          break;
        case 'download':
          $extensionPath = Installer::downloadFromCiviCRM($key, $directory);
          break;
      }
      $extensionSystem->getManager()->replace($extensionPath);
    }
  }

  /**
   * Add upgrade tasks of the extension to the key.
   *
   * We want to run those task as soon as possible before continuing the import.
   * To accomplish this we need to do some hacking where we lower the weight of the inserted tasks.
   *
   * @param $configuration
   * @param $config_item_set
   * @param $entityName
   * @param \CRM_Queue_TaskContext $ctx
   */
  public static function prepareExtensionUpgrades($configuration, $config_item_set, $entityName, \CRM_Queue_TaskContext $ctx) {
    $extensionSystem = \CRM_Extension_System::singleton();
    $currentNumberOfItems = $ctx->queue->numberOfItems();
    if ($ctx->queue instanceof \CRM_Queue_Queue_Sql) {
      $lastId = \CRM_Core_DAO::singleValueQuery("SELECT MAX(`id`) FROM `civicrm_queue_item` WHERE `queue_name` = %1", [1=>[$ctx->queue->getName(), 'String']]);
    }

    foreach($configuration as $key => $action) {
      if ($action == 'replace' || $action == 'downgrade' || $action == 'upgrade') {
        $upgrader = $extensionSystem->getMapper()->getUpgrader($key);
        if ($upgrader) {
          $upgrader->notify('upgrade', ['enqueue', $ctx->queue]);
        }
      }
    }
    \CRM_Utils_Hook::upgrade('enqueue', $ctx->queue);

    $itemsAdded = $ctx->queue->numberOfItems() - $currentNumberOfItems;
    if ($ctx->queue instanceof \CRM_Queue_Queue_Sql && $itemsAdded > 0) {
      $minWeight = \CRM_Core_DAO::singleValueQuery("SELECT MIN(`weight`) FROM `civicrm_queue_item` WHERE `queue_name` = %1", [1=>[$ctx->queue->getName(), 'String']]);
      if ($minWeight > 0) {
        $minWeight = 0;
      }
      $sqlParams = [
        1=>[$ctx->queue->getName(), 'String'],
        2=>[$lastId, 'Integer']
      ];
      $dao = \CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_queue_item` WHERE `queue_name` = %1 AND `id` > %2", $sqlParams);
      $itemsToUpDate = $itemsAdded;
      while($dao->fetch()) {
        $newWeight = $minWeight + ($dao->weight - $itemsToUpDate);
        $updateParams = [
          1 => [$newWeight, 'Integer'],
          2 => [$dao->id, 'Integer']
        ];
        \CRM_Core_DAO::executeQuery("UPDATE `civicrm_queue_item` SET `weight` = %1 WHERE `id` =%2", $updateParams);
        $itemsToUpDate --;
      }
    }

    return TRUE;
  }

  /**
   * Add upgrade tasks of the extension to the key.
   *
   * We want to run those task as soon as possible before continuing the import.
   * To accomplish this we need to do some hacking where we lower the weight of the inserted tasks.
   *
   * @param $configuration
   * @param $config_item_set
   * @param $entityName
   * @param \CRM_Queue_TaskContext $ctx
   */
  public static function finishExtensionImport($configuration, $config_item_set, $entityName, \CRM_Queue_TaskContext $ctx) {
    foreach($configuration as $key => $action) {
      $configuration[$key] = '0';
    }
    $config_item_set['import_configuration'][$entityName] = $configuration;
    $values['import_configuration'] = $config_item_set['import_configuration'];
    civicrm_api4('ConfigItemSet', 'update', [
      'values' => $values,
      'where' => [['id', '=', $config_item_set['id']]],
    ]);
    return TRUE;
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
    $fileFactory = civiconfig_get_fileformat_factory();
    $fileFormat = $fileFactory->getFileFormatClass($config_item_set['import_file_format']);
    $entityData = $fileFormat->loadEntityImportData($config_item_set, $this->entityDefinition->getName(), $this->getEntityDefinition()->getFileName());
    foreach($entityData as $key => $extension) {
      $entityData[$key] = $this->isExtensionInstalled($extension);
    }
    return $entityData;
  }

  /**
   * Enrich
   * @param $extension
   * @return mixed
   */
  protected function isExtensionInstalled($extension) {
    $key = $extension['key'];
    $extensionSystem = \CRM_Extension_System::singleton();
    $extensionBasePath = $extensionSystem->getDefaultContainer()->getBaseDir();
    $status = $extensionSystem->getManager()->getStatus($key);
    if ($status == \CRM_Extension_Manager::STATUS_INSTALLED || $status == \CRM_Extension_Manager::STATUS_DISABLED) {
      try {
        $info = $extensionSystem->getMapper()->keyToInfo($key, TRUE);
        $info = (array) $info;
        $extension['current_status'] = $status;
        $extension['current_version'] = $info['version'];
        $extension['current_info'] = $info;
        $extension['path'] = $extensionSystem->getFullContainer()->getPath($key);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    return $extension;
  }

  /**
   * @return string
   */
  public function getNextButtonTitle() {
    return E::ts('Import extensions and next');
  }


}
