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

class RelationshipType extends Definition {

  /**
   * @param array $afterEntities
   * @param array $beforeEntities
   */
  public function __construct($afterEntities=[], $beforeEntities=[]) {
    parent::__construct('RelationshipType', 'RelationshipType', $afterEntities, $beforeEntities);
    $this->titleAttribute = 'label_a_b';
    $this->nameAttribute = 'name_a_b';
  }

}
