<?php

use CRM_Civirules_ExtensionUtil as E;

/**
 * Form for configuring the Send Email to Household Admins action.
 */
class CRM_CivirulesActions_Contribution_Form_SendEmailHouseholdAdmins extends CRM_CivirulesActions_Form_Form {

  /**
   * Build the form.
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');

    // Get all active message templates.
    $messageTemplates = [];
    try {
      $templates = civicrm_api3('MessageTemplate', 'get', [
        'is_active' => 1,
        'workflow_id' => ['IS NULL' => 1],
        'options' => ['limit' => 0, 'sort' => 'msg_title ASC'],
      ]);

      foreach ($templates['values'] as $template) {
        $messageTemplates[$template['id']] = $template['msg_title'];
      }
    } catch (Exception $e) {
      CRM_Core_Error::statusBounce('Error loading message templates: ' . $e->getMessage());
    }

    // Message template select.
    $this->add(
      'select',
      'message_template_id',
      E::ts('Message Template'),
      ['' => E::ts('- Select -')] + $messageTemplates,
      TRUE,
      ['class' => 'crm-select2 huge']
    );

    // From email field.
    $this->add(
      'text',
      'from_email',
      E::ts('From Email (optional)'),
      ['size' => 50, 'placeholder' => 'e.g., "Your Name" <email@example.com>']
    );

    $this->addButtons([
      [
        'type' => 'next',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ],
    ]);

    parent::buildQuickForm();
  }

  /**
   * Set default values.
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleAction->action_params);

    if (!empty($data['message_template_id'])) {
      $defaultValues['message_template_id'] = $data['message_template_id'];
    }

    if (!empty($data['from_email'])) {
      $defaultValues['from_email'] = $data['from_email'];
    }

    return $defaultValues;
  }

  /**
   * Process the form submission.
   */
  public function postProcess() {
    $data = [
      'message_template_id' => $this->_submitValues['message_template_id'],
      'from_email' => $this->_submitValues['from_email'] ?? '',
    ];

    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();

    parent::postProcess();
  }

}
