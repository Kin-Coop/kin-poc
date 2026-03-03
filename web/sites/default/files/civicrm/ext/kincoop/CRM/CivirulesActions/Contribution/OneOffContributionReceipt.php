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

    foreach ($contribution as $field => $value) {
      if (strpos($field, 'custom_') === 0 && !empty($value)) {
        $label = \CRM_Core_BAO_CustomField::getLabel($field);
        $body .= "<li><strong>{$label}:</strong> {$value}</li>";
      }
    }

    try {
      $params = [
        'id' => 134, // Message Template ID
        'contact_id' => $contribution['contact_id'], // Recipient’s contact ID
        'contribution_id' => $contribution['id'],
        'from' => '"Kin Cooperative" <members@kin.coop>',
        // Optional: specify email override if you want to force a specific address
        'to_email' => $contact['email_primary.email'],
        'tplParams' => [
          //'admin_name' => $admin['contact_id_a.first_name'],
          //'group' => $group_name["display_name"],
          //'amount' => $amount,
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
