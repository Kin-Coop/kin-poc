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

class ResourceUrl implements Url {

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

  public function getUrlTypeTitle() {
    return E::ts('Resource URL');
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
   * @return string
   */
  public function getUrlType() {
    return 'resource_url';
  }

  /**
   * @param $import_configuration
   * @param $configuration
   * @param $config_item_set
   */
  public function prepareForImport($import_configuration, $configuration, $config_item_set) {
    $replaceResourceUrl = Decorator::replaceResourceUrlOnImport($config_item_set);
    $askForReplaceResourceUrl = Decorator::askForResourceUrlReplacementOnImport($config_item_set);
    if ($replaceResourceUrl && strpos($configuration['replace_url'], '[cms.root]/')===0) {
      $cmsRootUrl = \Civi::paths()->getUrl('[cms.root]/', 'absolute');
      $this->newUrl  = str_replace('[cms.root]/', $cmsRootUrl, $configuration['replace_url']);
    } elseif ($replaceResourceUrl && strpos($configuration['replace_url'], '[civicrm.files]/')===0) {
      $civicrmFilesUrl = \Civi::paths()->getUrl('[civicrm.files]/', 'absolute');
      $this->newUrl  = str_replace('[civicrm.files]/', $civicrmFilesUrl, $configuration['replace_url']);
    } elseif ($askForReplaceResourceUrl && strpos($configuration['replace_url'], '[cms.root]/')===0) {
      $this->newUrl  = str_replace('[cms.root]/', $config_item_set['import_configuration']['UrlReplaceer']['resource_url_replacement_cms_root_url'], $configuration['replace_url']);
    } elseif ($askForReplaceResourceUrl && strpos($configuration['replace_url'], '[civicrm.files]/')===0) {
      $this->newUrl  = str_replace('[civicrm.files]/', $config_item_set['import_configuration']['UrlReplaceer']['resource_url_replacement_civicrm_files_url'], $configuration['replace_url']);
    }
  }

  /**
   * Exports the URL (possible to store images/attachments in the export file).
   * Returns $configuration
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   *
   * @return array
   */
  public function export($configuration, $config_item_set, $directory = '') {
    $cmsRootUrl = \Civi::paths()->getUrl('[cms.root]/', 'absolute');
    $civicrmFilesUrl = \Civi::paths()->getUrl('[civicrm.files]/', 'absolute');
    if (strpos($this->urlInSource, $cmsRootUrl)===0) {
      $configuration['replace_url'] = str_replace($cmsRootUrl, '[cms.root]/', $this->urlInSource);
    } elseif (strpos($this->urlInSource, $civicrmFilesUrl)===0) {
      $configuration['replace_url'] = str_replace($civicrmFilesUrl, '[civicrm.files]/', $this->urlInSource);
    }

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


}
