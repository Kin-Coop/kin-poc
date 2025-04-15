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

use CRM_Civiconfig_ExtensionUtil as E;
use Symfony\Component\Process\Process;

class Composer {

  protected static $composerCommand;

  public static function install($path, $composerHome=null) {
    if (file_exists($path . DIRECTORY_SEPARATOR . "composer.json")) {
      $cmd = self::composerCommand() . " install --no-interaction";
      $env = [
        'COMPOSER_HOME' => $path
      ];
      if (!empty($composerHome)) {
        $env['COMPOSER_HOME'] = $composerHome;
      }
      CommandRunnerFacade::getRunner()->run($cmd, $path, $env);
    }
  }

  /**
   * Checks whether the git command is able to run.
   *
   * @return bool
   * @throws \RuntimeException
   */
  public static function checkCommand(): bool {
    $cwd = getcwd();
    $composer = self::composerCommand();
    $cmd = "which ".$composer;
    try {
      $process = new Process([$cmd], $cwd);
      $process->run();
    } catch (\Exception $ex) {
      $msg = E::ts('%1 command not found. Details: %2', [1=>$composer, 2=>$ex->getMessage()]);
      throw new \RuntimeException($msg,  $ex->getCode(), $ex);
    }
    if (!$process->isSuccessful()) {
      $msg = E::ts('%1 command not found.', [1=>$composer]);
      throw new \RuntimeException($msg);
    }
    return TRUE;
  }

  /**
   * Returns the git command.
   *
   * @return string
   */
  public static function composerCommand() {
    if (empty(self::$composerCommand)) {
      $settings = \CRM_Civiconfig_Settings::getSettings();
      if (!empty($settings['composer_command'])) {
        self::$composerCommand = $settings['composer_command'];
      } else {
        self::$composerCommand = "composer";
      }
    }
    return self::$composerCommand;
  }

}
