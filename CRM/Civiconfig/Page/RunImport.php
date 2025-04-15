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

class CRM_Civiconfig_Page_RunImport extends CRM_Core_Page {

  /**
   * @var int
   */
  protected $id;

  protected $configItemSet;

  /**
   * Run the basic page (run essentially starts execution for that page).
   */
  public function run() {
    $redirectUrl = CRM_Utils_System::url('civicrm/admin/civiconfig', array('reset' => 1));
    $this->id = CRM_Utils_Request::retrieve('id', 'Integer');
    if ($this->id) {
      $this->configItemSet = Civi\Api4\ConfigItemSet::get()
        ->addWhere('id', '=', $this->id)
        ->setLimit(1)
        ->execute()
        ->first();
    }
    $queue = civiconfig_get_queue_service();
    if ($queue->getQueue()->numberOfItems() > 0) {
      $cancelUrl = CRM_Utils_System::url('civicrm/admin/civiconfig/cancelimportjob', ['reset' => 1]);
      CRM_Core_Session::setStatus(E::ts('Another import job does exists. Try again later or <a href="%1">cancel</a> the job.', [1=>$cancelUrl]), E::ts('Import job already exists'), 'error');
      CRM_Utils_System::redirect($redirectUrl);
    }

    $factory = civiconfig_get_entity_factory();
    $currentEntityName = CRM_Utils_Request::retrieve('entity', 'String');
    foreach($factory->getEntityList() as $entityName) {
      if (!empty($currentEntityName) && $currentEntityName != $entityName) {
        continue;
      }
      $entityClass = $factory->getEntityDefinition($entityName);
      if ($entityClass->getImporterClass()) {
        $configuration = [];
        if (isset($this->configItemSet['import_configuration']) && isset($this->configItemSet['import_configuration'][$entityName])) {
          $configuration = $this->configItemSet['import_configuration'][$entityName];
        }
        $entityClass->getImporterClass()->addImportTasksToQueue($queue, $configuration, $this->configItemSet);
        if (!empty($currentEntityName) && $entityClass->getImporterClass() instanceof ImportDirectly) {
            $redirectUrl = $entityClass->getImporterClass()->getRedirectUrl($configuration, $this->configItemSet);
        }
      }
    }
    if (empty($currentEntityName)) {
      $queue->lastTask($this->configItemSet);
    }

    $runner = new CRM_Queue_Runner([
      'title' => E::ts('Import %1', [1=>$this->configItemSet['title']]),
      'queue' => $queue->getQueue(),
      'onEndUrl' => $redirectUrl,
    ]);
    if ($queue->getQueue()->numberOfItems()) {
      $runner->runAllViaWeb();
    } else {
      CRM_Utils_System::redirect($redirectUrl);
    }

    CRM_Utils_System::setTitle(E::ts('Import %1', [1=>$this->configItemSet['title']]));
    parent::run();
  }

}
