<?php

use Civi\Api4\Contribution;
use Civi\Api4\ContributionRecur;

/**
 * Contribution.Canceloldpendingrecurring API
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
function civicrm_api3_contribution_Canceloldpendingrecurring($params) {
  $results = [
    'processed' => 0,
    'updated' => 0,
    'errors' => [],
  ];

  try {
    // Determine date cutoff (10 days ago)
    $cutoffDate = date('Y-m-d', strtotime('-10 days'));

    // Fetch all matching contributions using APIv4
    $contributions = Contribution::get()
      ->addSelect('id', 'contact_id', 'contribution_recur_id', 'receive_date', 'status_id', 'kin_group')
      ->addJoin('ContributionRecur AS recur', 'INNER', ['contribution_recur_id', '=', 'recur.id'])
      ->addWhere('contribution_status_id:name', '=', 'Pending')
      ->addWhere('recur.contribution_status_id', '=', 5) // "In Progress"
      ->addWhere('receive_date', '<=', $cutoffDate)
      ->addWhere('Kin_Contributions.Household', '!=', 425)
      ->addWhere('contribution_recur_id', 'IS NOT EMPTY')
      ->execute();

    foreach ($contributions as $c) {
      $results['processed']++;

      $contacts = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('id', 'email_primary.email')
        ->addWhere('id', '=', $c['contact_id'])
        ->execute()
        ->first();

      $contribution = \Civi\Api4\Contribution::get(FALSE)
        ->addSelect('id', 'Kin_Contributions.Household.display_name')
        ->addWhere('id', '=', $c['id'])
        ->execute()
        ->first();

      try {
        // Cancel recurring contribution
        /*
        ContributionRecur::update()
          ->addWhere('id', '=', $c['contribution_recur_id'])
          ->addValue('contribution_status_id:name', 'Cancelled')
          ->execute();

        // Mark contribution as failed
        Contribution::update()
          ->addWhere('id', '=', $c['id'])
          ->addValue('contribution_status_id:name', 'Failed')
          ->execute();
        */

        /*
        // Send message template ID 131
        civicrm_api3('Email', 'Send', [
          'template_id' => 131,
          'contact_id' => $c['contact_id'],
          'contribution_id' => $c['id'],
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
            'contactId' => $c['contact_id'],
            'contributionId' => $c['id'],
          ],
          'tplParams' => [
            'group' => $contribution['Kin_Contributions.Household.display_name'],
          ],
          'toEmail' => $contacts['email_primary.email'],
          'from' => '"Kin" <members@kin.coop>',
        ]);


        $results['updated']++;
      }
      catch (Exception $e) {
        $results['errors'][] = "Contribution ID {$c['id']}: " . $e->getMessage();
      }
    }

    return civicrm_api3_create_success($results, $params, 'Contribution', 'cancelOldPendingRecurring');
  }
  catch (Exception $e) {
    return civicrm_api3_create_error($e->getMessage());
  }
}

