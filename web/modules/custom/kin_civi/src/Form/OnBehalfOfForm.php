<?php
  
  namespace Drupal\kin_civi\Form;
  
  \Drupal::service('civicrm')->initialize();
  
  use Drupal\Core\Form\FormBase;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Url;
  use CRM_Core_Exception;
  use Civi\Api4\UFMatch;
  
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
      
      $user = \Drupal::currentUser();
      $uid = $user->id();
      $cid = kin_civi_get_contact_id($uid);
      
      if(\Drupal::currentUser()->isAuthenticated() == FALSE) {
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      }
      
      $group_id = \Drupal::routeMatch()->getParameter('group_id');
      $group = $this->kin_civi_check_group($group_id);
      $ref = $cid . '-' . date('mdi');
      
      //dpm($cid);
      
      If($group == false) {
        $form = [
          '#markup' => $this->t('The group not found. Please check and try again.'),
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
      
      if (!\Drupal::service('email.validator')->isValid($email)) {
        $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
      }
      
      $amount = $form_state->getValue('amount');
      if ($amount <= 0) {
        $form_state->setErrorByName('amount', $this->t('Please enter a positive amount.'));
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
      
      civicrm_initialize();
      
      try {
        $email = $form_state->getValue('email');
        $amount = $form_state->getValue('amount');
        $group = $form_state->getValue('group');
        
        // Step 1: Find or create the contact
        $contactId = $this->findOrCreateContact($email);
        
        if (!$contactId) {
          throw new \Exception('Could not find or create contact.');
        }
        
        // Step 2: Create the contribution
        $contributionParams = [
          'contact_id' => $contactId,
          'financial_type_id' => 1, // Adjust this to your financial type ID
          'total_amount' => $amount,
          'receive_date' => date('YmdHis'),
          'contribution_status_id' => 2, // Pending status
          'custom_' . $this->getHouseholdCustomFieldId() => $householdReference,
        ];
        
        $result = civicrm_api3('Contribution', 'create', $contributionParams);
        
        if ($result['is_error']) {
          throw new \Exception($result['error_message']);
        }
        
        \Drupal::messenger()->addStatus($this->t('Thank you for your contribution of @amount. Your reference number is @id.', [
          '@amount' => \Drupal::service('renderer')->renderPlain(\Drupal::service('commerce_price.currency_formatter')->format($amount, 'USD')),
            '@id' => $result['id'],
        ]));
      

    } catch (\Exception $e) {
        \Drupal::logger('kin_civi')->error($e->getMessage());
        \Drupal::messenger()->addError($this->t('There was an error processing your contribution. Please try again later.'));
      }
    }
    
    /**
     * Finds or creates a contact based on email.
     */
    protected function findOrCreateContact($email) {
      try {
        // First try to find the contact
        $result = civicrm_api3('Contact', 'get', [
          'sequential' => 1,
          'return' => ['id'],
          'email' => $email,
          'options' => ['limit' => 1],
        ]);
        
        if ($result['count'] > 0) {
          return $result['values'][0]['id'];
        }
        
        // If not found, create a new contact
        $result = civicrm_api3('Contact', 'create', [
          'contact_type' => 'Individual',
          'email' => $email,
        ]);
        
        return $result['id'];
        
      } catch (\CiviCRM_API3_Exception $e) {
        \Drupal::logger('kin_civi')->error($e->getMessage());
        return FALSE;
      }
    }
    
    /**
     * Gets the custom field ID for the household reference.
     */
    protected function getHouseholdCustomFieldId() {
      try {
        // You'll need to adjust these parameters to match your custom field
        $result = civicrm_api3('CustomField', 'getsingle', [
          'name' => 'household_reference',
          'return' => ['id'],
        ]);
        
        return $result['id'];
      } catch (\CiviCRM_API3_Exception $e) {
        \Drupal::logger('kin_civi')->error('Could not find household_reference custom field: ' . $e->getMessage());
        throw new \Exception('Configuration error: Household reference custom field not found.');
      }
    }
    
    function kin_civi_check_group($group_id) {
      try {
        $group = \Civi\Api4\Household::get(FALSE)
                                          ->addSelect('id', 'display_name')
                                          ->addWhere('id', '=', $group_id)
                                          ->setLimit(1)
                                          ->execute();
        if (!empty($group)) {
          return (array) $group->first();
        } else {
          return FALSE;
        }
      }
      catch (APIException $e) {
        \Drupal::logger('kin_civi')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
      }
    }
    
    function kin_civi_get_id_from_email($email) {
      try {
        $contacts = \Civi\Api4\Contact::get(FALSE)
                                      ->addSelect('id')
                                      ->addWhere('email_primary.email', '=', $email)
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
    }
  }
  
  