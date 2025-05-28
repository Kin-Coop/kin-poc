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

class Factory {

  public const EARLIEST_PRIORITY = -999;

  public const EARLY_PRIORITY = -10;

  public const NORMAL_PRIORITY = 0;

  public const LATE_PRIORITY = 10;

  public const LATEST_PRIORITY = 999;

  /**
   * @var \Civi\ConfigItems\Entity\EntityDefinition[][]
   */
  protected $entities = array();

  /**
   * @var String[]|null
   */
  protected $build = null;

  /**
   * @var \Civi\ConfigItems\Entity\Decorator[]
   */
  protected $decorators = [];

  public function __construct() {

  }

  /**
   * @return String[]
   */
  public function getEntityList() {
    if (!$this->build) {
      // First create a build list.
      // If such a list already exists then we return it.
      $this->build = [];

      /**
       * @var \Civi\ConfigItems\Entity\EntityDefinition[][]
       */
      $entitiesToProcess = $this->entities;
      // Change beforeEntities to afterEntities.
      foreach ($entitiesToProcess as $priority => $entities) {
        foreach ($entities as $entity) {
          if (!empty($entity->getBeforeEntities())) {
            foreach ($entity->getBeforeEntities() as $beforeEntityName) {
              $beforeEntity = $this->findByEntityName($beforeEntityName, $entitiesToProcess);
              if ($beforeEntity) {
                $beforeEntity->addAfterEntity($entity);
              }
            }
            $entity->clearBeforeEntities();
          }
        }
      }

      foreach ($entitiesToProcess as $priority => $entities) {
        foreach ($entities as $entity) {
          if ($entity->isAvailable()) {
            $this->addEntityDefinitionToBuildList($entity, $entitiesToProcess);
          }
        }
      }
    }
    return $this->build;
  }

  /**
   * @param $decorator
   */
  public function addDecorator($decorator) {
    $this->decorators[] = $decorator;
  }

  /**
   * @return \Civi\ConfigItems\Entity\Decorator[]
   */
  public function getDecorators() {
    return $this->decorators;
  }

  /**
   * @param $name
   *
   * @return \Civi\ConfigItems\Entity\Decorator|null
   */
  public function getDecoratorByName($name) {
    foreach($this->decorators as $decorator) {
      if ($decorator->getName() == $name) {
        return $decorator;
      }
    }
    return null;
  }

  /**
   * @param \Civi\ConfigItems\Entity\EntityDefinition $entityDefinition
   * @param int $priority
   */
  public function addEntityDefinition($entityDefinition, $priority=Factory::NORMAL_PRIORITY) {
    $this->entities[$priority][] = $entityDefinition;
  }

  /**
   * @param $entityName
   * @return \Civi\ConfigItems\Entity\EntityDefinition|null
   */
  public function getEntityDefinition($entityName) {
    return $this->findByEntityName($entityName, $this->entities);
  }

  /**
   * Returns a list of entities available in the config item set.
   *
   * @param $config_item_set
   * @return array
   */
  public function getEntityListForConfigItemSet($config_item_set) {
    $entities = [];
    foreach ($this->getEntityList() as $entityName) {
      if (isset($config_item_set['entities'][$entityName]) && $config_item_set['entities'][$entityName]) {
        $entities[] = $entityName;
      }
    }
    return $entities;
  }

  /**
   * @param String $entityName
   * @param \Civi\ConfigItems\Entity\EntityDefinition[][] $list
   * @return \Civi\ConfigItems\Entity\EntityDefinition
   */
  private function findByEntityName($entityName, $list) {
    foreach($list as $priority => $entities) {
      foreach ($entities as $index => $entity) {
        if ($entity->getName() == $entityName) {
          return $list[$priority][$index];
        }
      }
    }
    return null;
  }

  /**
   * @param \Civi\ConfigItems\Entity\EntityDefinition $entity
   * @param \Civi\ConfigItems\Entity\EntityDefinition[][] $list
   */
  private function addEntityDefinitionToBuildList(EntityDefinition $entity, $list) {
    if (!empty($entity->getAfterEntities())) {
      foreach($entity->getAfterEntities() as $afterEntityName) {
        if (!in_array($afterEntityName, $this->build)) {
          $afterEntity = $this->findByEntityName($afterEntityName, $list);
          if ($afterEntity) {
            $this->addEntityDefinitionToBuildList($afterEntity, $list);
          }
        }
      }
    }
    if (!in_array($entity->getName(), $this->build)) {
      $this->build[] = $entity->getName();
    }
  }

}
