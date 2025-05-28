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

namespace Civi\ConfigItems\Entity\OptionValue;

use Civi\ConfigItems\Entity\SimpleEntity\Exporter as SimpleEntityExporter;

use CRM_Civiconfig_ExtensionUtil as E;

class Exporter extends SimpleEntityExporter {


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
    $ignored = parent::getIgnoredAttributes();
    foreach($this->getGroups() as $group => $groupTitle) {
      $ignored[$group][] = 'option_group_id';
    }
    return $ignored;
  }


}
