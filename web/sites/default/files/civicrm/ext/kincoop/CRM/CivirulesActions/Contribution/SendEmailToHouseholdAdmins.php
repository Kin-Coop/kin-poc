<?php

use CRM_Civirules_ExtensionUtil as E;

/**
 * Custom CiviRules action: Send email to all Household Admins of the household
 * referenced in a contribution's custom field.
 */
class CRM_CivirulesActions_Contribution_SendEmailToHouseholdAdmins extends CRM_Civirules_Action {

  /**
   * Process the action.
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contributionId = $triggerData->getEntityId();

    // Get the contribution with the custom household field.
    try {
      $contribution = \Civi\Api4\Contribution::get(FALSE)
        ->addSelect('id', 'contact_id', 'Kin_Contributions.Household') // custom_25 is your Group field
        ->addWhere('id', '=', $contributionId)
        ->execute()
        ->first();

      if (!$contribution) {
        \Civi::log()->warning("SendEmailToHouseholdAdmins: Contribution {$contributionId} not found.");
        return;
      }

      // Get the household ID from the custom field.
      $householdId = $contribution['Kin_Contributions.Household'] ?? NULL;

      if (empty($householdId)) {
        \Civi::log()->warning("SendEmailToHouseholdAdmins: No household reference found for contribution {$contributionId}.");
        return;
      }

      // Get all active relationships where contact is admin of this household.
      $relationships = \Civi\Api4\Relationship::get(FALSE)
        ->addSelect('contact_id_a')
        ->addWhere('relationship_type_id', '=', 11)
        ->addWhere('contact_id_b', '=', $householdId)
        ->addWhere('is_active', '=', TRUE)
        ->execute();

      if ($relationships->count() == 0) {
        \Civi::log()->info("SendEmailToHouseholdAdmins: No active household admins found for household {$householdId}.");
        return;
      }

      // Get action parameters (message template, from email, etc.).
      $actionParams = $this->getActionParameters();
      $messageTemplateId = $actionParams['message_template_id'] ?? NULL;
      //$fromEmail = $actionParams['from_email'] ?? NULL;

      if (empty($messageTemplateId)) {
        \Civi::log()->error('SendEmailToHouseholdAdmins: No message template configured.');
        return;
      }

      // Send email to each admin.
      foreach ($relationships as $relationship) {
        $contactId = $relationship['contact_id_a'];

        // Get contact email.
        $contact = \Civi\Api4\Contact::get(FALSE)
          ->addSelect('id', 'display_name', 'email_primary.email')
          ->addWhere('id', '=', $contactId)
          ->execute()
          ->first();

        if (empty($contact['email_primary.email'])) {
          \Civi::log()->warning("SendEmailToHouseholdAdmins: Contact {$contactId} has no email address.");
          continue;
        }

        // Send the email using MessageTemplate API.
        try {
          $params = [
            'id' => $messageTemplateId,
            'contact_id' => $contactId,
            'contribution_id' => $contributionId,
            'from' => '"Kin Cooperative" <members@kin.coop>',
            'tokenContext' => [
              'contactId' => $contactId,
              'contributionId' => $contributionId,
            ],
            'to_email' => $contact['email_primary.email'],
          ];

          civicrm_api3('MessageTemplate', 'send', $params);

          \Civi::log()->info("SendEmailToHouseholdAdmins: Email sent to contact {$contactId} ({$contact['email_primary.email']}).");

        } catch (Exception $e) {
          \Civi::log()->error("SendEmailToHouseholdAdmins: Failed to send email to contact {$contactId}: " . $e->getMessage());
        }
      }

    } catch (Exception $e) {
      \Civi::log()->error('SendEmailToHouseholdAdmins: Error processing action: ' . $e->getMessage());
    }
  }

  /**
   * Returns the specification of the configuration options for the action.
   *
   * @return array
   */

  /*
  public function getConfigurationSpecification() {
    return [
      [
        'name' => 'message_template_id',
        'type' => 'Integer',
        'title' => E::ts('Message Template'),
        'required' => TRUE,
      ],
      [
        'name' => 'from_email',
        'type' => 'String',
        'title' => E::ts('From Email'),
        'required' => FALSE,
      ],
    ];
  }
  */

  /**
   * Returns a redirect URL to a page for setting the configuration.
   *
   * @param int $ruleActionId
   * @return string|bool
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/contribution/sendemailhouseholdadmins', 'rule_action_id=' . $ruleActionId);
  }

  /**
   * Returns a user-friendly description of the action configuration.
   *
   * @return string
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $messageTemplateId = $params['message_template_id'] ?? NULL;

    if (empty($messageTemplateId)) {
      return E::ts('No message template selected');
    }

    try {
      $template = civicrm_api3('MessageTemplate', 'getsingle', [
        'id' => $messageTemplateId,
      ]);
      return E::ts('Send "%1" to all Household Admins of the referenced household', [1 => $template['msg_title']]);
    } catch (Exception $e) {
      return E::ts('Send message template ID %1 to all Household Admins', [1 => $messageTemplateId]);
    }
  }

}
