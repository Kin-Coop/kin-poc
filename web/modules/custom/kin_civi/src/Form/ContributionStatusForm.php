<?php

namespace Drupal\kin_civi\Form;

\Drupal::service('civicrm')->initialize();

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Civi\Api4\Contribution;
use Civi\Api4\UFMatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\kin_civi\Service\kin_civi_emails;

/**
 * Defines a form to update CiviCRM contribution status.
 */
class ContributionStatusForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'contribution_status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?Request $request = NULL)
  {

    //check user is logged in
    if (\Drupal::currentUser()->isAuthenticated() == FALSE) {
      throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
    }

    $contribution_id = \Drupal::routeMatch()->getParameter('contribution_id');
    $contribution = kin_civi_get_contribution($contribution_id);
    $contribution_amount = $contribution['total_amount'] * -1;
    $member_cid = kin_civi_get_contrib_contact($contribution_id);
    $member_name = kin_civi_get_name($member_cid)['display_name'];
    $group_id = \Drupal::routeMatch()->getParameter('group_id');
    //$form_state->setValue('group_id', $group_id);
    $group = \Drupal::service('kin_civi.utils')->kin_civi_check_group($group_id);

    if ($contribution == false) {
      $form = [
        '#markup' => $this->t('The contribution was not found. Please check and try again.'),
      ];
    } else {
      $form['contribution_id'] = [
        '#type' => 'hidden',
        '#value' => $contribution_id,
      ];

      $form['intro'] = [
        '#markup' => $this->t('
           <p>Please approve or disapprove this request.</p>
           <p>If the request type is not a personal request, then it requires the agreement of the whole group.</p>
           <p>If the request type is a "Collective purchase" you also need to confirm that
           </p>
           <ul>
           <li>The money comes from leftover group funds</li>
           <li>There was no prior agreement, contract or expectation of payment</li>
           <li>This is not a payment for goods or services</li>
           </ul>
          '),
      ];

      $form['group'] = [
        '#type' => 'item',
        '#title' => $this->t('Group'),
        '#markup' => '<strong>' . $group['display_name'] . '</strong>',
      ];

      $form['requested_by'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Requested by'),
        '#default_value' => $member_name,
        '#disabled' => TRUE,
      ];

      $form['reward_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Type of Reward'),
        '#options' => $this->getRewardTypeOptions(),
        '#required' => TRUE,
        '#default_value' => $contribution['Group_Reward.Reward_Type'],
      ];

      $form['group_agreement'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Has the whole group agreed to this reward?'),
        '#required' => TRUE,
        '#states' => [
          'visible' => [
            ':input[name="reward_type"]' => ['!value' => 'Personal request'],
          ],
          'required' => [
            ':input[name="reward_type"]' => ['!value' => 'Personal request'],
          ],
        ],
      ];

      $form['not_goods_services'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('This is not a payment for goods or services.'),
        '#states' => [
          'visible' => [
            ':input[name="reward_type"]' => ['value' => 'Collective purchase'],
          ],
          'required' => [
            ':input[name="reward_type"]' => ['value' => 'Collective purchase'],
          ],
        ],
      ];

      $form['no_prior_agreement'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('There was no prior agreement, contract or expectation of payment.'),
        '#states' => [
          'visible' => [
            ':input[name="reward_type"]' => ['value' => 'Collective purchase'],
          ],
          'required' => [
            ':input[name="reward_type"]' => ['value' => 'Collective purchase'],
          ],
        ],
      ];

      $form['leftover_funds'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('The money comes from leftover group funds.'),
        '#states' => [
          'visible' => [
            ':input[name="reward_type"]' => ['value' => 'Collective purchase'],
          ],
          'required' => [
            ':input[name="reward_type"]' => ['value' => 'Collective purchase'],
          ],
        ],
      ];

      $form['amount'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Amount requested'),
        '#default_value' => '£' . number_format($contribution_amount, 2, '.', ','),
        '#disabled' => TRUE,
      ];

      $form['receive_date'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Date'),
        '#default_value' => date('d-m-Y H:i', strtotime($contribution['receive_date'])),
        '#disabled' => TRUE,
      ];

      $form['gift_note'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Request Note'),
        '#default_value' => $contribution['Kin_Contributions.Note'], // This is the preset value
      ];

      $form['status'] = [
        '#type' => 'select',
        '#title' => $this->t('Approve Request'),
        '#options' => [
          'yes' => $this->t('Yes'),
          'no' => $this->t('No'),
        ],
        '#required' => TRUE,
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    $contribution_id = $form_state->getValue('contribution_id');
    //$status = $form_state->getValue('status') === 'Yes' ? True : False;
    $status = $form_state->getValue('status');
    $amount = $form_state->getValue('amount');

    //return contribution record from id
    $contributions = Contribution::get(FALSE)
      ->addSelect('Kin_Contributions.Household')
      ->addWhere('id', '=', $contribution_id)
      ->addWhere('financial_type_id', '=', 5)
      ->execute();

    //is there a gift with this id?
    if ($contributions->rowCount > 0) {
      //Get group from contribution record
      $group = $contributions[0]['Kin_Contributions.Household'];
      $group_name = kin_civi_get_name($group);

      //Get current user - should be only the admin accessing this form
      $uid = \Drupal::currentUser()->id();

      // Get contact id of the admin user
      $admin_cid = kin_civi_get_cid($uid);

      // Get contact id, name and email of the member making the gift request
      $member_cid = kin_civi_get_contrib_contact($contribution_id);
      $name = kin_civi_get_name($member_cid)['display_name'];
      $email = kin_civi_get_name($member_cid)['email_primary.email'];
      $agreed = is_null($form_state->getValue('group_agreement')) ? 0 : $form_state->getValue('group_agreement');
      $not_goods_services = is_null($form_state->getValue('not_goods_services')) ? 0 : $form_state->getValue('not_goods_services');
      $no_prior_agreement = is_null($form_state->getValue('no_prior_agreement')) ? 0 : $form_state->getValue('no_prior_agreement');
      $leftover_funds = is_null($form_state->getValue('leftover_funds')) ? 0 : $form_state->getValue('leftover_funds');
      $reward_type = $form_state->getValue('reward_type');

      // Only update if the person accessing this form is an admin of the group where the gift is being requested
      if (kin_civi_is_admin($admin_cid, $group)) {
        try {
          // Please note $status is Case Sensitive!!
          Contribution::update(FALSE)
            ->addValue('Kin_Contributions.Approved', $status)
            ->addValue('Group_Reward.Reward_Type', $reward_type)
            ->addValue('Kin_Contributions.Note', $form_state->getValue('gift_note'))
            ->addValue('Group_Reward.All_members_agreed', $agreed)
            ->addValue('Group_Reward.This_is_not_a_payment_for_goods_or_services', $not_goods_services)
            ->addValue('Group_Reward.There_was_no_prior_agreement_contract_or_expectation_of_payment', $no_prior_agreement)
            ->addValue('Group_Reward.The_money_comes_from_leftover_group_funds', $leftover_funds)
            ->addWhere('id', '=', $contribution_id)
            ->execute();

          \Drupal::messenger()->addMessage($this->t('Contribution approved status updated to %status.', [
            '%status' => $status,
          ]));

          // Send email to admins to say money request has been approved
          // Only need to send it if there is more than one admin for the group
          $admins = \Civi\Api4\Relationship::get(FALSE)
            ->addSelect('contact_id_a.first_name', 'email.email', 'contact_id_a')
            ->addJoin('Email AS email', 'INNER', ['contact_id_a.email_primary', '=', 'email.id'])
            ->addWhere('contact_id_b', '=', $group)
            ->addWhere('relationship_type_id', '=', 11)
            ->execute();

          // Convert to array (API4 returns an iterator)
          $adminsArray = $admins->getArrayCopy();
          $count = count($adminsArray);

          // Send update email to group admins
          // Only send if more than one admin found because they already know about the approval!
          if ($count <= 1) {
            \Civi::log()->info("kin_civi: Skipping email send for group {$group} — only {$count} admin(s) found.");
          } else {
            $sent = 0;

            foreach ($admins as $admin) {
              if ($admin['contact_id_a'] != $admin_cid) {
                try {
                  $params = [
                    'id' => 132, // Message Template ID
                    'contact_id' => $member_cid, // Recipient’s contact ID
                    'from' => '"Kin Cooperative" <members@kin.coop>',
                    // Optional: specify email override if you want to force a specific address
                    'to_email' => $admin['email.email'],
                    'tplParams' => [
                      'admin_name' => $admin['contact_id_a.first_name'],
                      'group' => $group_name["display_name"],
                      'amount' => $amount,
                      'status' => $status == 'yes' ? 'approved' : 'declined',
                    ],
                  ];

                  civicrm_api3('MessageTemplate', 'send', $params);
                  $sent++;
                } catch (Exception $e) {
                  \Civi::log()->error('Failed to send to contact ' . $admin['contact_id_a'] . ': ' . $e->getMessage());
                }
              }
            }
            \Civi::log()->info("kin_civi: Sent $sent emails for group $group.");
          }

          // If request denied send email to member informing them and asking them to get in touch
          if ($status == 'no') {
            $result = civicrm_api3('MessageTemplate', 'send', [
              'id' => 125, // The ID of your message template
              'contact_id' => $member_cid, // Recipient’s contact ID
              'from' => '"Kin" <members@kin.coop>',
              'to_email' => $email,
              'tplParams' => [
                'group' => $group_name["display_name"],
                'amount' => $amount,
                'contribution_id' => $contribution_id,
              ],
            ]);

            // If request approved send notification email to admin to transfer money
          } elseif ($status == 'yes') {

            $result = civicrm_api3('MessageTemplate', 'send', [
              'id' => 124, // The ID of your message template
              'contact_id' => $member_cid, // Recipient’s contact ID
              'from' => '"Kin Cooperative" <members@kin.coop>',
              'to_email' => 'members@kin.coop',
              'tplParams' => [
                'group' => $group_name["display_name"],
                'amount' => $amount,
                'contribution_id' => $contribution_id,
              ],
            ]);

            /*
            $relationships = \Civi\Api4\Relationship::get(FALSE)
              ->addJoin('Contact AS contact', 'INNER', ['contact_id_a', '=', 'contact.id'])
              ->addJoin('Email AS email', 'INNER', ['contact.email_primary', '=', 'email.id'])
              ->addSelect('id', 'contact_id_a', 'contact.id', 'contact.email_primary', 'email.email', 'contact.first_name', 'email.id')
              ->addWhere('contact_id_b', '=', 477)
              ->addWhere('relationship_type_id', '=', 11)
              ->execute();
            foreach ($relationships as $relationship) {
              // do something
            }
            */

            //\Drupal::service('kin_civi.kin_civi_service')->kin_civi_send_email($cid, $params);
          }

        } catch (\Exception $e) {
          \Drupal::messenger()->addError($this->t('Failed to update contribution: %message', ['%message' => $e->getMessage()]));
        }
      } else {
        \Drupal::messenger()->addWarning($this->t('You need to be an admin of this group in order to approve a gift request.'));
      }
    } else {
      \Drupal::messenger()->addWarning($this->t('No gift contribution found.'));
    }
  }

  /**
   * Get reward type options from CiviCRM custom field.
   *
   * @return array
   *   An associative array of options (value => label).
   */
  protected function getRewardTypeOptions() {

    try {
      // Replace 'custom_123' with your actual custom field ID
      $result = \Civi\Api4\Contribution::getFields(FALSE)
                                       ->addWhere('name', '=', 'Group_Reward.Reward_Type') // Use the actual custom field ID
                                       ->setLoadOptions(TRUE)
                                       ->addSelect('options')
                                       ->execute()
                                       ->first();

      $options = [];
      if (!empty($result['options'])) {
        foreach ($result['options'] as $option) {
          $options[$option] = $option;
        }
      }

      return $options;

    } catch (\Exception $e) {
      \Drupal::logger('kin_civi')->error('Error loading reward type options: @message', [
        '@message' => $e->getMessage(),
      ]);
      return [];
    }
  }

}

function kin_civi_get_contribution($contribution_id)
{
  $contributions = \Civi\Api4\Contribution::get(FALSE)
    ->addSelect('Kin_Contributions.Note', 'id', 'receive_date', 'total_amount', 'Kin_Contributions.Approved', 'Group_Reward.Reward_Type')
    ->addWhere('id', '=', $contribution_id)
    ->addWhere('financial_type_id', '=', 5)
    ->execute();

  if ($contributions->rowCount > 0) {
    return (array)$contributions->first();
  } else {
    return FALSE;
  }
}

function kin_civi_get_cid($uid)
{
  try {
    // Query CiviCRM APIv4 to get the contact ID for the Drupal user.
    $result = UFMatch::get(FALSE)
      ->addWhere('uf_id', '=', $uid)
      ->addSelect('contact_id')
      ->execute();

    if ($result) {
      return (int)$result->first()['contact_id'];
    } else {
      return FALSE;
    }
  } catch (APIException $e) {
    \Drupal::logger('mymodule')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
  }
}

function kin_civi_get_name($cid)
{
  try {
    // Query CiviCRM APIv4 to get the name from the id.
    $contacts = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('display_name', 'email_primary.email')
      ->addWhere('id', '=', $cid)
      ->execute();

    if ($contacts) {
      return (array)$contacts->first();
    } else {
      return FALSE;
    }
  } catch (APIException $e) {
    \Drupal::logger('kin_civi')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
  }
}

function kin_civi_is_admin($rida, $ridb)
{
  $relationships = \Civi\Api4\Relationship::get(FALSE)
    ->addSelect('relationship_type_id')
    ->addWhere('contact_id_a', '=', $rida)
    ->addWhere('contact_id_b', '=', $ridb)
    ->setLimit(1)
    ->execute();

  if ($relationships) {
    if ($relationships->first()['relationship_type_id'] == 11) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
}

function kin_civi_get_contrib_contact($contribution_id)
{
  $contributions = \Civi\Api4\Contribution::get(FALSE)
    ->addSelect('contact_id')
    ->addWhere('id', '=', $contribution_id)
    ->execute();
  if ($contributions) {
    return $contributions->first()['contact_id'];
  }
}
