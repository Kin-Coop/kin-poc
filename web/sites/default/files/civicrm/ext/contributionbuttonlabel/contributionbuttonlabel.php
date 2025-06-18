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
        _contributionbuttonlabel_getButtonLabel($elements, $form->getVar('_id'), $values[$formName][1]);
        break;
      }
    }
  }

  if ('CRM_Contribute_Form_ContributionPage_Settings' == $formName
    && !($form->getVar('_action') & CRM_Core_Action::DELETE)
  ) {
    if ($form->getVar('_id') && empty($_REQUEST['snippet'])) {
      return;
    }
    $form->add('text', 'main_page_button_label', ts('Main Page Button Label'));
    $form->add('text', 'confirm_page_button_label', ts('Confirm Page Button Label'));
    CRM_Core_Region::instance('page-body')->add([
      'template' => 'CRM/ContributionButtonLabel/Label.tpl',
    ]);

    if ($form->getVar('_id')) {
      $contributionPage = civicrm_api4('ContributionPage', 'get', [
        'select' => [
          'contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_main',
          'contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_confirm',
        ],
        'where' => [
          ['id', '=', $form->getVar('_id')],
        ],
        'checkPermissions' => FALSE,
      ], 0);

      $defaults = [
        'main_page_button_label' => $contributionPage['contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_main'],
        'confirm_page_button_label' => $contributionPage['contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_confirm'],
      ];

      $form->setDefaults($defaults);
    }
  }
}

/**
 * Implements hook_civicrm_post().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
 */
function contributionbuttonlabel_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if ($objectName == 'ContributionPage' && in_array($op, ['edit', 'create'])
    && (isset($_POST['main_page_button_label']) || isset($_POST['confirm_page_button_label']))
    && empty(Civi::$statics['contributionbuttonlabel_civicrm_post']['called'])
  ) {
    try {
      Civi::$statics['contributionbuttonlabel_civicrm_post']['called'] = TRUE;
      $result = civicrm_api3('CustomField', 'get', [
        'return' => ['name'],
        'custom_group_id' => 'contributionbuttonlabel_cg_buttonlabel',
      ])['values'];
      $result = array_column($result, 'id', 'name');
      civicrm_api3('ContributionPage', 'create', [
        'id' => $objectId,
        "custom_{$result['contributionbuttonlabel_cf_main']}" => $_POST['main_page_button_label'] ?? 'null',
        "custom_{$result['contributionbuttonlabel_cf_confirm']}" => $_POST['confirm_page_button_label'] ?? 'null',
      ]);
    }
    catch (Exception $e) {
    }
  }
}

/**
 * Get button label.
 *
 * @param object $element
 * @param int $contributionPageId
 * @param string $pageName
 *
 */
function _contributionbuttonlabel_getButtonLabel(&$element, $contributionPageId, $pageName) {
  try {
    $labelName = civicrm_api4('ContributionPage', 'get', [
      'select' => [
        "contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_{$pageName}",
      ],
      'where' => [
        ['id', '=', $contributionPageId],
      ],
      'checkPermissions' => FALSE,
    ], 0)["contributionbuttonlabel_cg_buttonlabel.contributionbuttonlabel_cf_{$pageName}"] ?? '';

    if (empty($labelName)) {
      return;
    }
    if (!property_exists($element, '_content')) {
      $element->_attributes['value'] = $labelName;
    }
    else {
      $element->_content = substr_replace($element->_content, $labelName, (strpos($element->_content, '/i> ') + 4));
    }
  }
  catch (Exception $e) {
  }
}
