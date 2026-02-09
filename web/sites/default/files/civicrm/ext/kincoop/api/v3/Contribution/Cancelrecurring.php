<?php

use Civi\Api4\Contribution;
use Civi\Api4\ContributionRecur;

/**
 * Contribution.Cancelrecurring API
 *
 * Cancel pending contributions older than 10 days linked to "In Progress" recurring contributions,
 * excluding contributions where custom field kin_group = 425.
 *
 * @param array $params
 *
 * @return array
 *    API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws CRM_Core_Exception
 *
 */
function civicrm_api3_contribution_Cancelrecurring($params) {
  $results = [
    'processed' => 0,
    'updated' => 0,
    'errors' => [],
  ];

  if($params['contribution_id']) {
    try {
      $contribution_id = $params['contribution_id'];

      $contribution = \Civi\Api4\Contribution::get(FALSE)
         ->addSelect('id', 'Kin_Contributions.Household.display_name', 'contact_id', 'contribution_recur_id')
         ->addWhere('id', '=', $contribution_id)
         ->execute()
         ->first();

      $contacts = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('id', 'email_primary.email')
        ->addWhere('id', '=', $contribution['contact_id'])
        ->execute()
        ->first();

      try {
        // Cancel recurring contribution
        ContributionRecur::update(FALSE)
         ->addValue('contribution_status_id', 3) // "Cancelled"
         ->addValue('cancel_reason', 'Automatic civirule after contribution failed for 10 days')
         ->addWhere('id', '=', $contribution['contribution_recur_id'])
         ->execute();

        /*
        // Mark contribution as failed
        Contribution::update()
          ->addWhere('id', '=', $contribution_id)
          ->addValue('contribution_status_id:name', 'Failed')
          ->execute();
        */

        /*
        // Send message template ID 131
        civicrm_api3('Email', 'Send', [
          'template_id' => 131,
          'contact_id' => $contribution['contact_id'],
          'contribution_id' => $contribution_id,
          'extra_data' => ['contact' => 477],
          //'valueName' => 'contribution',
          //'template_params' => "'group' => 'my group'",
        ]);
        */

        // Send email to delegate confirming contribution
        $delivery = \CRM_Core_BAO_MessageTemplate::sendTemplate([
          //'workflow' => 'onbehalfof_delegate_completed',
          'messageTemplateID' => 131,
          'tokenContext' => [
            'contactId' => $contribution['contact_id'],
            'contributionId' => $contribution_id,
          ],
          'tplParams' => [
            'group' => $contribution['Kin_Contributions.Household.display_name'],
          ],
          'toEmail' => $contacts['email_primary.email'],
          'from' => '"Kin" <members@kin.coop>',
        ]);
      }
      catch (Exception $e) {
        $results['errors'][] = "Contribution ID {$contribution_id}: " . $e->getMessage();
      }

      return civicrm_api3_create_success($results, $params, 'Contribution', 'cancelOldPendingRecurring');
    }
    catch (Exception $e) {
      return civicrm_api3_create_error($e->getMessage());
    }
  }
}

