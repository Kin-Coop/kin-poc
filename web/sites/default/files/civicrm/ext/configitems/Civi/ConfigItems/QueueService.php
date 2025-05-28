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

namespace Civi\ConfigItems;

use CRM_Civiconfig_ExtensionUtil as E;

/**
 * The queue service holds functions to interact with queue.
 *
 * A queue exists of tasks and we want to have some control over whether we have a task
 * which might take long. E.g. installing an extension, then we want to have one task for each extension
 * but if we add message templates and option values both could be done in one task.
 *
 * So we have a function to add callbacks to the current task.
 * And we have a function to create a 'standalone' task.
 *
 * This queue service also has function to run the tasks which in turns calls the callbacks.
 */
class QueueService {

  /**
   * @var array
   */
  protected $callbacks = [];

  /**
   * @var \CRM_Queue_Queue
   */
  protected $queue;

  /**
   * @param $entityTitle
   * @param callable $callback
   * @param array $parameters
   */
  public function addCallbackToCurrentTask($entityTitle, $callback, $parameters=[]) {
    $this->callbacks[] = [
      'callback' => $callback,
      'parameters' => $parameters,
      'entityTitle' => $entityTitle
    ];
  }

  /**
   * Adds a new task to the queue.
   *
   * If $this->callbacks is not empty then this will be first added as a task.
   *
   * @param $title
   * @param $callback
   * @param $parameters
   * @param bool $prepend
   *  When true prepend the task at the beginning of the queue.
   */
  public function addNewTask($title, $callback, $parameters, $prepend=false) {
    $this->createTask();
    $this->callbacks[] = [
      'callback' => $callback,
      'parameters' => $parameters,
    ];
    $options = [];
    if ($prepend) {
      $options['weight'] = -1;
    }
    $task = new \CRM_Queue_Task([static::class, 'runCallbacks'], [$this->callbacks], $title);
    $this->getQueue()->createItem($task, $options);
    $this->callbacks = [];
  }

  /**
   * Create a task from the current callback.
   * Ths function needs to be called to create a task from the current callbacks.
   *
   * Make sure you call this function at the end so that all callbacks are written to the queue
   * as a task.
   */
  public function createTask() {
    if (!empty($this->callbacks)) {
      $arrTitles = [];
      foreach($this->callbacks as $callback) {
        $arrTitles[] = $callback['entityTitle'];
      }
      $strTitles = implode(", ", $arrTitles);

      $task = new \CRM_Queue_Task([static::class, 'runCallbacks'], [$this->callbacks], E::ts('Import entities: %1', [1=>$strTitles]));
      $this->getQueue()->createItem($task);
      $this->callbacks = [];
    }
  }

  /**
   * Create a last task for this import.
   *
   * @param $config_item_set
   */
  public function lastTask($config_item_set) {
    $this->createTask();

    $options['weight'] = 999;
    $task = new \CRM_Queue_Task([static::class, 'finishImport'], [$config_item_set], E::ts('Finishing import'));
    $this->getQueue()->createItem($task, $options);
  }

  /**
   *
   * @param \CRM_Queue_TaskContext $ctx
   * @param $callbacks
   *
   * @return bool
   */
  public static function runCallbacks(\CRM_Queue_TaskContext $ctx, $callbacks) {
    foreach($callbacks as $callback) {
      $args = isset($callback['parameters']) && is_array($callback['parameters']) ? $callback['parameters'] : array();
      $args[] = $ctx;
      call_user_func_array($callback['callback'], $args);
    }
    return TRUE;
  }

  /**
   * Finish an import job and clear the import configuration.
   *
   * This is done because we want at a new import that the user makes
   * new choices and some choices made have disappeared. Such as in the first import
   * the choice is between add an item or do not add. In the second import it is between
   * update an existing item or do not update.
   *
   * @param \CRM_Queue_TaskContext $ctx
   * @param $config_item_set
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   */
  public static function finishImport(\CRM_Queue_TaskContext $ctx, $config_item_set) {
    $values['import_configuration'] = null;
    civicrm_api4('ConfigItemSet', 'update', [
      'values' => $values,
      'where' => [['id', '=', $config_item_set['id']]],
    ]);
    \CRM_Core_Session::setStatus(E::ts('Imported %1', [1 => $config_item_set['title']]), E::ts('Finished import'), 'success');
    return TRUE;
  }

  /**
   * @return \CRM_Queue_Queue
   */
  public function getQueue() {
    if (!$this->queue) {
      $this->queue = \CRM_Queue_Service::singleton()->create([
        'type'  => 'Sql',
        'name'  => 'civiconfig_import_queue',
        'reset' => FALSE,
      ]);
    }
    return $this->queue;
  }



}
