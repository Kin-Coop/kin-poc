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
  class TestForm extends FormBase {
    
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      //return 'on_behalf_of_form';
      return 'test_form';
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, ?Request $request = NULL) {
      
      $this->mySharedValue = 'Hello from buildForm';
      $utils = \Drupal::service('kin_civi.utils');
      
      $user = \Drupal::currentUser();
      $uid = $user->id();
      $cid = $utils->kin_civi_get_contact_id($uid);
      //$form_state->setValue('delegate_id', $cid);

      
      
      if(\Drupal::currentUser()->isAuthenticated() == FALSE) {
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      }
      
      //$group_id = \Drupal::routeMatch()->getParameter('group_id');
      //$group = \Drupal::service('kin_civi.utils')->kin_civi_check_group($group_id);

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
          '#description' => $this->t( 'The email address of the member you are making the contribution on behalf of.' ),
        ];



        
        $form['actions'] = [
          '#type' => 'actions',
        ];
        
        $form['actions']['submit'] = [
          '#type'  => 'submit',
          '#value' => $this->t( 'Submit Contribution' ),
        ];

      
      return $form;
    }
    
    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

      
    }
    
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $smarty = \CRM_Core_Smarty::singleton();

        // Assign variables.
        $smarty->assign('first_name', 'Jane');
        $smarty->assign('group_code', 'ABC123');

        // Render a string:
        $templateString = <<<EOT
            Hello {\$first_name},
            
            Your code is {\$group_code}.
            EOT;

        $output = $smarty->fetch("string:$templateString");

        echo $output;


      $utils = \Drupal::service('kin_civi.utils');

        //dpm($form_state);

        //$email = $form_state->getValue('email');
        $group_id = $form_state->getValue('group_id');
        //$onbehalfof_id = $form_state->getValue('on_behalf_of_id');
        //$delegate_id = $form_state->getValue('delegate_id');
        //$onbehalfof_name = $utils->kin_civi_get_name($onbehalfof_id);


          // Set a flag to indicate successful submission.
          $form_state->set('submitted', TRUE);


          //Send email
          //$contribution_id = $results->first()['id'];
          //dpm($onbehalfof_name);

/*
          $sent = $utils->sendTemplateEmail(
              contactId: $delegate_id,
              toEmail: $utils->kin_civi_get_email($delegate_id),
              templateId: 96, // Your template ID
              params: [
                  'onbehalfof_name' => $onbehalfof_name,

              ],
              contributionId: $contribution_id // Optional
          );

          if ($sent) {
              \Drupal::messenger()->addMessage('Confirmation email sent and logged.');
          }
          else {
              \Drupal::messenger()->addError('Error sending confirmation email.');
          }
*/
          //$form_state->set('message' , $message);

          // Rebuild the form so buildForm runs again and shows the message.
         // $form_state->setRebuild(TRUE);

    }

  }
  
  