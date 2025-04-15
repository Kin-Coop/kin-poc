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

namespace Civi\ConfigItems\UrlReplacer\Utils;

use Civi\ConfigItems\UrlReplacer\ExternalLink;
use Civi\ConfigItems\UrlReplacer\Image;
use Civi\ConfigItems\UrlReplacer\ResourceUrl;
use Civi\ConfigItems\UrlReplacer\Url;

class FindUrlsInHTML {

  /**
   * @param $html
   * @return \Civi\ConfigItems\UrlReplacer\Url[]
   */
  public static function getUrls($html, $entityType, $entityName, $field, $label) {
    $cmsRootUrl = \Civi::paths()->getUrl('[cms.root]/', 'absolute');
    $civicrmFilesUrl = \Civi::paths()->getUrl('[civicrm.files]/', 'absolute');
    $return = [];
    $foundUrls = [];
    $foundUrls = self::findHrefUrls($html, $foundUrls);
    foreach($foundUrls as $foundUrl) {
      if (strpos($foundUrl, $cmsRootUrl) === 0 || strpos($foundUrl, $civicrmFilesUrl) === 0) {
        $return[] = new ResourceUrl($entityType, $entityName, $field, $foundUrl);
      } else {
        $return[] = new ExternalLink($entityType, $entityName, $field, $foundUrl, $label);
      }
    }
    $foundImages = [];
    $foundImages = self::findImageUrls($html, $foundImages);
    foreach($foundImages as $foundImage) {
      $return[] = new Image($entityType, $entityName, $field, $foundImage);
    }
    return $return;
  }

  /**
   * Find any HREF-style URLs and replace them.
   *
   * @param string $html
   * @param array $urls
   * @return array
   */
  public static function findHrefUrls($html, $urls) {
    $exp = ';(\<a[^>]*href *= *("|\'))([^("|\')>]+)("|\');iu';
    $matches = [];
    preg_match_all($exp, $html, $matches, PREG_SET_ORDER, 0);
    foreach($matches as $match) {
      if (!in_array($match[3], $urls) && self::isValidUrl($match[3])) {
        $urls[] = $match[3];
      }
    }
    return $urls;
  }

  /**
   * Find any HREF-style URLs and replace them.
   *
   * @param string $html
   * @param array $urls
   * @return array
   */
  public static function findImageUrls($html, $urls) {
    $exp = ';(\<img[^>]*src *= *("|\'))([^("|\')>]+)("|\');iu';
    $matches = [];
    preg_match_all($exp, $html, $matches, PREG_SET_ORDER, 0);
    foreach($matches as $match) {
      if (!in_array($match[3], $urls) && self::isValidUrl($match[3])) {
        $urls[] = $match[3];
      }
    }
    return $urls;
  }

  public static function isValidUrl($url) {
    if ($url == '#') {
      return FALSE;
    }
    if (strpos($url, '{action.')===0) {
      return FALSE;
    }
    return TRUE;
  }

}
