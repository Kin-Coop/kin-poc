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

use Civi\ConfigItems\Entity\SimpleEntity\Importer as SimpleEntityImporter;

use CRM_Civiconfig_ExtensionUtil as E;

class Importer extends SimpleEntityImporter {

  /**
   * @param $group
   * @param $data
   *
   * @return array
   */
  public function getOptions($group, $data) {
    if ($group == 'include' && isset($data[$this->entityDefinition->getIdAttribute()])) {
      $return[0] = E::ts('Do not update');
      $return[1] = E::ts('Update (keep current value)');
      $return[2] = E::ts('Update (with value: %1)', [1=>$data['value']]);
    } elseif ($group == 'include' && !isset($data[$this->entityDefinition->getIdAttribute()])) {
      $return[1] = E::ts('Add (generate a new value)');
      $return[2] = E::ts('Add (with value: %1)', [1=>$data['value']]);
      $return[0] = E::ts('Do not add');
    } elseif ($group == 'remove' && isset($data[$this->entityDefinition->getIdAttribute()])) {
      $return[0] = E::ts('Keep');
      $return[1] = E::ts('Remove');
    }
    return $return;
  }

  /**
   * Returns attributes which should not be exported.
   *
   * Contains the ID attribute by default.
   *
   * @return array
   */
  protected function getIgnoredAttributes() {
    $ignored = parent::getIgnoredAttributes();
    $ignored['include'][1][] = 'option_group_id';
    $ignored['include'][1][] = 'value';
    $ignored['include'][2][] = $this->entityDefinition->getIdAttribute();
    $ignored['include'][2][] = 'option_group_id';
    return $ignored;
  }

  protected function getApiParams($group, $data, $configurationValue) {
    $params = parent::getApiParams($group, $data, $configurationValue);
    $apiAction = $this->getApiAction($group, $data);
    if ($apiAction == 'create') {
      $params['values']['option_group_id'] = $this->entityDefinition->getOptionGroupId();
    }
    return $params;
  }

}
