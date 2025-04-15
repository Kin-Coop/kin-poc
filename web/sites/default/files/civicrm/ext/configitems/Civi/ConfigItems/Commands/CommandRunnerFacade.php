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

namespace Civi\ConfigItems\Commands;

use Civi\ConfigItems\Commands\Runner\SymfonyProcess;

class CommandRunnerFacade {

  /**
   * Returns a command runner. For now we only have an implementation of the
   * Symfony Process class.
   *
   * This function could be used to determine what sort of process runner we need.
   * Preferably the Symonfy Process but we are not sure yet whether that class exists
   * on every CiviCRM installation (such as Wordpress and or Joomla).
   *
   * @return \Civi\ConfigItems\Commands\Runner\CommandRunnerInterface
   */
  public static function getRunner() {
    return new SymfonyProcess();
  }

}
