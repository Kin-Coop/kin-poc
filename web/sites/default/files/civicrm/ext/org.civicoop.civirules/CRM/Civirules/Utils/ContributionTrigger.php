<?php
/**
 * Copyright (C) 2025  Jaap Jansma (jaap.jansma@civicoop.org)
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

class CRM_Civirules_Utils_ContributionTrigger {

  protected static $participantId = null;

  public static function pre($op, $objectName, $objectId, &$params) {
    if ($op == 'create' && $objectName == 'Contribution') {
      self::$participantId = null;
      if (isset($params['participant_id'])) {
        self::$participantId = $params['participant_id'];
      }
    }
  }

  public static function getParticipantId():? int {
    return self::$participantId;
  }

}
