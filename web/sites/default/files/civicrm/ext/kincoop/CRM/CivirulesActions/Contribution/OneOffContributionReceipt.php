<?php

//use CRM_CivirulesActions_Email_SendEmail as BaseSendEmail;

/**
 * Custom CiviRules action: Send email with custom contribution fields.
 */
class CRM_CivirulesActions_Contribution_OneOffContributionReceipt extends CRM_Civirules_Action {

  /**
   * Executes when the rule is triggered.
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contributionId = $triggerData->getEntityId();

    // Retrieve contribution with all custom fields.
    $contribution = \Civi\Api4\Contribution::get(FALSE)
      ->addSelect('*')
      ->addWhere('id', '=', $contributionId)
      ->setLimit(1)
      ->execute()
      ->first();

    if (!$contribution) {
      \Civi::log()->warning("Custom email skipped — no contribution found for ID $contributionId.");
      return;
    }

    // Retrieve related contact info.
    $contact = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('display_name', 'email_primary.email')
      ->addWhere('id', '=', $contribution['contact_id'])
      ->execute()
      ->first();

    $formattedDate = date('jS F Y \a\t g:ia', strtotime($contribution['receive_date']));

    try {
      $params = [
        'id' => 134, // Message Template ID
        'contact_id' => $contribution['contact_id'], // Recipient’s contact ID
        'contribution_id' => $contribution['id'],
        'tokenContext' => [
          'contactId' => $contribution['contact_id'],
          'contributionId' => $contribution['id'],
        ],
        'from' => '"Kin Cooperative" <members@kin.coop>',
        // Optional: specify email override if you want to force a specific address
        'to_email' => $contact['email_primary.email'],
        'tplParams' => [
          'date' => $formattedDate,
        ],
      ];

      civicrm_api3('MessageTemplate', 'send', $params);
    } catch (Exception $e) {
      \Civi::log()->error('Failed to send receipt to contact ' . $contribution['contact_id'] . ': ' . $e->getMessage());
    }
  }

  public function getExtraDataInputUrl($ruleActionId)
  {
    // TODO: Implement getExtraDataInputUrl() method.
    return FALSE;
  }
}
