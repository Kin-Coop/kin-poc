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

namespace Civi\ConfigItems\UrlReplacer;

use CRM_Civiconfig_ExtensionUtil as E;

class Image implements Url, ConfigurableImage {

  /**
   * @var \Civi\ConfigItems\UrlReplacer\Image\ImportConfigurationForm
   */
  protected $importForm;

  /**
   * @var \Civi\ConfigItems\UrlReplacer\Image\ExportConfigurationForm
   */
  protected $exportForm;

  /**
   * @var string
   */
  protected $entityType;

  /**
   * @var string
   */
  protected $entityName;

  /**
   * @var string
   */
  protected $fieldName;

  /**
   * @var string
   */
  protected $urlInSource;

  /**
   * @var string
   */
  protected $key;

  /**
   * @var string
   */
  protected $newUrl;

  /**
   * @param string $entityType
   * @param string$entityName
   * @param string $fieldName
   * @param string $label
   * @param string $urlInSource
   * @param string $key
   */
  public function __construct($entityType, $entityName, $fieldName, $urlInSource, $key=null) {
    $this->entityType = $entityType;
    $this->entityName = $entityName;
    $this->fieldName = $fieldName;
    $this->urlInSource = $urlInSource;
    if ($key) {
      $this->key = $key;
    } else {
      $this->key = hash('sha256',$this->entityType.':'.$this->entityName.':'.$this->fieldName.':'.$this->urlInSource);
    }
    if (empty($this->key)) {
      // Although MD5 is broken. It does not matter for us
      // all we want is a short key of this unique link.
      // That it could be decrypted is not a problem as the link is no secret.
      $this->key = md5($this->entityType.':'.$this->entityName.':'.$this->fieldName.':'.$this->urlInSource);
    }
  }

  /**
   * @return string
   */
  public function getUrlTypeTitle() {
    return E::ts('Image');
  }

  /**
   * @return string
   */
  public function getUrlType() {
    return 'url';
  }

  /**
   * Get the unique key for this link.
   *
   * @return string
   */
  public function getUniqueKey() {
    return $this->key;
  }

  /**
   * @param $import_configuration
   * @param $configuration
   * @param $config_item_set
   */
  public function prepareForImport($import_configuration, $configuration, $config_item_set) {
    if (empty($configuration)) {
      return;
    }

    if ($configuration['method'] == 'ask_on_import') {
      $sourceFile = $import_configuration['replace_url'];
    } else {
      $factory = civiconfig_get_fileformat_factory();
      $fileFormat = $factory->getFileFormatClass($config_item_set['import_file_format']);
      $importDirectory = $fileFormat->getImportDirectory($config_item_set);
      $sourceFile = $importDirectory . DIRECTORY_SEPARATOR . $configuration['subdir'] . DIRECTORY_SEPARATOR . $configuration['file'];
    }

    if (empty($sourceFile)) {
      return;
    }

    $config = \CRM_Core_Config::singleton();
    $imageUploadDir = rtrim($config->imageUploadDir, DIRECTORY_SEPARATOR);
    $targetDirectory = $imageUploadDir . DIRECTORY_SEPARATOR . $this->getUniqueKey();
    $targetFile = $targetDirectory . DIRECTORY_SEPARATOR . $configuration['basename'];
    \CRM_Utils_File::cleanDir($targetDirectory, TRUE, FALSE);
    \CRM_Utils_File::createDir($targetDirectory, TRUE, FALSE);
    copy($sourceFile, $targetFile);
    $this->newUrl = $config->imageUploadURL . $this->getUniqueKey() . "/" . $configuration['basename'];
  }

  /**
   * Exports the URL (possible to store images/attachments in the export file).
   * Returns $configuration
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   * @return array
   */
  public function export($configuration, $config_item_set, $directory='') {
    $sourceFilename = $this->urlInSource;
    if ($configuration['method'] == 'replace_with') {
      $sourceFilename = $configuration['replace_url'];
    }
    $config = \CRM_Core_Config::singleton();
    if (strpos($sourceFilename, $config->imageUploadURL) === 0) {
      $sourceFilename = $config->imageUploadDir . substr($sourceFilename, strlen($config->imageUploadURL));
    }
    $imageDir = $directory . DIRECTORY_SEPARATOR . $this->entityType . DIRECTORY_SEPARATOR;
    $basename = basename($sourceFilename);
    $ext = \CRM_Utils_File::getExtensionFromPath($sourceFilename);
    $targetFileName = $this->getUniqueKey() . '.' . $ext;
    \CRM_Utils_File::cleanDir($imageDir);
    \CRM_Utils_File::createDir($imageDir);
    file_put_contents($imageDir . DIRECTORY_SEPARATOR . $targetFileName, file_get_contents($sourceFilename));
    $configuration['subdir'] = $this->entityType;
    $configuration['file'] = $targetFileName;
    $configuration['basename'] = $basename;
    return $configuration;
  }

  /**
   * Replace the URL
   *
   * @param $entityType
   * @param $entityName
   * @param $fieldName
   * @param $content
   *
   * @return string
   */
  public function replace($entityType, $entityName, $fieldName, $content) {
    if ($this->entityType != $entityType) {
      return $content;
    }
    if ($this->entityName != $entityName) {
      return $content;
    }
    if ($this->fieldName != $fieldName) {
      return $content;
    }
    if (empty($this->newUrl)) {
      return $content;
    }
    return str_replace($this->urlInSource, $this->newUrl, $content);
  }

  /**
   * @param $configuration
   * @param $config_item_set
   *
   * @return bool
   */
  public function hasAdditionalConfigurationForImport($configuration, $config_item_set) {
    if ($configuration['method'] == 'ask_on_import') {
      return TRUE;
    }
    return false;
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return \Civi\ConfigItems\ConfigurationForm
   */
  public function getImportConfigurationForm() {
    if (!$this->importForm) {
      $this->importForm = new \Civi\ConfigItems\UrlReplacer\Image\ImportConfigurationForm($this);
    }
    return $this->importForm;
  }

  /**
   * Return a label describing the configuration.
   *
   * @param $configuration
   * @param $config_item_set
   *
   * @return string
   */
  public function getExportConfigurationLabel($configuration, $config_item_set) {
    if ($configuration['method'] == 'replace_with') {
      return E::ts('Replace with %1', [1=>$configuration['replace_url']]);
    } elseif ($configuration['method'] == 'ask_on_import') {
      return E::ts('Ask for replacement on import');
    }
    return '';
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return \Civi\ConfigItems\ConfigurationForm
   */
  public function getExportConfigurationForm() {
    if (!$this->exportForm) {
      $this->exportForm = new \Civi\ConfigItems\UrlReplacer\Image\ExportConfigurationForm($this);
    }
    return $this->exportForm;
  }

  /**
   * Returns a label for the URL.
   *
   * @return string
   */
  public function getLabel() {
    return $this->urlInSource;
  }

  /**
   * @return String
   */
  public function getImageUrl() {
    return $this->urlInSource;
  }

  /**
   * Returns an array with the link options.
   *
   * @return array
   */
  public function getReplacementOptions() {
    return [
      'ask_on_import' => E::ts('Ask for replacement on import'),
      'replace_with' => E::ts('Replace with'),
    ];
  }

}
