<?php

require_once 'kincoop.civix.php';

use CRM_Kincoop_ExtensionUtil as E;

const GIFT_FT_NAME = 'Gift';

const REVERSIBLE_AMOUNT_KEYS = array(
  'line_total' => TRUE,
  'line_total_inclusive' => TRUE,
  'net_amount' => TRUE,
  'total_amount' => TRUE,
  'unit_price' => TRUE,
);

/**
 * Implements hook_civicrm_pre
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pre
 */
function kincoop_civicrm_pre($op, $objectName, $id, &$params) {
  if(isset($params['financial_type_id'])) {
    if (isNewContribution($objectName, $op) && isAssociatedWithGift($params)) {
      reverseSignsOnAmounts($params);
    }
  }

  if ($objectName === 'Contribution' && $op === 'create') {
    // Check if custom override email field was submitted

    //Civi::log()->debug('New email: ' . $_POST['email-5']);
    if(!empty($params['contribution_page_id']) && $params['contribution_page_id'] == 8){
      if (!empty($_POST['email-5'])) {
        $overrideEmail = trim($_POST['email-5']);

        try {
            $contacts = \Civi\Api4\Contact::get(FALSE)
                ->addSelect('id')
                ->addWhere('email_primary.email', '=', $overrideEmail)
                ->setLimit(1)
                ->execute();

          if (!empty($contacts[0]['id'])) {
            $new_contact_id = $contacts[0]['id'];
            if ($params['contact_id'] != $new_contact_id) {
              $params['contact_id'] = $new_contact_id;
              \Civi::log()->info("Contact ID overridden based on email: $overrideEmail", [
                'new_contact_id' => $params['contact_id'],
              ]);
            }
          } else {
            \Civi::log()->warning("No contact found for override email: $overrideEmail");
          }
        } catch (CiviCRM_API4_Exception $e) {
          \Civi::log()->error("API error during email lookup: " . $e->getMessage());
        }
      }
    }
  }
}

/**
 * Implements hook_civirules_alter_trigger_data
 *
 * @link https://docs.civicrm.org/civirules/en/latest/hooks/hook_civirules_alter_trigger_data/
 */
function kincoop_civirules_alter_trigger_data(&$triggerData) {
  $contributionData = $triggerData->getEntityData('Contribution');
  if (isset($contributionData) && isAssociatedWithGift($contributionData)) {
    reassignContactIdToHousehold($triggerData, $contributionData);
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function kincoop_civicrm_config(&$config): void {
  _kincoop_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function kincoop_civicrm_install(): void {
  _kincoop_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function kincoop_civicrm_enable(): void {
  _kincoop_civix_civicrm_enable();
}

function isNewContribution($objectName, $op): bool {
  return $objectName == 'Contribution' && $op == 'create';
}

function isAssociatedWithGift($contributionData): bool {
  $financialTypeId = getFromObjectOrArray($contributionData, 'financial_type_id');
  if (!isset($financialTypeId)) {
    return FALSE;
  }
  $ftName = CRM_Core_DAO::singleValueQuery('SELECT name FROM civicrm_financial_type WHERE id = %1',
    array(1 => array($financialTypeId, 'Integer')));
  return $ftName == GIFT_FT_NAME;
}

function reverseSignsOnAmounts(&$params): void {
  array_walk_recursive($params, 'reverseSignIfAppropriate');
}

function reverseSignIfAppropriate(&$item, $key): void {
  if (!isReversibleAmount($key)) {
    return;
  }
  if (is_numeric($item)) {
    $item = -$item;
  } elseif (is_string($item)) {
    $item = '-' . $item;
  }
}

function isReversibleAmount($key): bool {
  return array_key_exists($key, REVERSIBLE_AMOUNT_KEYS);
}

function reassignContactIdToHousehold($triggerData, $contributionData): void {
  $householdContactId = getHouseholdContactId($contributionData);
  if (!isset($householdContactId)) {
    Civi::log()->debug('[' . __FUNCTION__ . '] ' .
      'Warning: no household found for this contribution [#' . $contributionData->id . '].' .
      'This may lead to an unexpected action.');
  }
  $triggerData->setContactId($householdContactId);
}

function getHouseholdContactId($contributionData): ?int {
  $contributionId = getFromObjectOrArray($contributionData, 'id');
  if (!isset($contributionId)) {
    Civi::log()->debug('$contributionId not present');
    return null;
  }
  //Civi::log()->debug('[' . __FUNCTION__ . '] $contributionId: ' . $contributionId);

  $contributionCustomGroupTableName = CRM_Core_DAO::singleValueQuery(
    'SELECT table_name FROM civicrm_custom_group WHERE extends = \'Contribution\'');

  $householdCustomFieldId = CRM_Core_DAO::singleValueQuery(
    'SELECT id FROM civicrm_custom_field WHERE name = \'Household\'');
  $householdContactIdColumnName = 'household_' . $householdCustomFieldId;

  return CRM_Core_DAO::singleValueQuery(
    'SELECT ' . $householdContactIdColumnName .
    ' FROM ' . $contributionCustomGroupTableName .
    ' WHERE entity_id = %1',
    array(1 => array($contributionId, 'Integer')));
}

function getFromObjectOrArray($objectOrArray, $key) {
  $array = (array) $objectOrArray;
    return $array[$key] ?? null;
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * Set a default value for an event price set field.
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function kincoop_civicrm_buildForm($formName, $form) {
    //Civi::log()->debug('Contents of $formName: ' . print_r($formName, TRUE));
    //Civi::log()->debug('Contents of $formName: ' . print_r($form, TRUE));
    if ($formName === 'CRM_Contribute_Form_Contribution_Main') {
        if ($form->_id === 1) {
          if($form->getAction() == CRM_Core_Action::ADD) {
             if (isset($_GET['groupid']) && $_GET['me']) {
                    $ref = $_GET['me'] . '-' . date('mdi');
                    $defaults['custom_25'] = $_GET['groupid'];
                    $defaults['custom_61'] = $ref;
                    //Civi::log()->debug('Contents of $defaults: ' . print_r($form->_fields, TRUE));
             }
            $defaults['custom_66'] = 1;
            $form->setDefaults($defaults);
            $form->addRule('custom_25', ts('This field is required.'), 'required');
          }
        } elseif ($form->_id === 3) {
          if($form->getAction() == CRM_Core_Action::ADD) {
            if (isset($_GET['groupid']) && $_GET['me']) {
                    $defaults['custom_25'] = $_GET['groupid'];
                    //$defaults['custom_62'] = 'Gift';
                    $form->setDefaults($defaults);
                }
            $form->addRule('custom_25', ts('This field is required.'), 'required');
            }
        } elseif ($form->_id === 4) {
            //Civi::log()->debug('Contents of $formName: ' . print_r($_GET, TRUE));
          if($form->getAction() == CRM_Core_Action::ADD) {
            if (isset($_GET['groupid']) && $_GET['me']) {
                $cid = CRM_Core_Session::singleton()->getLoggedInContactID();
                $cid = $cid ? $cid : 'K';
                $ref = $cid . '-' . date('mdi');
                $defaults['custom_25'] = $_GET['groupid'];
                $defaults['custom_61'] = $ref;
                $form->setDefaults($defaults);
                //if (isset($form['custom_25'])) {
                //  $form->addRule('custom_25', ts('This field is required.'), 'required');
                //}
                    //Civi::log()->debug('Contents of $defaults: ' . print_r($form->_fields, TRUE));
                }
            }
        } elseif ($form->_id === 7) {
          if($form->getAction() == CRM_Core_Action::ADD) {
            if (isset($_GET['groupid']) && $_GET['me']) {
              $ref = $_GET['me'] . '-' . date('mdi');
              $defaults['custom_25'] = $_GET['groupid'];
              $defaults['custom_61'] = $ref;
              $defaults['frequency_unit'] = "month";
              //Civi::log()->debug('Contents of $defaults: ' . print_r($form->_fields, TRUE));
            }
            $defaults['custom_66'] = 1;
            $form->setDefaults($defaults);
            $form->addRule('custom_25', ts('This field is required.'), 'required');
          }
        }
    }
}

// Check group/household is filled in
function kincoop_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
    if ($formName === 'CRM_Contribute_Form_Contribution_Main') {
        if ($form->_id === 1 || $form->_id === 3 || $form->_id ===4) {
            //Civi::log()->debug('Contents of $defaults: ' . print_r($fields, TRUE));
            if(empty($fields['custom_25'])) {
                $errors['custom_25'] = ts('This field is required.');
            }
        } elseif ($form->_id === 8) {
            //check contact exists from email
            //Civi::log()->debug('Contents of $fields: ' . print_r($fields, TRUE));

            if(empty($fields['custom_25'])) {
              $errors['custom_25'] = ts('This field is required.');
            }

            if (!empty($fields['email-5'])) {
              $on_behalf_of = $fields['email-5'];

              try {
                $contacts = \Civi\Api4\Contact::get(FALSE)
                  ->addSelect('id')
                  ->addWhere('email_primary.email', '=', $on_behalf_of)
                  ->setLimit(1)
                  ->execute();

                  if (empty($contacts[0])) {
                      $errors['email-5'] = ts('No member found with this email address. Please check and try again.');
                  } else {
                    $contact_id = $contacts[0]["id"];
                  }
              }
              catch (CiviCRM_API4_Exception $e) {
                  \Civi::log()->error("API error during email lookup: " . $e->getMessage());
              }

              //check a relationship exists for the contact and the group
              try {
                $relationships = \Civi\Api4\Relationship::get(TRUE)
                  ->addSelect('*')
                  ->addWhere('contact_id_a', '=', $contact_id)
                  ->addWhere('contact_id_b', '=', $fields['custom_25'])
                  ->setLimit(25)
                  ->execute();

                if (empty($relationships[0])) {
                  $errors['custom_25'] = ts('The email given does not match any members of this group. Please check and try again.');
                }
              }
              catch (CiviCRM_API4_Exception $e) {
                \Civi::log()->error("API error during email lookup: " . $e->getMessage());
              }
          }
        }
    }
    return;
}


/*
function civicrm_custom_access_civicrm_buildForm($formName, &$form) {
    global $user;
    drupal_access_denied();
    drupal_exit();

    $grant_access = 0;
    if(\Drupal::currentUser()->isAnonymous()) {
        if($formName == 'CRM_Contribute_Form_Contribution_Main' && $form->_id == 4 && !$grant_access) {
            drupal_access_denied();
            drupal_exit();
        }
    }
}
*/
