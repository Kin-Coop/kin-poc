<?php
use CRM_Kincoop_ExtensionUtil as E;

/**
 * Contact.Checkmembership API specification (optional)
 * This is used for documentation and validation.
 *
 *  This custom job checks all contacts tagged "Recurring_Member" (ID: 10).
 *  If their Membership_Valid_Until (custom_79) is today or earlier,
 *  it removes "Recurring_Member" and adds "Pending_Member" (ID: 8).
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */

/*
function _civicrm_api3_contact_Checkmembership_spec(&$spec) {
  $spec['magicword']['api.required'] = 1;
}
*/

/**
 * Contact.Checkmembership API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws CRM_Core_Exception
 */
function civicrm_api3_contact_Checkmembership($params) {
  $today = new DateTime('today');
  $updated = 0;

  try {
    // 1️⃣ Find all contacts that currently have the tag "Recurring_Member" (id 10)
    $contacts = civicrm_api3('EntityTag', 'get', [
      'entity_table' => 'civicrm_contact',
      'tag_id' => 10,
      'return' => ['entity_id'],
      'options' => ['limit' => 0],
    ]);

    foreach ($contacts['values'] as $contactTag) {
      $cid = $contactTag['entity_id'];

      // 2️⃣ Get the Membership_Valid_Until (custom_80) field value
      $contact = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'id' => $cid,
        'is_deleted' => 0,
        'return' => ['custom_80'],
      ]);

      if (empty($contact['count']) || empty($contact['values'][0]['custom_80'])) {
        continue; // no date set, skip
      }

      $membershipValidUntil = new DateTime($contact['values'][0]['custom_80']);

      // 3️⃣ Compare with today's date
      if ($membershipValidUntil < $today) {
        // 4️⃣ Remove Recurring_Member tag
        civicrm_api3('EntityTag', 'delete', [
          'entity_table' => 'civicrm_contact',
          'entity_id' => $cid,
          'tag_id' => 10,
        ]);

        // 5️⃣ Add Pending_Member tag
        civicrm_api3('EntityTag', 'create', [
          'entity_table' => 'civicrm_contact',
          'entity_id' => $cid,
          'tag_id' => 8,
        ]);

        $updated++;
      }
    }

    $message = "Processed {$updated} contacts with expired membership dates.";
    return civicrm_api3_create_success($message, $params, 'Contact', 'Checkmembership');
  }
  catch (Exception $e) {
    return civicrm_api3_create_error($e->getMessage());
  }
}
