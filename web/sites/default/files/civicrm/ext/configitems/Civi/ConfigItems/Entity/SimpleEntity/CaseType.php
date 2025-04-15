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

use CRM_Civiconfig_ExtensionUtil as E;

class CaseType extends Definition {

  /**
   * @param array $afterEntities
   * @param array $beforeEntities
   */
  public function __construct($afterEntities=[], $beforeEntities=[]) {
    parent::__construct('CaseType', 'CaseType', $afterEntities, $beforeEntities);
  }

  /**
   * @return bool
   */
  static function isAvailable() {
    return in_array('CiviCase', \Civi::settings()->get('enable_components'));
  }

  /**
   * Returns the help text.
   * Return an empty string if no help is available.
   *
   * @return string
   */
  public function getExportHelpText() {
    return E::ts('Make sure you also export the activity types and relationship types.');
  }

}
