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

use Civi\ConfigItems\Entity\ImportDirectly;
use CRM_Civiconfig_ExtensionUtil as E;

class CRM_Civiconfig_Form_CancelImportJob extends CRM_Core_Form {

  /**
   * Run the basic page (run essentially starts execution for that page).
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('Cancel import Job'));
    $this->addButtons(array(
      array('type' => 'next', 'name' => E::ts('Cancel Job'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => E::ts('Go back'))));
  }

  public function postProcess() {
    $queue = civiconfig_get_queue_service();
    if ($queue->getQueue()->numberOfItems() > 0) {
      $queue->getQueue()->deleteQueue();
    }

    $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig', array('reset' => 1));
    CRM_Utils_System::redirect($redirectUrl);
  }

}
