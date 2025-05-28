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

use Symfony\Component\Process\Process;
use CRM_Civiconfig_ExtensionUtil as E;

class Git {

  private static $gitCommand = "";

  /**
   * Clone a  git repository.
   *
   * @param $repoUrl
   * @param $cwd
   * @param null $name
   * @param null $branch
   * @throws \RuntimeException
   */
  public static function cloneRepository($repoUrl, $cwd, $name=null, $branch=null) {
    $cmd = self::gitCommand()." clone --single-branch --no-tags --depth=1 ".$repoUrl;
    if (!empty($name)) {
      $cmd .= " ".$name;
    }
    if (!empty($branch)) {
      $cmd .= " -b ".$branch;
    }
    CommandRunnerFacade::getRunner()->run($cmd, $cwd);
  }

  /**
   * Checks whether the git command is able to run.
   *
   * @return bool
   * @throws \RuntimeException
   */
  public static function checkCommand(): bool {
    $cwd = getcwd();
    $git = self::gitCommand();
    $cmd = "which ".$git;
    try {
      $process = new Process([$cmd], $cwd);
      $process->run();
    } catch (\Exception $ex) {
      $msg = E::ts('%1 command not found. Details: %2', [1=>$git, 2=>$ex->getMessage()]);
      throw new \RuntimeException($msg,  $ex->getCode(), $ex);
    }
    if (!$process->isSuccessful()) {
      $msg = E::ts('%1 command not found.', [1=>$git]);
      throw new \RuntimeException($msg);
    }
    return TRUE;
  }

  /**
   * Returns the git command.
   *
   * @return string
   */
  public static function gitCommand() {
    if (empty(self::$gitCommand)) {
      $settings = \CRM_Civiconfig_Settings::getSettings();
      if (!empty($settings['git_command'])) {
        self::$gitCommand = $settings['git_command'];
      } else {
        self::$gitCommand = "git";
      }
    }
    return self::$gitCommand;
  }

}
