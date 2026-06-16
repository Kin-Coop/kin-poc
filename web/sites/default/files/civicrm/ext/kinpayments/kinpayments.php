<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'kinpayments.civix.php';
// phpcs:enable

use CRM_Kinpayments_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
/*
function kinpayments_civicrm_config(\CRM_Core_Config $config): void {
  _kinpayments_civix_civicrm_config($config);
}
*/

function kinpayments_civicrm_config(&$config): void {
  _kinpayments_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function kinpayments_civicrm_install(): void {
  _kinpayments_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function kinpayments_civicrm_enable(): void {
  _kinpayments_civix_civicrm_enable();
}


// ── CSV Import hook ──────────────────────────────────────────────────────────

/**
 * Implements hook_civicrm_import_post_import().
 *
 * Fired by nz.co.fuzion.csvimport (and core importers) after all rows
 * have been processed. We only trigger matching when the import target
 * entity is KinpaymentsPayment.
 *
 * Note: the exact hook name / signature may vary between versions of
 * nz.co.fuzion.csvimport. If this hook isn't fired, use a Scheduled Job
 * as a fallback (see api/v3/KinpaymentsPayment.php).
 *
 * @param string $objectName   Entity being imported.
 * @param array  $importParams Parameters passed to the importer.
 */
function kinpayments_civicrm_import_post_import(string $objectName, array $importParams): void {
  if (strtolower($objectName) !== 'kinpaymentspayment') {
    return;
  }

  try {
    $summary = \Civi\Api4\KinpaymentsPayment::matchPayments(FALSE)
      ->setIncludeUnmatched(FALSE)
      ->execute()
      ->first();

    \Civi::log()->info(
      'kinpayments: post-import matching complete. ' .
      'Matched: ' . ($summary['matched'] ?? 0) .
      ', Unmatched: ' . ($summary['unmatched'] ?? 0) .
      ', Pending review: ' . ($summary['pending'] ?? 0) .
      ', Errors: ' . ($summary['errors'] ?? 0)
    );
  }
  catch (\Exception $e) {
    \Civi::log()->error('kinpayments: post-import matching failed: ' . $e->getMessage());
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * Some versions of csvimport fire postProcess on the import form class
 * rather than a dedicated hook. This catches that case.
 *
 * @param string $formName
 * @param object $form
 */
/*
function kinpayments_civicrm_postProcess(string $formName, $form): void {
  // nz.co.fuzion.csvimport form class name – adjust if the extension uses a different name
  $csvImportForms = [
    'CRM_Csvimport_Form_Import',
    'CRM_Csvimport_Import_Form_DataSource',
    'CRM_Csvimport_Import_Form_Summary',
  ];

  if (!in_array($formName, $csvImportForms, TRUE)) {
    return;
  }

  // Only act on the final summary step
  $step = $form->get('_step') ?? $form->getVar('_step') ?? NULL;
  if ($step !== NULL && $step < 3) {
    return;
  }

  // Check the entity being imported
  $entity = $form->get('entity') ?? NULL;
  if ($entity && strtolower($entity) !== 'kinpaymentspayment') {
    return;
  }

  try {
    \Civi\Api4\KinpaymentsPayment::matchPayments(FALSE)
      ->setIncludeUnmatched(FALSE)
      ->execute();
  }
  catch (\Exception $e) {
    \Civi::log()->error('kinpayments: postProcess matching failed: ' . $e->getMessage());
  }
}
*/
