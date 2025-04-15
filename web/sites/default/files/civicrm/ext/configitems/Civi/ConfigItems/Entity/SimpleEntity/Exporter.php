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

use Civi\ConfigItems\Entity\EntityExporter;
use Civi\ConfigItems\ConfigurationForm;
use Civi\ConfigItems\FileFormat\EntityImportDataException;
use CRM_Civiconfig_ExtensionUtil as E;

class Exporter implements EntityExporter {

  /**
   * @var \Civi\ConfigItems\Entity\SimpleEntity\Definition;
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
    return $this->entityDefinition->getExportHelpText();
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
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return false|ConfigurationForm
   */
  public function getExportConfigurationForm() {
    return new ExportForm($this);
  }

  /**
   * @return array
   */
  public function getGroups() {
    return [
      'include' => E::ts('Include'),
      'remove' => E::ts('Mark as removed'),
    ];
  }

  /**
   * Returns attributes which should not be exported.
   *
   * Contains the ID attribute by default.
   *
   * @return array
   */
  public function getIgnoredAttributes() {
    $ignored = [];
    foreach($this->getGroups() as $group => $groupTitle) {
      $ignored[$group][] = $this->getEntityDefinition()->getIdAttribute();
      $ignored[$group][] = 'domain_id';
    }
    return $ignored;
  }

  /**
   * Exports the entity
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   * @return array
   */
  public function export($configuration, $config_item_set, $directory='') {
    $entityName = $this->getEntityDefinition()->getApiEntityName();
    $ignoredAttributes = $this->getIgnoredAttributes();
    $nameAttribute = $this->entityDefinition->getNameAttribute();
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

    $whereClauses = [];
    if ($this->entityDefinition->getAdditionalWhereClauses()) {
      $whereClauses = array_merge($whereClauses, $this->entityDefinition->getAdditionalWhereClauses());
    }
    $results = civicrm_api4($entityName, 'get', [
      'where' => $whereClauses,
    ]);
    foreach ($results as $result) {
      $unmungedName = $result[$nameAttribute];
      $name = \CRM_Utils_String::munge($result[$nameAttribute]);
      foreach($this->getGroups() as $group => $groupTitle) {
        if (isset($configuration[$group]) && in_array($name, $configuration[$group])) {
          $data[$group][$unmungedName] = (array) $result;
          foreach ($ignoredAttributes[$group] as $ignoredAttribute) {
            if (isset($data[$group]) && isset($data[$group][$unmungedName]) && isset($data[$group][$unmungedName][$ignoredAttribute])) {
              unset($data[$group][$unmungedName][$ignoredAttribute]);
            }
          }
          unset($importData[$group][$unmungedName]);
        }
      }
    }
    foreach($this->getGroups() as $group => $groupTitle) {
      if (isset($configuration[$group]) && $configuration[$group]) {
        foreach ($configuration[$group] as $name) {
          if (isset($importData[$group][$name])) {
            $data[$group][$name] = $importData[$group][$name];
          }
        }
      }
    }
    $data = $this->entityDefinition->alterEntityDataForExport($data, $configuration, $config_item_set);
    return $data;
  }


}
