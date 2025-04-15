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

use CRM_Civiconfig_ExtensionUtil as E;

interface ImportDirectly {

  /**
   * Returns a redirect url
   *
   * @param $configuration
   * @param $config_item_set
   * @param $action
   * @return string
   */
  public function getRedirectUrl($configuration, $config_item_set);

  /**
   * @param $configuration
   * @param $config_item_set
   *
   * @return bool
   */
  public function importDirectly($configuration, $config_item_set);

  /**
   * @return string
   */
  public function getNextButtonTitle();

}
