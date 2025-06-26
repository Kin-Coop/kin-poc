<?php
  
  namespace Drupal\kin_civi\Form;
  
  \Drupal::service('civicrm')->initialize();
  
  use Drupal\Core\Form\FormBase;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Url;
  use CRM_Core_Exception;
  use Civi\Api4\UFMatch;
  use CRM_Utils_Money;
  use Drupal\kin_civi\Service\Utils;
  
  /**
   * Provides a custom contribution form.
   */
  class OnBehalfOfForm extends FormBase {
    
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      return 'on_behalf_of_form';
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, ?Request $request = NULL) {
      
      //$this->mySharedValue = 'Hello from buildForm';
      $utils = \Drupal::service('kin_civi.utils');
      
      $user = \Drupal::currentUser();
      $uid = $user->id();
      $cid = $utils->kin_civi_get_contact_id($uid);
      $form_state->setValue('delegate_id', $cid);

      
      
      if(\Drupal::currentUser()->isAuthenticated() == FALSE) {
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      }
      
      $group_id = \Drupal::routeMatch()->getParameter('group_id');
      $group = \Drupal::service('kin_civi.utils')->kin_civi_check_group($group_id);
      $ref = $cid . '-' . date('mdi');
      
      //dpm($cid);
      //dpm($form_state->getValue('delegate_id'));
      //dpm($form_state);
      
      If($group == false) {
          $form = [
              '#markup' => $this->t('The group was not found. Please check and try again.'),
          ];
      } elseif ($form_state->get('submitted')) {
          return [
              '#markup' => $form_state->get('message')
          ];
      } else {
        $form['intro'] = [
          '#markup' => '<p>This form is to allow you to make a contribution on behalf of someone else in your group.
                        Please ensure you have the correct email for the member.</p>
                        <p>Once you have submitted this form, please make the bank transfer to Kin for the amount
                        stated using the unique reference below.</p>',
        ];
        
        $form['group_id'] = [
          '#type'  => 'hidden',
          '#value' => $group_id,
        ];

          $form['delegate_id'] = [
              '#type'  => 'hidden',
              '#value' => $cid,
          ];
        
        $form['email'] = [
          '#type'        => 'email',
          '#title'       => $this->t( 'Email Address' ),
          '#required'    => TRUE,
          '#description' => $this->t( 'The email address of the member you are making the contribution on behalf of.' ),
        ];
        
        $form['amount'] = [
          '#type'        => 'number',
          '#title'       => $this->t( 'Contribution Amount' ),
          '#required'    => TRUE,
          '#min'         => 0.01,
          '#step'        => 0.01,
          '#description' => $this->t( 'Enter the amount you are contributing.' ),
        ];
        
        $form['group'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Group'),
          '#default_value' => $group['display_name'], // This is the preset value
          '#disabled' => TRUE, // Makes the field read-only
        ];
        
        $form['reference'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Reference'),
          '#default_value' => $ref, // This is the preset value
          '#disabled' => TRUE, // Makes the field read-only
          '#description' => $this->t('Please use this reference when making the bank transfer.'),
        ];
        
        $form['note'] = [
          '#type' => 'textarea',
          '#title' => $this->t('Notes'),
          '#description' => $this->t( 'Enter any notes you want to give about this contribution.' ),
        ];
        
        $form['actions'] = [
          '#type' => 'actions',
        ];
        
        $form['actions']['submit'] = [
          '#type'  => 'submit',
          '#value' => $this->t( 'Submit Contribution' ),
        ];
      }
      
      return $form;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
      $email = $form_state->getValue('email');
      $group_id = $form_state->getValue('group_id');
      $utils = \Drupal::service('kin_civi.utils');
      
      if (!\Drupal::service('email.validator')->isValid($email)) {
        $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
      }
      
      $amount = $form_state->getValue('amount');
      if ($amount <= 0) {
        $form_state->setErrorByName('amount', $this->t('Please enter a positive amount.'));
      }

      //dpm($email);
      // Get original contributor ID
      $onbehalfof_id = $utils->kin_civi_get_id_from_email($email);
      $form_state->setValue('on_behalf_of_id', $onbehalfof_id);
      //dpm($onbehalfof_id);
      //dpm($group_id);
      
      // Check original contributor is in group
      $relationship = $utils->kin_civi_check_contact_in_group($onbehalfof_id, $group_id);
      if(!$relationship) {
        $form_state->setErrorByName('email', $this->t('This email does not match anyone in this group. Please try again.'));
      }
      
    }
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
      // Initialize CiviCRM
      if (!\Drupal::moduleHandler()->moduleExists('civicrm')) {
        \Drupal::messenger()->addError($this->t('CiviCRM is not installed.'));
        return;
      }

      $utils = \Drupal::service('kin_civi.utils');

        //dpm($form_state);

        //$email = $form_state->getValue('email');
        $amount = $form_state->getValue('amount');
        $group_id = $form_state->getValue('group_id');
        $onbehalfof_id = $form_state->getValue('on_behalf_of_id');
        $delegate_id = $form_state->getValue('delegate_id');
        $ref = $form_state->getValue('reference');
        $onbehalfof_name = $utils->kin_civi_get_name($onbehalfof_id);
        $group_name = $utils->kin_civi_get_name($group_id);
      
        $onBehalfOf = \Civi\Api4\Contact::get()
                     ->addSelect('custom.*','*','email_primary.email')
                     ->addWhere('id', '=', $onbehalfof_id)
                     ->execute()
                     ->first();
      
      $delegate = \Civi\Api4\Contact::get()
                    ->addSelect('custom.*','*','email_primary.email')
                    ->addWhere('id', '=', $delegate_id)
                    ->execute()
                    ->first();
      
      try {

        // Step 2: Create the contribution
        $results = \Civi\Api4\Contribution::create(FALSE)
                  ->addValue('contact_id', $onbehalfof_id)
                  ->addValue('financial_type_id', 1)
                  ->addValue('total_amount', $amount)
                  ->addValue('contribution_status_id', 2)
                  ->addValue('Kin_Contributions.Household', $group_id)
                  ->addValue('Kin_Contributions.Delegated_Contributor', $delegate_id)
                  ->addValue('Unique_Contribution_ID.Unique_Contribution_Reference', $ref)
                  ->execute();
        
        $contribution_id = $results->first()['id'];
        
        // To delegate
        $delivery = \CRM_Core_BAO_MessageTemplate::sendTemplate([
          'workflow' => 'workflow_test',
          'tokenContext' => [
            'contactId' => $delegate_id,
            'contributionId' => $contribution_id,
          ],
          'tplParams' => [
            'group' => $group_name,
            'onBehalfOf' => $onBehalfOf,
              'ref' => $ref,
          ],
          'toEmail' => $delegate['email_primary.email'],
          'from' => 'admin@kin.coop',
          'bcc' => 'info@kin.coop',
        ]);
        
        \Drupal::messenger()->addStatus($this->t('Contribution created successfully.'));

          // Set a flag to indicate successful submission.
          $form_state->set('submitted', TRUE);

          $message = t( '<p>Your contribution has been created successfully. The details are:</p>
            <p>Amount: @amount<br>
            On behalf of: @name<br>
            Reference: @ref</p>
            <p><strong>Please go to your bank app and pay your contribution to:</strong></p>
            <p>&nbsp;</p>
            <p style="font-weight: 600;">Kin Co operative Limited<br />
            Account Number: 67355138<br />
            Sort Code: 08-92-99</p>
            <p>&nbsp;</p>
            <p><strong>Please enter the unique contribution reference as the payment reference.</strong></p>
            <p>An email will be sent to @name confirming the contribution. You will also receive an email with the payment instructions.</p>
            <p>If you are with the Co-operative Bank, <a href="https://www.co-operativebank.co.uk/help-and-support/payments/money-transfer/">they are having a temporary issue with references</a> and you can leave this field blank.</p>
            <p style="margin: 1.2rem 0 2rem;"><a href="/members/group/@gid" class="btn btn-primary">Return to group</a></p>
                    ',
              [
                  '@amount' => CRM_Utils_Money::format($amount, 'GBP'),
                  '@name' => $onbehalfof_name,
                  '@ref' => $ref,
                  '@gid' => $group_id,
              ]);

          //dpm($delivery);

          if ($delivery[0]) {
              \Drupal::messenger()->addMessage('Confirmation email sent and logged.');
          }
          else {
              \Drupal::messenger()->addError('Error sending confirmation email.');
          }

          $form_state->set('message' , $message);

          // Rebuild the form so buildForm runs again and shows the message.
          $form_state->setRebuild(TRUE);


      } catch (\Exception $e) {
        \Drupal::logger('kin_civi')->error($e->getMessage());
        \Drupal::messenger()->addError($this->t('There was an error processing your contribution.'));
      }
    }

  }
  
  