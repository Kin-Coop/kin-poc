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

namespace Civi\ConfigItems\FileFormat;

use Civi\ConfigItems\Entity\EntityExporter;
use CRM_Civiconfig_ExtensionUtil as E;

abstract class AbstractFileFormat {

  /**
   * Creates an export file and returns the file name.
   *
   * @param $config_item_set
   * @return string
   */
  abstract public function export($config_item_set);

  /**
   * Redirects the output as a download.
   * @param $config_item_set
   */
  abstract public function download($config_item_set);

  /**
   * Uploads and extract the file.
   * Returns the configuration in the imported file.
   *
   * @param string $file
   *   The path to the uploaded file
   * @param array $config_item_set
   * @return array
   */
  abstract public function upload($file, $config_item_set);

  /**
   * Validates the uploaded file.
   *
   * @param string $file
   *   The path to the uploaded file
   * @return false|array
   *  Returns FALSE when validation failed. Returns an array containing the config
   *  item set.
   */
  abstract public function validate($file);

  /**
   * @param $config_item_set
   *
   * @return string
   */
  protected function getFilenameWithoutExtension($config_item_set) {
    return $config_item_set['name'] . '-v'.$config_item_set['version'];
  }

  /**
   * Creates the export data directory and returns the path to the directory.
   *
   * @param $config_item_set
   * @return string
   */
  protected function generateExportDirectory($config_item_set) {
    $factory = civiconfig_get_entity_factory();
    $tempdir = \CRM_Utils_File::tempdir($this->getFilenameWithoutExtension($config_item_set));
    \CRM_Utils_File::createDir($tempdir);
    foreach($factory->getEntityListForConfigItemSet($config_item_set) as $entityName) {
      $entityDefinition = $factory->getEntityDefinition($entityName);
      $entityExporter = $entityDefinition->getExporterClass();
      if ($entityExporter instanceof EntityExporter) {
        $entityConfiguration = [];
        if (isset($config_item_set['configuration']) && isset($config_item_set['configuration'][$entityName])) {
          $entityConfiguration = $config_item_set['configuration'][$entityName];
        }
        $entityData = $entityExporter->export($entityConfiguration, $config_item_set, $tempdir);
        if (!empty($entityData)) {
          file_put_contents($tempdir . DIRECTORY_SEPARATOR . $entityDefinition->getFileName() . ".json", json_encode($entityData, JSON_PRETTY_PRINT));
        } else {
          unset($config_item_set['entities'][$entityName]);
          unset($config_item_set['configuration'][$entityName]);
        }
      }
    }
    foreach($factory->getDecorators() as $decorator) {
      $decoratorName = $decorator->getName();
      $decoratorConfiguration = [];
      if (isset($config_item_set['configuration']) && isset($config_item_set['configuration'][$decoratorName])) {
        $decoratorConfiguration = $config_item_set['configuration'][$decoratorName];
      }
      $config_item_set['configuration'][$decoratorName] = $decorator->export($decoratorConfiguration, $config_item_set, $tempdir);
    }

    $infoData['name'] = $config_item_set['name'];
    $infoData['title'] = $config_item_set['title'];
    $infoData['version'] = $config_item_set['version'];
    $infoData['entities'] = $config_item_set['entities'];
    $infoData['description'] = $config_item_set['description'];
    $infoData['configuration'] = $config_item_set['configuration'];
    file_put_contents($tempdir . DIRECTORY_SEPARATOR . "info.json", json_encode($infoData, JSON_PRETTY_PRINT));

    return $tempdir;
  }

  /**
   * @param $config_item_set
   * @param $entityName
   * @param $entityFileName
   * @return array
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public function loadEntityImportData($config_item_set, $entityName, $entityFileName) {
    $importDir = civiconfig_get_import_directory();
    $entityDataFileName = $importDir . $config_item_set['import_sub_directory'] . DIRECTORY_SEPARATOR . $entityFileName . ".json";
    if (!file_exists($entityDataFileName)) {
      throw new EntityImportDataException(E::ts('Could not read entity data file for entity %1 in config item set %2', [1=>$entityName, 2=>$config_item_set['title']]));
    }
    $entityData = json_decode(file_get_contents($entityDataFileName), true);
    if ($entityData === null) {
      throw new EntityImportDataException(E::ts('Could not decode json data for entity %1 in config item set %2', [1=>$entityName, 2=>$config_item_set['title']]));
    }
    return $entityData;
  }

  /**
   * Validates the array with the data of the config item set.
   * This validation takes place prior to import.
   *
   * @param array $config_item_set
   * @return FALSE|array
   */
  protected function validateConfigItemSetForImport($config_item_set) {
    if (!isset($config_item_set['name']) || !isset($config_item_set['version']) || !isset($config_item_set['title'])) {
      return FALSE;
    }
    $config_item_set['import_sub_directory'] = $this->createImportDirectory($config_item_set);
    $config_item_set['id'] = null;
    $existingConfigItemSets = \Civi\Api4\ConfigItemSet::get()
      ->addWhere('name', '=', $config_item_set['name'])
      ->addOrderBy('id')
      ->setLimit(1)
      ->execute();
    if ($existingConfigItemSets->count()) {
      $config_item_set['id'] = $existingConfigItemSets->first()['id'];
    }
    return $config_item_set;
  }

  /**
   * Creates a directory to be used during import.
   *
   * @return string
   */
  protected function createImportDirectory($config_item_set) {
    $directoryName = $this->getImportDirectory($config_item_set);
    if (file_exists($directoryName)) {
      \CRM_Utils_File::cleanDir($directoryName, TRUE, FALSE);
    }
    \CRM_Utils_File::createDir($directoryName);
    return basename($directoryName);
  }

  /**
   * Returns the directory name of this import.
   *
   * @param $config_item_set
   * @return string
   */
  public function getImportDirectory($config_item_set) {
    $directoryBaseName = 'import-' . $this->getFilenameWithoutExtension($config_item_set);
    $importDirectory = civiconfig_get_import_directory();
    return $importDirectory . $directoryBaseName;
  }

  /**
   * Delete the import directory.
   *
   * @param $config_item_set
   * @throws \Exception
   */
  public function delete($config_item_set) {
    $directory = $this->getImportDirectory($config_item_set);
    if (file_exists($directory)) {
      \CRM_Utils_File::cleanDir($directory, TRUE, FALSE);
    }
  }

}
