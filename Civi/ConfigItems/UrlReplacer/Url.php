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

interface Url {

  /**
   * @return string
   */
  public function getUrlTypeTitle();

  /**
   * @return string
   */
  public function getUrlType();

  /**
   * Get the unique key for this link.
   *
   * @return string
   */
  public function getUniqueKey();

  /**
   * @param $import_configuration
   * @param $configuration
   * @param $config_item_set
   */
  public function prepareForImport($import_configuration, $configuration, $config_item_set);

  /**
   * Exports the URL (possible to store images/attachments in the export file).
   * Returns $configuration
   *
   * @param $configuration
   * @param $config_item_set
   * @param string $directory
   * @return array
   */
  public function export($configuration, $config_item_set, $directory='');

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
  public function replace($entityType, $entityName, $fieldName, $content);

}
