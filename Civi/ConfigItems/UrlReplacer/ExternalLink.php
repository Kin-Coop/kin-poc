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

use Civi\ConfigItems\ConfigurationForm;

use CRM_Civiconfig_ExtensionUtil as E;

class ExternalLink implements Url, ConfigurableUrl {

  /**
   * @var \Civi\ConfigItems\UrlReplacer\ExternalLink\ImportConfigurationForm
   */
  protected $importForm;

  /**
   * @var \Civi\ConfigItems\UrlReplacer\ExternalLink\ExportConfigurationForm
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
  protected $label;

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
   * @param string $urlInSource
   * @param string $label
   * @param string $key
   */
  public function __construct($entityType, $entityName, $fieldName, $urlInSource, $label, $key=null) {
    $this->entityType = $entityType;
    $this->entityName = $entityName;
    $this->fieldName = $fieldName;
    $this->urlInSource = $urlInSource;
    $this->label = $label;
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
    return E::ts('Link');
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
   * Returns a label for the URL.
   *
   * @return string
   */
  public function getLabel() {
    return $this->label .' (' . $this->urlInSource . ')';
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
   * Import the url
   *
   * This function could be used to store the image and return the public url to the image.
   *
   * @param $import_configuration
   * @param $configuration
   * @param $config_item_set
   */
  public function prepareForImport($import_configuration, $configuration, $config_item_set) {
    if ($configuration['method'] == 'ask_on_import') {
      $this->newUrl = $import_configuration['replace_url'];
    } elseif ($configuration['method'] == 'replace_with') {
      $this->newUrl = $configuration['replace_url'];
    }
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
    return $configuration;
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return ConfigurationForm
   */
  public function getImportConfigurationForm() {
    if (!$this->importForm) {
      $this->importForm = new \Civi\ConfigItems\UrlReplacer\ExternalLink\ImportConfigurationForm($this);
    }
    return $this->importForm;
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return ConfigurationForm
   */
  public function getExportConfigurationForm() {
    if (!$this->exportForm) {
      $this->exportForm = new \Civi\ConfigItems\UrlReplacer\ExternalLink\ExportConfigurationForm($this);
    }
    return $this->exportForm;
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

}
