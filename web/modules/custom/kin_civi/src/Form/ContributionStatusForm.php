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
class ContributionStatusForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'contribution_status_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, ?Request $request = NULL) {

        //check user is logged in
        if(\Drupal::currentUser()->isAuthenticated() == FALSE) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }

        $contribution_id = \Drupal::routeMatch()->getParameter('contribution_id');
        $contribution = kin_civi_get_contribution($contribution_id);
        $member_cid = kin_civi_get_contrib_contact($contribution_id);
        $member_name = kin_civi_get_name($member_cid)['display_name'];
        
        If($contribution == false) {
          $form = [
            '#markup' => $this->t('The contribution was not found. Please check and try again.'),
          ];
        } else {
          $form['contribution_id'] = [
            '#type' => 'hidden',
            '#value' => $contribution_id,
          ];
          
          $form['requested_by'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Requested by'),
            '#default_value' => $member_name,
            '#disabled' => TRUE,
          ];
          
          $form['amount'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Amount requested'),
            '#default_value' => '£' . number_format($contribution['total_amount'],2,',','.'),
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
            '#disabled' => TRUE, // Makes the field read-only
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
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $contribution_id = $form_state->getValue('contribution_id');
        //$status = $form_state->getValue('status') === 'Yes' ? True : False;
        $status = $form_state->getValue('status');
        $amount = $form_state->getValue('amount');

        //dpm($status);

        //return contribution record from id
        $contributions = Contribution::get(FALSE)
            ->addSelect('Kin_Contributions.Household')
            ->addWhere('id', '=', $contribution_id)
            ->addWhere('financial_type_id', '=', 5)
            ->execute();

        //is there a gift with this id?
        if($contributions->rowCount > 0) {
            //Get group from contribution record
            $group = $contributions[0]['Kin_Contributions.Household'];

            //Get current user - should be only the admin accessing this form
            $uid = \Drupal::currentUser()->id();

            // Get contact id of the admin user
            $admin_cid = kin_civi_get_cid($uid);

            // Get contact id, name and email of the member making the gift request
            $member_cid = kin_civi_get_contrib_contact($contribution_id);
            $name = kin_civi_get_name($member_cid)['display_name'];
            $email = kin_civi_get_name($member_cid)['email_primary.email'];

            // Only update if the person accessing this form is an admin of the group where the gift is being requested
            if(kin_civi_is_admin($admin_cid, $group)) {
                try {

                    // Please note $status is Case Sensitive!!
                    Contribution::update(FALSE)
                        ->addValue('Kin_Contributions.Approved', $status)
                        ->addWhere('id', '=', $contribution_id)
                        ->execute();

                    \Drupal::messenger()->addMessage($this->t('Contribution approved status updated to %status.', [
                        '%status' => $status,
                    ]));

                    //Send notification email
                    // If request denied send email to member informing them and asking them to get in touch
                    if($status == 'no') {
                        $params = [
                            'subject' => 'Kin Gift Request Declined',
                            'text' => "Dear $name,\r\n\r\nYour gift request has been declined. Please contact the group admin for further information.\r\n\r\nKin Team",
                            'from' => '"Admin" <admin@kin.coop>',
                            'toName' => $name,
                            'toEmail' => $email,
                            'admin' => $admin_cid,
                            'member' => $member_cid,
                        ];

                        \Drupal::service('kin_civi.kin_civi_service')->kin_civi_send_email($member_cid, $params);

                    // If request approved send notification email to admin to transfer money
                    } elseif ($status == 'yes') {
                        $params = [
                            'subject' => 'Kin Gift Request Approved',
                            'text' => "Dear Kin Admin,\r\n\r\nThe gift request for contribution id $contribution_id has been approved.\r\n\r\nPlease take the appropriate action.",
                            'from' => '"Kin" <admin@kin.coop>',
                            'toName' => 'Kin Admin',
                            'toEmail' => '"Admin" <admin@kin.coop>',
                            'admin' => $admin_cid,
                            'member' => $member_cid,
                        ];

                      $result = civicrm_api3('MessageTemplate', 'send', [
                        'id' => 123, // The ID of your message template
                        'contact_id' => $member_cid, // Recipient’s contact ID
                        'from' => '"Kin" <admin@kin.coop>',
                        'to_email' => 'admin@kin.coop',
                        'tplParams' => [
                          'group' => $group,
                          'amount' => '£' . $amount,
                          'contribution_id' => $contribution_id,
                        ],
                      ]);


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
}

function kin_civi_get_contribution($contribution_id) {
    $contributions = \Civi\Api4\Contribution::get(FALSE)
        ->addSelect('Kin_Contributions.Note', 'id', 'receive_date', 'total_amount', 'Kin_Contributions.Approved')
        ->addWhere('id', '=', $contribution_id)
        ->addWhere('financial_type_id', '=', 5)
        ->execute();

    if ($contributions->rowCount > 0) {
        return (array) $contributions->first();
    } else {
        return FALSE;
    }
}
function kin_civi_get_cid($uid) {
        try {
            // Query CiviCRM APIv4 to get the contact ID for the Drupal user.
            $result = UFMatch::get(FALSE)
                ->addWhere('uf_id', '=', $uid)
                ->addSelect('contact_id')
                ->execute();

            if ($result) {
                return (int) $result->first()['contact_id'];
            } else {
                return FALSE;
            }
        }
        catch (APIException $e) {
            \Drupal::logger('mymodule')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
        }
}

function kin_civi_get_name($cid) {
    try {
        // Query CiviCRM APIv4 to get the name from the id.
        $contacts = \Civi\Api4\Contact::get(FALSE)
            ->addSelect('display_name', 'email_primary.email')
            ->addWhere('id', '=', $cid)
            ->execute();

        if ($contacts) {
            return (array) $contacts->first();
        } else {
            return FALSE;
        }
    }
    catch (APIException $e) {
        \Drupal::logger('kin_civi')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
    }
}

function kin_civi_is_admin($rida, $ridb) {
    $relationships = \Civi\Api4\Relationship::get(FALSE)
        ->addSelect('relationship_type_id')
        ->addWhere('contact_id_a', '=', $rida)
        ->addWhere('contact_id_b', '=', $ridb)
        ->setLimit(1)
        ->execute();

    if($relationships) {
        if($relationships->first()['relationship_type_id'] == 11) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function kin_civi_get_contrib_contact($contribution_id) {
  $contributions = \Civi\Api4\Contribution::get(FALSE)
                                          ->addSelect('contact_id')
                                          ->addWhere('id', '=', $contribution_id)
                                          ->execute();
  if($contributions) {
    return $contributions->first()['contact_id'];
  }
}