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

use Civi\ConfigItems\ConfigurationForm;

interface Decorator {

  /**
   * Returns the name of this decorator.
   *
   * @return string
   */
  public function getName();

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return false|ConfigurationForm
   */
  public function getImportConfigurationForm();

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return false|ConfigurationForm
   */
  public function getExportConfigurationForm();

  /**
   * @param array $entityData
   * @param \Civi\ConfigItems\Entity\EntityDefinition $entityDefinition
   * @param array $configuration
   * @param array $config_item_set
   *
   * @return array
   */
  public function alterImportData($entityData, $entityDefinition, $configuration, $config_item_set);

  /**
   * @param array $entityData
   * @param \Civi\ConfigItems\Entity\EntityDefinition $entityDefinition
   * @param array $configuration
   * @param array $config_item_set
   *
   * @return array
   */
  public function alterExportData($entityData, $entityDefinition, $configuration, $config_item_set);

  /**
   * Exports the decorator. Returns the $configuration
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   * @return array
   */
  public function export($configuration, $config_item_set, $directory='');

}
