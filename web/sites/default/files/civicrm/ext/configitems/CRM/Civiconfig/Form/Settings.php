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

class CRM_Civiconfig_Form_Settings extends CRM_Core_Form {

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('Configuration Set Settings'));

    $this->addCheckBox('overrride_git_command', E::ts('Override Git'), [E::ts('Specify a custom path to git') => '1']);
    $this->add('text', 'git_command', E::ts('GIT Command'), array('size' => CRM_Utils_Type::HUGE, 'class' => 'huge40'), FALSE);
    $this->addCheckBox('overrride_composer_command', E::ts('Override Composer'), [E::ts('Specify a custom path to composer') => '1']);
    $this->add('text', 'composer_command', E::ts('Composer Command'), array('size' => CRM_Utils_Type::HUGE, 'class' => 'huge40'), FALSE);

    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Cancel'))));
  }

  /**
   * Function to set default values (overrides parent function)
   *
   * @return array $defaults
   * @access public
   */
  function setDefaultValues() {
    $defaults = CRM_Civiconfig_Settings::getSettings();
    return $defaults;
  }

  public function postProcess() {
    $values = $this->exportValues();
    CRM_Civiconfig_Settings::saveSettings($values);

    $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig', array('reset' => 1));
    CRM_Utils_System::redirect($redirectUrl);
  }

}
