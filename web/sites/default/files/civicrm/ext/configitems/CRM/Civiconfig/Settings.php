<?php
/**
 * Copyright (C) 2022  Jaap Jansma (jaap.jansma@civicoop.org)
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

use CRM_Civiconfig_ExtensionUtil as E;

class CRM_Civiconfig_Settings {

  /**
   * Checks the requirements.
   *
   * @param $displayStatusMessage
   *
   * @return bool
   */
  public static function checkRequirements($displayStatusMessage=FALSE) {
    $isValid = TRUE;
    $message = [];
    try {
      \Civi\ConfigItems\Commands\Git::checkCommand();
    } catch (\RuntimeException $ex) {
      $isValid = FALSE;
      $message[] = $ex->getMessage();
    }

    try {
      \Civi\ConfigItems\Commands\Composer::checkCommand();
    } catch (\RuntimeException $ex) {
      $isValid = FALSE;
      $message[] = $ex->getMessage();
    }

    if ($displayStatusMessage && !$isValid) {
      $settingsUrl = CRM_Utils_System::url('civicrm/admin/civiconfig/settings', ['reset' => 1]);
      $message[] = E::ts('Check your <a href="%1">settings</a>', [1=>$settingsUrl]);
      $message = implode("<br><br>", $message);
      \CRM_Core_Session::setStatus($message, E::ts('Not all requirements are met'), 'error');
    }
    return $isValid;
  }

  /**
   * Returns the settings
   *
   * @return array
   */
  public static function getSettings() {
    $settings = [
      'overrride_git_command' => [],
      'git_command' => '',
      'overrride_composer_command' => [],
      'composer_command' => '',
    ];
    if (Civi::settings()->get('civiconfig_override_git')) {
      $settings['overrride_git_command'][1] = 1;
      $settings['git_command'] = Civi::settings()->get('civiconfig_git');
    }
    if (Civi::settings()->get('civiconfig_override_composer')) {
      $settings['overrride_composer_command'][1] = 1;
      $settings['composer_command'] = Civi::settings()->get('civiconfig_composer');
    }
    return $settings;
  }

  public static function saveSettings($settings) {
    if (isset($settings['overrride_git_command']) && isset($settings['overrride_git_command'][1]) && $settings['overrride_git_command'][1]) {
      \Civi::settings()->set('civiconfig_override_git', '1');
      \Civi::settings()->set('civiconfig_git', $settings['git_command']);
    } else {
      \Civi::settings()->set('civiconfig_override_git', '0');
      \Civi::settings()->set('civiconfig_git', '');
    }

    if (isset($settings['overrride_composer_command']) && isset($settings['overrride_composer_command'][1]) && $settings['overrride_composer_command'][1]) {
      \Civi::settings()->set('civiconfig_override_composer', '1');
      \Civi::settings()->set('civiconfig_composer', $settings['composer_command']);
    } else {
      \Civi::settings()->set('civiconfig_override_composer', '0');
      \Civi::settings()->set('civiconfig_composer', '');
    }
  }

}
