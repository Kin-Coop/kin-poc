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

namespace Civi\ConfigItems\UrlReplacer;

interface ReplaceableUrls {

  /**
   * Returns the urls which can be replaced
   *
   * @param $configuration
   * @param $config_item_set
   * @return \Civi\ConfigItems\UrlReplacer\Url[]
   */
  public function getUrlsForExport($configuration, $config_item_set);

  /**
   * Returns the urls which can be replaced
   *
   * @param $configuration
   * @param $config_item_set
   * @param bool $loadAll
   *
   * @return \Civi\ConfigItems\UrlReplacer\Url[]
   */
  public function getUrlsForImport($configuration, $config_item_set, $loadAll=FALSE);

  /**
   * Returns the $entityData in which the urls are replaced.
   *
   * @param array $entityData
   * @params Url[] $urls
   * @param $configuration
   * @param $config_item_set
   * @return array
   */
  public function replaceUrls($entityData, $urls, $configuration, $config_item_set);

}
