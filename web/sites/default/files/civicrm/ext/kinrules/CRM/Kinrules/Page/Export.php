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

      // Emit the CSV directly and terminate before the theme/page shell renders.
      // Using CRM_Utils_System::download() alone was allowing CiviCRM to continue
      // rendering the surrounding HTML page, so we set headers ourselves and then
      // call civiExit() to stop cleanly.
      CRM_Utils_System::setHttpHeader('Content-Type', 'text/csv; charset=utf-8');
      CRM_Utils_System::setHttpHeader(
        'Content-Disposition',
        'attachment; filename="' . $filename . '"'
      );
      CRM_Utils_System::setHttpHeader('Content-Length', (string) strlen($csv));
      // Prevent caching of the export.
      CRM_Utils_System::setHttpHeader('Pragma', 'no-cache');
      CRM_Utils_System::setHttpHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
      CRM_Utils_System::setHttpHeader('Expires', '0');

      echo $csv;
      CRM_Utils_System::civiExit();
    }

  }
