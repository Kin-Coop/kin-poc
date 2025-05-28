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

interface EntityImporter {

  /**
   * Returns the import configuration form.
   * Returns false if this entity does not have a configuration for import.
   *
   * @return false|ConfigurationForm
   */
  public function getImportConfigurationForm();

  /**
   * Add tasks to the import queue.
   *
   * You can add multiple tasks, for example if a task might take long, such as installing
   * an extension you can add a task for each extension. This way we prevent browser timeouts.
   *
   * @param \Civi\ConfigItems\QueueService $queue
   * @param $configuration
   * @param $config_item_set
   * @return void
   */
  public function addImportTasksToQueue(\Civi\ConfigItems\QueueService $queue, $configuration, $config_item_set);

  /**
   * Returns the entity definition
   *
   * @return \Civi\ConfigItems\Entity\EntityDefinition
   */
  public function getEntityDefinition();

  /**
   * Checks whether import entity data exists.
   *
   * @param $config_item_set
   * @return bool
   */
  public function entityImportDataExists($config_item_set);

  /**
   * Load the entity data.
   *
   * @param $config_item_set
   * @return array
   * @throws \Civi\ConfigItems\FileFormat\EntityImportDataException
   */
  public function loadEntityImportData($config_item_set);

  /**
   * Returns the help text.
   * Return an empty string if no help is available.
   *
   * @return string
   */
  public function getHelpText();

}
