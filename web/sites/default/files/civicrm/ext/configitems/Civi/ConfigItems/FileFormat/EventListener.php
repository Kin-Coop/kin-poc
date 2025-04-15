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

namespace Civi\ConfigItems\FileFormat;

use Civi\Core\DAO\Event\PreDelete;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EventListener {

  /**
   * Make sure the import directory is removed.
   *
   * @param \Civi\Core\DAO\Event\PreDelete $event
   * @throws \Exception
   */
  public static function daoPreDelete(PreDelete $event) {
    if ($event->object instanceof \CRM_Civiconfig_DAO_ConfigItemSet) {
      $file_format_factory = civiconfig_get_fileformat_factory();
      $config_item_set = \Civi\Api4\ConfigItemSet::get()
        ->addWhere('id', '=', $event->object->id)
        ->setLimit(1)
        ->execute()
        ->first();
      if (!empty($config_item_set['import_file_format'])) {
        $fileFormatClass = $file_format_factory->getFileFormatClass($config_item_set['import_file_format']);
        $fileFormatClass->delete($config_item_set);
      }
    }
  }

  /**
   * Adds event listeners
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   */
  public static function addEventListeners(ContainerBuilder $container) {
    $container->findDefinition('dispatcher')
      ->addMethodCall('addListener', [
        'civi.dao.preDelete',
        [self::class, 'daoPreDelete']
      ]);
  }

}
