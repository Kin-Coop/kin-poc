<?php

require_once 'contributionbuttonlabel.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function contributionbuttonlabel_civicrm_config(&$config) {
  _contributionbuttonlabel_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function contributionbuttonlabel_civicrm_install() {
  _contributionbuttonlabel_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function contributionbuttonlabel_civicrm_enable() {
  _contributionbuttonlabel_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function contributionbuttonlabel_civicrm_managed(&$entities) {
  $entities[] = [
    'module' => 'contributionbuttonlabel',
    'name' => 'contributionbuttonlabel_cg_extend_object',
    'entity' => 'OptionValue',
    'update' => 'never',
    'params' => [
      'label' => ts('Contribution page'),
      'name' => 'civicrm_contribution_page',
      'value' => 'ContributionPage',
      'option_group_id' => 'cg_extend_objects',
      'options' => ['match' => ['option_group_id', 'name']],
      'is_active' => 1,
      'version' => 3,
    ],
  ];

  $entities[] = [
    'module' => 'contributionbuttonlabel',
    'name' => 'contributionbuttonlabel_cg_buttonlabel',
    'entity' => 'CustomGroup',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'contributionbuttonlabel_cg_buttonlabel',
      'title' => ts('Contribution Button Labels'),
      'extends' => 'ContributionPage',
      'style' => 'Inline',
      'is_active' => TRUE,
      'is_public' => FALSE,
      'is_reserved' => 1,
      'options' => ['match' => ['name']],
    ],
  ];

  $entities[] = [
    'module' => 'contributionbuttonlabel',
    'name' => 'contributionbuttonlabel_cf_main',
    'entity' => 'CustomField',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'contributionbuttonlabel_cf_main',
      'label' => ts('Main Page Button Label'),
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_active' => TRUE,
      'text_length' => 255,
      'weight' => 3,
      'option_type' => 0,
      'custom_group_id' => 'contributionbuttonlabel_cg_buttonlabel',
      'options' => ['match' => ['name', 'custom_group_id']],
    ],
  ];

  $entities[] = [
    'module' => 'contributionbuttonlabel',
    'name' => 'contributionbuttonlabel_cf_confirm',
    'entity' => 'CustomField',
    'update' => 'never',
    'params' => [
      'version' => 3,
      'name' => 'contributionbuttonlabel_cf_confirm',
      'label' => ts('Confirm Page Button Label'),
      'data_type' => 'String',
      'html_type' => 'Text',
      'is_active' => TRUE,
      'text_length' => 255,
      'weight' => 3,
      'option_type' => 0,
      'custom_group_id' => 'contributionbuttonlabel_cg_buttonlabel',
      'options' => ['match' => ['name', 'custom_group_id']],
    ],
  ];
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_buildForm
 */
function contributionbuttonlabel_civicrm_buildForm($formName, &$form) {
  if (in_array($formName, ['CRM_Contribute_Form_Contribution_Confirm', 'CRM_Contribute_Form_Contribution_Main'])) {
    $buttons = &$form->getElement('buttons');

    $values = [
      'CRM_Contribute_Form_Contribution_Confirm' => ['_qf_Confirm_next', 'confirm'],
      'CRM_Contribute_Form_Contribution_Main' => ['_qf_Main_upload', 'main'],
    ];
    foreach ($buttons->_elements as &$elements) {
      if ($elements->_attributes['name'] == $values[$formName][0]) {
        _contributionbuttonlabel_setButtonLabel($elements, $form->getVar('_id'), $values[$formName][1]);
        break;
      }
    }
  }
}

/**
 * Set a custom button label on a contribution page element.
 *
 * @param object $element
 * @param int $contributionPageId
 * @param string $pageName
 */
function _contributionbuttonlabel_setButtonLabel(&$element, $contributionPageId, $pageName) {
  $labelName = _contributionbuttonlabel_getButtonLabel($contributionPageId, $pageName);

  if (empty($labelName)) {
    return;
  }

  if (!property_exists($element, '_content')) {
    $element->_attributes['value'] = $labelName;
  }
  else {
    $element->_content = substr_replace($element->_content, $labelName, (strpos($element->_content, '/i> ') + 4));
  }
  if ($pageName == 'confirm') {
    _contributionbuttonlabel_civicrm_setPaymentProcessorText($labelName);
  }

}

/**
 * Replace default payment processor button help text with a custom label.
 *
 * @param string $newLabel
 */
function _contributionbuttonlabel_civicrm_setPaymentProcessorText($newLabel) {
  $smarty = CRM_Core_Smarty::singleton();
  // FIXME: Need to handle via the UI, may be Option group?
  $buttonText = [
    '<strong>Make Contribution</strong>',
    '<strong>Make Payment</strong>',
    '<strong>Continue</strong>',
  ];
  $smarty->assign('button', $newLabel);
  foreach (['continueText', 'confirmText'] as $variableType) {
    $text = $smarty->getTemplateVars($variableType);
    if (empty($text)) {
      continue;
    }
    $text = str_replace($buttonText, "<strong>{$newLabel}</strong>", $text);
    $smarty->assign($variableType, $text);
  }

}

/**
 * Get button label.
 *
 * @param int $contributionPageId
 * @param string $pageName
 *
 * @return string|null
 */
function _contributionbuttonlabel_getButtonLabel($contributionPageId, $pageName): ?string {
  return \Civi\Api4\ContributionPage::get(FALSE)
    ->addSelect("contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_{$pageName}")
    ->addWhere('id', '=', $contributionPageId)
    ->execute()
    ->first()["contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_{$pageName}"] ?? '';
}
