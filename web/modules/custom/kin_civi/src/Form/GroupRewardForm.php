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
  use Drupal\Core\Link;

  /**
   * Provides a custom contribution form.
   */
  class GroupRewardForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
      return 'group_reward_form';
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
      //$form_state->setValue('delegate_id', $cid);



      if(\Drupal::currentUser()->isAuthenticated() == FALSE) {
        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
      }

      $group_id = \Drupal::routeMatch()->getParameter('group_id');
      //$form_state->setValue('group_id', $group_id);
      $group = \Drupal::service('kin_civi.utils')->kin_civi_check_group($group_id);
      $ref = $cid . '-' . $group_id . 'R';

      $isAdmin = self::isAdmin($cid, $group_id);

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
      } elseif($isAdmin == false) {
        $form = [
          '#markup' => $this->t("You are not an admin of " . $group['display_name'] . ". You need to be an admin in order to create a group reward."),
        ];
      } else {

        $form = [
          '#markup' => $this->t('
            <p>This form is for creating a group reward. This could be:</p>
          <ul>
          <li>A personal gift to a member (ie money agreed on rotation or a solidarity payment)</li>
           <li>A community appreciation gift (recognising someone who has done a lot to support your group and sends them a gift of appreciation that is not regular)</li>
           <li>A collective purchase</li>
            <li>Something else</li>
            </ul>
          <p>Please select the type of gift it is below and add some comments to explain the gift/purchase.
          You will need to enter the email address of the member who will be receiving the money.</p>
          <p><strong>You need to have the agreement of the whole group before requesting a group reward.</strong></p>
          '),
        ];

        $form['group_id'] = [
          '#type'  => 'hidden',
          '#value' => $group_id,
        ];

        $form['delegate_id'] = [
          '#type'  => 'hidden',
          '#value' => $cid,
        ];

        $form['group'] = [
          '#type' => 'item',
          '#title' => $this->t('Group'),
          '#markup' => '<strong>' . $group['display_name'] . '</strong>',
        ];

        $form['reward_type'] = [
          '#type' => 'select',
          '#title' => $this->t('Type of Reward'),
          '#options' => $this->getRewardTypeOptions(),
          '#empty_option' => $this->t('- Select -'),
          '#required' => TRUE,
        ];

        $form['note'] = [
          '#type' => 'textarea',
          '#title' => $this->t('Please add some details about what this group reward is for'),
          '#required' => TRUE,
        ];

        $form['group_agreement'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Has the whole group agreed to this reward?'),
          '#required' => TRUE,
        ];

        $form['not_goods_services'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('This is not a payment for goods or services.'),
          '#states' => [
            'visible' => [
              ':input[name="reward_type"]' => ['value' => 2],
            ],
            'required' => [
              ':input[name="reward_type"]' => ['value' => 2],
            ],
          ],
        ];

        $form['no_prior_agreement'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('There was no prior agreement, contract or expectation of payment.'),
          '#states' => [
            'visible' => [
              ':input[name="reward_type"]' => ['value' => 2],
            ],
            'required' => [
              ':input[name="reward_type"]' => ['value' => 2],
            ],
          ],
        ];

        $form['leftover_funds'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('The money comes from leftover group funds.'),
          '#states' => [
            'visible' => [
              ':input[name="reward_type"]' => ['value' => 2],
            ],
            'required' => [
              ':input[name="reward_type"]' => ['value' => 2],
            ],
          ],
        ];

        $form['email'] = [
          '#type'        => 'email',
          '#title'       => $this->t( 'Email Address' ),
          '#required'    => TRUE,
          '#description' => $this->t( 'The email address of the member you are giving the reward to.' ),
        ];

        $form['amount'] = [
          '#type'        => 'number',
          '#title'       => $this->t( 'Reward Amount' ),
          '#required'    => TRUE,
          '#min'         => 0.01,
          '#step'        => 0.01,
          '#description' => $this->t( 'Enter the amount you are contributing.' ),
        ];

        $form['reference'] = [
          '#type' => 'hidden',
          '#title' => $this->t('Reference'),
          '#default_value' => $ref, // This is the preset value
          '#disabled' => TRUE, // Makes the field read-only
        ];

        $form['actions'] = [
          '#type' => 'actions',
        ];

        $form['actions']['submit'] = [
          '#type'  => 'submit',
          '#value' => $this->t( 'Create Reward' ),
          '#button_type' => 'primary', // Drupal core style
          '#attributes' => [
            'class' => ['my-3', 'me-4'],
          ],
        ];

        // Add a link styled as a button.
        $url = Url::fromUserInput('/member/group/' . $group_id);
        $link = Link::fromTextAndUrl($this->t(' Back to group'), $url)->toRenderable();
        $link['#attributes']['class'] = ['btn', 'btn-secondary', 'bi', 'bi-arrow-left', 'my-3'];

        // Add the link after the submit button.
        $form['actions']['back'] = [
          '#type' => 'markup',
          '#markup' => \Drupal::service('renderer')->render($link),
          '#weight' => 10,
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
        $ref = $onbehalfof_id . '-' . $group_id . 'W';
        $onbehalfof_name = $utils->kin_civi_get_name($onbehalfof_id);
        $group_name = $utils->kin_civi_get_name($group_id);
        $agreed = is_null($form_state->getValue('group_agreement')) ? 0 : $form_state->getValue('group_agreement');
        $not_goods_services = is_null($form_state->getValue('not_goods_services')) ? 0 : $form_state->getValue('not_goods_services');
        $no_prior_agreement = is_null($form_state->getValue('no_prior_agreement')) ? 0 : $form_state->getValue('no_prior_agreement');
        $leftover_funds = is_null($form_state->getValue('leftover_funds')) ? 0 : $form_state->getValue('leftover_funds');
        $reward_type = $form_state->getValue('reward_type');

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
                  ->addValue('financial_type_id', 5)
                  ->addValue('total_amount', $amount)
                  ->addValue('contribution_status_id', 2)
                  ->addValue('Kin_Contributions.Household', $group_id)
                  ->addValue('Kin_Contributions.Delegated_Contributor', $delegate_id)
                  ->addValue('Unique_Contribution_ID.Unique_Contribution_Reference', $ref)
                  ->addValue('Group_Reward.Reward_Type', $reward_type)
                  ->addValue('Kin_Contributions.Note', $form_state->getValue('note'))
                  ->addValue('Group_Reward.All_members_agreed', $agreed)
                  ->addValue('Group_Reward.This_is_not_a_payment_for_goods_or_services', $not_goods_services)
                  ->addValue('Group_Reward.There_was_no_prior_agreement_contract_or_expectation_of_payment', $no_prior_agreement)
                  ->addValue('Group_Reward.The_money_comes_from_leftover_group_funds', $leftover_funds)
                  ->execute();

        // Set contribution source to be "reward" so we can then filter rewards in the contributions/donations

        $contribution_id = $results->first()['id'];

        \Drupal::messenger()->addStatus($this->t('Group reward created successfully.'));

        /*
        // Send email to delegate
        $delivery = \CRM_Core_BAO_MessageTemplate::sendTemplate([
          'workflow' => 'onbehalfof_delegate',
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
            'from' => '"Kin" <members@kin.coop>',
        ]);

        // Log email as activity to contacts
          $activity = \Civi\Api4\Activity::create(FALSE)
              ->addValue('status_id:name', 'Completed')
              ->addValue('activity_type_id:name', 'Email')
              ->addValue('subject', $delivery[1])
              ->addValue('details', $delivery[3])
              ->addValue('source_contact_id', $delegate_id)
              ->addValue('target_contact_id', $onbehalfof_id)
              ->execute();


          // Send email to original contributor
      $delivery = \CRM_Core_BAO_MessageTemplate::sendTemplate([
          'workflow' => 'onbehalfof_contributor',
          'tokenContext' => [
              'contactId' => $delegate_id,
              'contributionId' => $contribution_id,
          ],
          'tplParams' => [
              'group' => $group_name,
              'onBehalfOf' => $onBehalfOf,
              'ref' => $ref,
          ],
          'toEmail' => $onBehalfOf['email_primary.email'],
          'from' => '"Kin" <members@kin.coop>',
      ]);

          // Log email as activity to contacts
          $activity = \Civi\Api4\Activity::create(FALSE)
              ->addValue('status_id:name', 'Completed')
              ->addValue('activity_type_id:name', 'Email')
              ->addValue('subject', $delivery[1])
              ->addValue('details', $delivery[3])
              ->addValue('source_contact_id', $onbehalfof_id)
              ->addValue('target_contact_id', $delegate_id)
              ->execute();
*/


          // Set a flag to indicate successful submission.
          $form_state->set('submitted', TRUE);

          $message = t( '<p>Your group reward has been created successfully. The details are:</p>
            <p>Amount: @amount<br>
            Group reward payee: @name<br>
            Reference: @ref</p>
            <p><strong>A payment of @amount will be paid to @name from Kin Cooperative with the reference @ref</strong></p>
            <p>An email will be sent to yourself and to @name confirming the reward payment.</p>
            <p style="margin: 1.2rem 0 2rem;"><a href="/member/group/@gid" class="btn btn-primary">Return to group</a></p>
                    ',
              [
                  '@amount' => CRM_Utils_Money::format($amount, 'GBP'),
                  '@name' => $onbehalfof_name,
                  '@ref' => $ref,
                  '@gid' => $group_id,
              ]);

          //dpm($delivery);

        /*
          if ($delivery[0]) {
              //\Drupal::messenger()->addMessage('Confirmation email sent and logged.');
          }
          else {
              \Drupal::messenger()->addError('Error sending confirmation email.');
          }
        */

          $form_state->set('message' , $message);

          // Rebuild the form so buildForm runs again and shows the message.
          $form_state->setRebuild(TRUE);


      } catch (\Exception $e) {
        \Drupal::logger('kin_civi')->error($e->getMessage());
        \Drupal::messenger()->addError($this->t('There was an error processing your contribution.'));
      }
    }

    protected static function isAdmin($contact, $group) {
      try {
        $admins = \Civi\Api4\Relationship::get(FALSE)
          ->addSelect('id')
          ->addWhere('contact_id_b', '=', $group)
          ->addWhere('relationship_type_id', '=', 11)
          ->addWhere('contact_id_a', '=', $contact)
          ->execute()
          ->countFetched();

        return !($admins == 0);
      }
      catch (\Exception $e) {
        \Drupal::logger('kin_civi')->error('Error getting CiviCRM admin status: @message', [
          '@message' => $e->getMessage(),
        ]);
        return NULL;
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

