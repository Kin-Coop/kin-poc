<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

namespace Civi\Searchkitmaps;

use CRM_Searchkitmaps_ExtensionUtil as E;

class Utils {

  /**
   * Get a list of JS variables (`CRM.crmSearchkitmaps`) to provide to the browser.
   *
   * @return array
   */
  public static function getSettings() {
    return [
      'basePath' => E::url(),
    ];
  }

}
