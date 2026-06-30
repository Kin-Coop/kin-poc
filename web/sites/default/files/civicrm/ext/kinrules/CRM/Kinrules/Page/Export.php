<?php

use CRM_Kinrules_ExtensionUtil as E;

/**
 * Page that streams the CiviRules export as a CSV download.
 *
 * Reachable at civicrm/kinrules/export
 */
class CRM_Kinrules_Page_Export extends CRM_Core_Page {

  public function run() {
    // Restrict to users who can administer CiviCRM (same level as CiviRules).
    if (!CRM_Core_Permission::check('administer CiviCRM')) {
      CRM_Core_Error::statusBounce(E::ts('You do not have permission to export CiviRules.'));
    }

    $csv = CRM_Kinrules_Export::toCsv();

    $filename = 'civirules-export-' . date('Ymd-His') . '.csv';

    CRM_Utils_System::download(
      $filename,
      'text/csv',
      $csv,
      NULL,
      FALSE
    );
    // download() exits, so nothing below runs.
  }

}
