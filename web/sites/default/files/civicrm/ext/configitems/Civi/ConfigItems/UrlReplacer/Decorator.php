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
use Civi\ConfigItems\Entity\Decorator as BaseDecorator;

class Decorator implements BaseDecorator {

  /**
   * @var \Civi\ConfigItems\UrlReplacer\ImportConfigurationForm
   */
  protected $importForm;

  /**
   * @var \Civi\ConfigItems\UrlReplacer\ExportConfigurationForm
   */
  protected $exportForm;

  /**
   * @var \Civi\ConfigItems\UrlReplacer\Url[][]
   */
  protected $importUrls;

  /**
   * @var Civi\ConfigItems\UrlReplacer\Url[][]
   */
  protected $urls;

  /**
   * Returns the name of this decorator.
   *
   * @return string
   */
  public function getName() {
    return 'UrlReplaceer';
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return false|ConfigurationForm
   */
  public function getImportConfigurationForm() {
    if (!$this->importForm) {
      $this->importForm = new ImportConfigurationForm($this);
    }
    return $this->importForm;
  }

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return false|ConfigurationForm
   */
  public function getExportConfigurationForm() {
    if (!$this->exportForm) {
      $this->exportForm = new ExportConfigurationForm($this);
    }
    return $this->exportForm;
  }

  /**
   * @param array $entityData
   * @param \Civi\ConfigItems\Entity\EntityDefinition $entityDefinition
   * @param array $configuration
   * @paran array $config_item_set
   * @return array
   */
  public function alterImportData($entityData, $entityDefinition, $configuration, $config_item_set) {
    if ($entityDefinition instanceof ReplaceableUrls) {
      $entityData = $entityDefinition->replaceUrls($entityData, $this->getUrlsForImport($config_item_set), $configuration, $config_item_set);
    }
    return $entityData;
  }

  /**
   * @param array $entityData
   * @param \Civi\ConfigItems\Entity\EntityDefinition $entityDefinition
   * @param array $configuration
   * @param array $config_item_set
   *
   * @return array
   */
  public function alterExportData($entityData, $entityDefinition, $configuration, $config_item_set) {
    return $entityData;
  }

  /**
   * Exports the decorator. Returns the $configuration
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   * @return array
   */
  public function export($configuration, $config_item_set, $directory='') {
    $urls = $this->getUrlsForExport($config_item_set);
    $count = count($urls);
    if ($count) {
      foreach ($urls as $urlKey => $url) {
        $urlConfiguration = [];
        if (isset($configuration['urls'][$urlKey])) {
          $urlConfiguration = $configuration['urls'][$urlKey];
        }
        $urlConfiguration = $url->export($urlConfiguration, $config_item_set, $directory);
        if (!empty($urlConfiguration)) {
          $configuration['urls'][$urlKey] = $urlConfiguration;
        } else {
          unset($configuration['urls'][$urlKey]);
        }
      }
    }
    return $configuration;
  }

  /**
   * Returns the URLs which can be replaced during import.
   *
   * @param $config_item_set
   * @param bool $reset
   * @param bool $loadAll
   * @return \Civi\ConfigItems\UrlReplacer\Url[]
   */
  public function getUrlsForImport($config_item_set, $reset=FALSE, $loadAll=FALSE) {
    if(empty($config_item_set)){
      return [];
    }
    $id = $config_item_set['id'];
    if (!isset($this->importUrls[$id]) || $reset) {
      $this->importUrls[$id] = [];
      $factory = civiconfig_get_entity_factory();
      $exportConfiguration = [];
      if (isset($config_item_set['configuration']) && isset($config_item_set['configuration'][$this->getName()])) {
        $exportConfiguration = $config_item_set['configuration'][$this->getName()];
      }
      if (!isset($exportConfiguration['urls'])) {
        $exportConfiguration['urls'] = [];
      }
      $importConfiguration = [];
      if (isset($config_item_set['import_configuration']) && isset($config_item_set['import_configuration'][$this->getName()])) {
        $importConfiguration = $config_item_set['import_configuration'][$this->getName()];
      }
      if (!isset($importConfiguration['urls'])) {
        $importConfiguration['urls'] = [];
      }

      foreach ($factory->getEntityListForConfigItemSet($config_item_set) as $entityName) {
        $entity = $factory->getEntityDefinition($entityName);
        if ($entity instanceof ReplaceableUrls) {
          $entityImportConfiguration = [];
          if (isset($config_item_set['import_configuration']) && isset($config_item_set['import_configuration'][$entityName])) {
            $entityImportConfiguration = $config_item_set['import_configuration'][$entityName];
          }
          foreach ($entity->getUrlsForImport($entityImportConfiguration, $config_item_set, $loadAll) as $url) {
            $urlKey = $url->getUniqueKey();
            if (!isset($importConfiguration['urls'][$urlKey])) {
              $importConfiguration['urls'][$urlKey] = [];
            }
            if (!isset($exportConfiguration['urls'][$urlKey])) {
              $exportConfiguration['urls'][$urlKey] = [];
            }

            $url->prepareForImport($importConfiguration['urls'][$urlKey], $exportConfiguration['urls'][$urlKey], $config_item_set);
            $this->importUrls[$id][$urlKey] = $url;
          }
        }
      }
    }
    return $this->importUrls[$id];
  }

  /**
   * Returns the URLs
   *
   * If $sort is set to Decorator::SORT_PER_LABEL then the first key in the returned
   * array is the label of the url.
   * If $sort is set to Decorator::SORT_PER_ENTITY then the first key in the returned
   * array is the entity type.
   *
   * @param $config_item_set
   * @return \Civi\ConfigItems\UrlReplacer\Url[]
   */
  public function getUrlsForExport($config_item_set) {
    $factory = civiconfig_get_entity_factory();
    $id = $config_item_set['id'];
    if (!isset($this->urls[$id])) {
      $this->urls[$id] = [];
      foreach ($factory->getEntityListForConfigItemSet($config_item_set) as $entityName) {
        $entity = $factory->getEntityDefinition($entityName);
        if ($entity instanceof ReplaceableUrls) {
          $configuration = [];
          if (isset($config_item_set['configuration']) && isset($config_item_set['configuration'][$entityName])) {
            $configuration = $config_item_set['configuration'][$entityName];
          }
          foreach ($entity->getUrlsForExport($configuration, $config_item_set) as $url) {
            $this->urls[$id][$url->getUniqueKey()] = $url;
          }
        }
      }
    }
    return $this->urls[$id];
  }

  /**
   * Returns whether any entity is available for url replacements
   *
   * @param $config_item_set
   * @return bool
   */
  public function isAvailable($config_item_set) {
    $factory = civiconfig_get_entity_factory();
    foreach($factory->getEntityListForConfigItemSet($config_item_set) as $entityName) {
      $entity = $factory->getEntityDefinition($entityName);
      if ($entity instanceof ReplaceableUrls) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns true when resource url is included
   *
   * @param $config_item_set
   *
   * @return bool
   */
  public function containsResourceURL($config_item_set) {
    $urls = $this->getUrlsForExport($config_item_set);
    foreach($urls as $url) {
      if ($url instanceof ResourceUrl) {
        return TRUE;
      }
    }
    return false;
  }

  /**
   * Returns true when the administrator should give a replacement for the resource url.
   *
   * @param $config_item_set
   * @return bool
   */
  public static function askForResourceUrlReplacementOnImport($config_item_set) {
    if (isset($config_item_set['configuration']['UrlReplaceer']['resource_url_replacement']) ) {
      if ($config_item_set['configuration']['UrlReplaceer']['resource_url_replacement'] == 'ask_on_import') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns true when resource url should be replaced on import.
   *
   * @param $config_item_set
   * @return bool
   */
  public static function replaceResourceUrlOnImport($config_item_set) {
    $replaceResourceUrl = false;
    if (isset($config_item_set['configuration']['UrlReplaceer']['resource_url_replacement']) && $config_item_set['configuration']['UrlReplaceer']['resource_url_replacement'] == 'replace_with_target_resource_url') {
      $replaceResourceUrl = true;
    }
    return $replaceResourceUrl;
  }


}
