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

interface ConfigurableUrl {

  /**
   * Returns an array with the link options.
   *
   * @return array
   */
  public function getReplacementOptions();

  /**
   * Returns a label for the URL.
   *
   * @return string
   */
  public function getLabel();

  /**
   * @param $configuration
   * @param $config_item_set
   *
   * @return bool
   */
  public function hasAdditionalConfigurationForImport($configuration, $config_item_set);

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return ConfigurationForm
   */
  public function getImportConfigurationForm();

  /**
   * Returns the export configuration form.
   * Returns false if this entity does not have a configuration for export.
   *
   * @return ConfigurationForm
   */
  public function getExportConfigurationForm();

  /**
   * Return a label describing the configuration.
   *
   * @param $configuration
   * @param $config_item_set
   *
   * @return string
   */
  public function getExportConfigurationLabel($configuration, $config_item_set);

}
