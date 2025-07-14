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

  // Send email to delegated contributor when contribution is set to completed
  // No need to send one to the contributor as they will recieve the normal one via civirules anyway
  if($objectName === 'Contribution' && $op === 'edit') {

    // Has it been set to completed?
    if($params['contribution_status_id'] == 1) {
      $contribution = \Civi\Api4\Contribution::get(FALSE)
        ->addSelect('*', 'custom.*')
        ->addWhere('id', '=', $id)
        ->setLimit(25)
        ->execute()
        ->first();

      // Has it been delegated and was it originally pending?
      if($contribution["Kin_Contributions.Delegated_Contributor"] && $contribution["contribution_status_id"] != 1) {

        $delegate_id = $contribution["Kin_Contributions.Delegated_Contributor"];
        $contribution_id = $params['id'];

        $group = \Civi\Api4\Contact::get()
          ->addSelect('custom.*','*','email_primary.email')
          ->addWhere('id', '=', $contribution["Kin_Contributions.Household"])
          ->execute()
          ->first();

        $onBehalfOf = \Civi\Api4\Contact::get()
          ->addSelect('custom.*','*','email_primary.email')
          ->addWhere('id', '=', $contribution["contact_id"])
          ->execute()
          ->first();

        $delegate = \Civi\Api4\Contact::get()
          ->addSelect('custom.*','*','email_primary.email')
          ->addWhere('id', '=', $delegate_id)
          ->execute()
          ->first();

        // Send email to delegate confirming contribution
        $delivery = \CRM_Core_BAO_MessageTemplate::sendTemplate([
          'workflow' => 'onbehalfof_delegate_completed',
          'tokenContext' => [
            'contactId' => $delegate_id,
            'contributionId' => $contribution_id,
          ],
          'tplParams' => [
            'group' => $group['display_name'],
            'onBehalfOf' => $onBehalfOf,
          ],
          'toEmail' => $delegate['email_primary.email'],
            'from' => '"Kin" <admin@kin.coop>',
          'bcc' => 'info@kin.coop',
        ]);
      }
    }
  }

  if ($objectName === 'Contribution' && $op === 'create') {
    // Check if custom override email field was submitted

    //Civi::log()->debug('New email: ' . $_POST['email-5']);
      // This is code for on behalf of to submit a contribution on behalf of someone else
      // It uses the contribution page/form 8
    if(!empty($params['contribution_page_id']) && $params['contribution_page_id'] == 8){
      if (!empty($_POST['email-5'])) {
        $overrideEmail = trim($_POST['email-5']);

        // Get contact id from email
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
 * Implements hook_civicrm_post().
 */
// Send out the receipt email manually for recurring payments (form id 7) because by default receipt emails
// don't get sent for pending transactions using a payment processor. We are only using a payment processor
// for recurring transactions. The alternative would be to update the payment processor class that just extends the real one
// and then implements the function isSendReceiptForPending and returns TRUE instead of FALSE
// (see https://github.com/civicrm/civicrm-core/blob/6bdf4c122348e57b708ff31d76fc45dad21ae1f8/CRM/Core/Payment.php#L1955 and
// https://chat.civicrm.org/civicrm/pl/yj64iwrh6fyrzgcdw8wziabm4a)
function kincoop_civicrm_postCommit($op, $objectName, $objectId, &$objectRef) {
  if ($objectName === 'Contribution' && $op === 'create') {
    $contribution = $objectRef;

    // Check if it's from a contribution page
    if (!empty($contribution->contribution_page_id) && $contribution->contribution_page_id == 7) {

      // Check if it's pending
      if ((int) $contribution->contribution_status_id === 2) {
        try {
          civicrm_api3('Contribution', 'sendconfirmation',  ['id' => $contribution->id]);
        }
        catch (CiviCRM_API3_Exception $e) {
          \Civi::log()->error('Failed to send receipt for pending contribution ID ' . $contribution->id . ': ' . $e->getMessage());
        }
      }
    }
  }
}


// Re-direct all emails to me on dev sites
function kincoop_civicrm_alterMailParams(&$params, $context) {

  if(str_contains( $_SERVER['HTTP_HOST'], 'dev')) {
    $params['toEmail'] = 'ben@benmango.co.uk';
    $params['cc'] = 'ben@benmango.co.uk';
    $params['bcc'] = 'ben@benmango.co.uk';
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
        } elseif ($form->_id === 4 || $form->_id === 8) {
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
        }

        // on behalf of form
        elseif ($form->_id === 8) {
            //check contact exists from email

            if(empty($fields['custom_25'])) {
              $errors['custom_25'] = ts('This field is required.');
            }

            if (!empty($fields['email-5'])) {
              $on_behalf_of = $fields['email-5'];

              // Check contact exists
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

              //check that the member is in the group selected
              try {
                $relationships = \Civi\Api4\Relationship::get(FALSE)
                  ->addSelect('*')
                  ->addWhere('contact_id_a', '=', $contact_id)
                  ->addWhere('contact_id_b', '=', $fields['custom_25'])
                  ->setLimit(1)
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


