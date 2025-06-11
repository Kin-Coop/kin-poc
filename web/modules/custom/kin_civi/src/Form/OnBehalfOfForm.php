<?php
  
  namespace Drupal\kin_civi\Form;
  
  \Drupal::service('civicrm')->initialize();
  
  use Drupal\Core\Form\FormBase;
  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Url;
  use CRM_Core_Exception;
  use Civi\Api4\UFMatch;
  use CRM_Utils_Money;
  
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
      
      $this->mySharedValue = 'Hello from buildForm';
      
      $user = \Drupal::currentUser();
      $uid = $user->id();
      $cid = kin_civi_get_contact_id($uid);
      $form_state->setValue('delegate_id', $cid);
      
      
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
      $group_id = $form_state->getValue('group_id');
      
      if (!\Drupal::service('email.validator')->isValid($email)) {
        $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
      }
      
      $amount = $form_state->getValue('amount');
      if ($amount <= 0) {
        $form_state->setErrorByName('amount', $this->t('Please enter a positive amount.'));
      }

      //dpm($email);
      // Get original contributor ID
      $onbehalfof_id = $this->kin_civi_get_id_from_email($email);
      $form_state->setValue('on_behalf_of_id', $onbehalfof_id);
      //dpm($onbehalfof_id);
      //dpm($group_id);
      
      // Check original contributor is in group
      $relationship = $this->kin_civi_check_contact_in_group($onbehalfof_id, $group_id);
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
      
      try {
        //$email = $form_state->getValue('email');
        $amount = $form_state->getValue('amount');
        $group_id = $form_state->getValue('group_id');
        $onbehalfof_id = $form_state->getValue('on_behalf_of_id');
        $delegate_id = $form_state->getValue('delegate_id');
        $ref = $form_state->getValue('reference');
        
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

          \Drupal::messenger()->addStatus($this->t(
              'Thank you for your contribution of @amount. Your reference number is @id.',
              [
                  '@amount' => CRM_Utils_Money::format($amount, 'GBP'),
                  '@id' => $results['id'],
              ]
          ));


      } catch (\Exception $e) {
        \Drupal::logger('kin_civi')->error($e->getMessage());
        \Drupal::messenger()->addError($this->t('There was an error processing your contribution.'));
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
          $individuals = \Civi\Api4\Individual::get(FALSE)
              ->addSelect('id')
              ->addWhere('email_primary.email', '=', $email)
              ->setLimit(1)
              ->execute();
        
        if (empty($individuals[0])) {
            \Drupal::logger('kin_civi')->error('CiviCRM APIv4 error: Contact not found.');
        } else {
          return $individuals[0]["id"];
        }
      }
      catch (CiviCRM_API4_Exception $e) {
        \Civi::log()->error("API error during email lookup: " . $e->getMessage());
      }
    }
    
    function kin_civi_check_contact_in_group($contact_id, $group_id) {
      try {
        $relationships = \Civi\Api4\Relationship::get(FALSE)
                        ->addSelect('*')
                        ->addWhere('contact_id_a', '=', $contact_id)
                        ->addWhere('contact_id_b', '=', $group_id)
                        ->setLimit(1)
                        ->execute();
        
        if (empty($relationships[0])) {
          return false;
        } else {
            return true;
        }
      }
      catch (CiviCRM_API4_Exception $e) {
        \Civi::log()->error("API error during email lookup: " . $e->getMessage());
      }
    }
  }
  
  