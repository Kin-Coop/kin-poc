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

use Civi\ConfigItems\UrlReplacer\ReplaceableUrls;
use Civi\ConfigItems\UrlReplacer\Utils\FindUrlsInHTML;
use CRM_Civiconfig_ExtensionUtil as E;

class MessageTemplate extends Definition implements ReplaceableUrls {

  /**
   * @param array $afterEntities
   * @param array $beforeEntities
   */
  public function __construct($afterEntities=[], $beforeEntities=[]) {
    parent::__construct('MessageTemplate', 'MessageTemplate', $afterEntities, $beforeEntities);
    $this->titleAttribute = 'msg_title';
    $this->nameAttribute = 'msg_title';
  }

  /**
   * Returns additional where clauses for api4.
   *
   * @return array
   */
  public function getAdditionalWhereClauses() {
    return [
      ['workflow_id', 'IS NULL']
    ];
  }

  /**
   * Returns the urls which can be replaced
   *
   * @param $configuration
   * @param $config_item_set
   *
   * @return \Civi\ConfigItems\UrlReplacer\Url[]
   */
  public function getUrlsForExport($configuration, $config_item_set) {
    $urls = [];
    $data = $this->getExporterClass()->export($configuration, $config_item_set);
    if (isset($data['include'])) {
      foreach($data['include'] as $entityName => $entity) {
        $entityTitle = $entity[$this->getTitleAttribute()];
        $msgTextLabel = E::ts('%1: %2 - Plain-Text Format', [1=>$this->getTitleSingle(), $entityTitle]);
        $msgHTMLLabel = E::ts('%1: %2 - HTML Format', [1=>$this->getTitleSingle(), $entityTitle]);
        $urls = array_merge($urls, FindUrlsInHTML::getUrls($entity['msg_html'], 'MessageTemplate', $entityName, 'msg_html', $msgHTMLLabel));
        $urls = array_merge($urls, FindUrlsInHTML::getUrls($entity['msg_text'], 'MessageTemplate', $entityName, 'msg_text', $msgTextLabel));
      }
    }
    return $urls;
  }

  /**
   * Returns the urls which can be replaced
   *
   * @param $configuration
   * @param $config_item_set
   * @param bool $loadAll
   *
   * @return \Civi\ConfigItems\UrlReplacer\Url[]
   */
  public function getUrlsForImport($configuration, $config_item_set, $loadAll=FALSE) {
    $urls = [];
    $data = $this->getImporterClass()->loadEntityImportData($config_item_set);
    if (isset($data['include'])) {
      foreach($data['include'] as $entityName => $entity) {
        if ($loadAll || (isset($configuration['include']) && isset($configuration['include'][$entityName]) && $configuration['include'][$entityName])) {
          $entityTitle = $entity[$this->getTitleAttribute()];
          $msgTextLabel = E::ts('%1: %2 - Plain-Text Format', [
            1 => $this->getTitleSingle(),
            $entityTitle
          ]);
          $msgHTMLLabel = E::ts('%1: %2 - HTML Format', [
            1 => $this->getTitleSingle(),
            $entityTitle
          ]);
          $urls = array_merge($urls, FindUrlsInHTML::getUrls($entity['msg_html'], 'MessageTemplate', $entityName, 'msg_html', $msgHTMLLabel));
          $urls = array_merge($urls, FindUrlsInHTML::getUrls($entity['msg_text'], 'MessageTemplate', $entityName, 'msg_text', $msgTextLabel));
        }
      }
    }
    return $urls;
  }

  /**
   * Returns the $entityData in which the urls are replaced.
   *
   * @param array $entityData
   * @param \Civi\ConfigItems\UrlReplacer\Url[][] $urls
   * @param $configuration
   * @param $config_item_set
   * @return array
   */
  public function replaceUrls($entityData, $urls, $configuration, $config_item_set) {
    if (isset($entityData['include'])) {
      foreach ($entityData['include'] as $entityName => $entity) {
        foreach($urls as $url) {
          $entityData['include'][$entityName]['msg_html'] = $url->replace('MessageTemplate', $entityName, 'msg_html', $entityData['include'][$entityName]['msg_html']);
          $entityData['include'][$entityName]['msg_text'] = $url->replace('MessageTemplate', $entityName, 'msg_text', $entityData['include'][$entityName]['msg_text']);
        }
      }
    }
    return $entityData;
  }


}
