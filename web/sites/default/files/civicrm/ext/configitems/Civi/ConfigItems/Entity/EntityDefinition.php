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

namespace Civi\ConfigItems\Entity;

abstract class EntityDefinition {

  /**
   * @var String
   */
  protected $name;

  /**
   * @var String
   */
  protected $title_single;

  /**
   * @var String
   */
  protected $title_plural;

  /**
   * @var String[]
   */
  protected $afterEntities = [];

  /**
   * @var String[]
   */
  protected $beforeEntities = [];

  /**
   * @return \Civi\ConfigItems\Entity\EntityImporter
   */
  abstract public function getImporterClass();

  /**
   * @return \Civi\ConfigItems\Entity\EntityExporter
   */
  abstract public function getExporterClass();

  public function __construct($name, $afterEntities=[], $beforeEntities=[]) {
    $this->name = $name;
    $this->afterEntities = $afterEntities;
    $this->beforeEntities = $beforeEntities;
  }


  /**
   * @return String
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @return String
   */
  public function getFileName() {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getTitlePlural() {
    if (empty($this->title_plural) && !empty($this->title_single)) {
      return $this->title_single;
    } elseif (empty($this->title_plural)) {
      return $this->name;
    }
    return $this->title_plural;
  }

  /**
   * @return string
   */
  public function getTitleSingle() {
    if (empty($this->title_single) && !empty($this->title_plural)) {
      return $this->title_plural;
    } elseif (empty($this->title_single)) {
      return $this->name;
    }
    return $this->title_single;
  }

  /**
   * @return array|mixed|String[]
   */
  public function getAfterEntities() {
    return $this->afterEntities;
  }

  /**
   * @return array|mixed|String[]
   */
  public function getBeforeEntities() {
    return $this->beforeEntities;
  }

  /**
   * Clear the before entities list.
   */
  public function clearBeforeEntities() {
    $this->beforeEntities = [];
  }

  /**
   * Clear the after entities list.
   */
  public function clearAfterEntities() {
    $this->afterEntities = [];
  }

  /**
   * Add an entity to the after entities list.
   *
   * @param $entityName
   */
  public function addAfterEntity($entityName) {
    $this->afterEntities[] = $entityName;
  }

  /**
   * Add an entity to the before entities list.
   *
   * @param $entityName
   */
  public function addBeforeEntity($entityName) {
    $this->beforeEntities[] = $entityName;
  }

  /**
   * @return bool
   */
  static function isAvailable() {
    return TRUE;
  }

  /**
   * Alter the entity data just before import.
   *
   * @param $entityData
   * @param $configuration
   * @param $config_item_set
   *
   * @return array|mixed
   */
  public function alterEntityDataForImport($entityData, $configuration, $config_item_set) {
    $factory = civiconfig_get_entity_factory();
    foreach($factory->getDecorators() as $decorator) {
      $entityData = $decorator->alterImportData($entityData, $this, $configuration, $config_item_set);
    }
    return $entityData;
  }

  /**
   * Alter the entity data just before import.
   *
   * @param $entityData
   * @param $configuration
   * @param $config_item_set
   *
   * @return array|mixed
   */
  public function alterEntityDataForExport($entityData, $configuration, $config_item_set) {
    $factory = civiconfig_get_entity_factory();
    foreach($factory->getDecorators() as $decorator) {
      $entityData = $decorator->alterExportData($entityData, $this, $configuration, $config_item_set);
    }
    return $entityData;
  }

}
