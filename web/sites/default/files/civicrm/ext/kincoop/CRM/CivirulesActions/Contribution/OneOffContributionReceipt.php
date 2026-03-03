<?php

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

    // Get the message template ID from the action parameters
    $actionParams = $this->getActionParameters();
    $messageTemplateId = $actionParams['message_template_id'] ?? NULL;

    if (empty($messageTemplateId)) {
      \Civi::log()->error('OneOffContributionReceipt action: No message template selected.');
      return;
    }

    try {
      $params = [
        'id' => $messageTemplateId, // Use the configured template ID
        'contact_id' => $contribution['contact_id'],
        'contribution_id' => $contribution['id'],
        'tokenContext' => [
          'contactId' => $contribution['contact_id'],
          'contributionId' => $contribution['id'],
        ],
        'from' => '"Kin Cooperative" <members@kin.coop>',
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

  /**
   * Returns the URL to the configuration form.
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/contribution/oneoffreceipt', 'rule_action_id=' . $ruleActionId);
  }

  /**
   * Returns a user-friendly description of the action configuration.
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $messageTemplateId = $params['message_template_id'] ?? NULL;

    if (empty($messageTemplateId)) {
      return 'No message template selected';
    }

    try {
      $template = civicrm_api3('MessageTemplate', 'getsingle', [
        'id' => $messageTemplateId,
      ]);
      return 'Send message template: ' . $template['msg_title'];
    } catch (Exception $e) {
      return 'Message template ID: ' . $messageTemplateId;
    }
  }

}
