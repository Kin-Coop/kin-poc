<?php

namespace Drupal\kin_civi\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Civi\Api4\Contribution;
use Civi\Api4\UFMatch;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RedirectCommand;

class CancelContributionConfirmForm extends ConfirmFormBase {

  protected $contributionId;

  public function getFormId() {
    return 'kin_civi_cancel_contribution_confirm';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $contribution_id = NULL) {
    $this->contributionId = $contribution_id;
    $form = parent::buildForm($form, $form_state);

    $form['actions']['submit']['#ajax'] = [
      'callback' => '::ajaxSubmit',
      'event' => 'click',
      'progress' => ['type' => 'throbber'],
    ];

    // Ensure the button doesn't also do a normal (non-AJAX) submit.
    $form['actions']['submit']['#attributes']['class'][] = 'use-ajax-submit';

    return $form;
  }

  public function getQuestion() {
    return $this->t('Confirm cancellation?');
  }

  public function getDescription() {
    return $this->t('This will cancel your pending contribution.');
  }

  public function getConfirmText() {
    return $this->t('Yes, cancel it');
  }

  public function getCancelText() {
    return $this->t('No, keep it');
  }

  public function getCancelUrl() {
    // Send them back to the members page listing their contributions.
    return Url::fromUri('internal:/member/home');
  }

  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If validation/ownership failed, form errors were set — show them, keep modal open.
    if ($form_state->getErrors()) {
      // Let the default form render show the messages inside the dialog.
      $response->addCommand(new CloseModalDialogCommand());
      $response->addCommand(new MessageCommand($this->t('Contribution could not be cancelled.'), NULL, ['type' => 'error']));
      return $response;
    }

    $response->addCommand(new CloseModalDialogCommand());
    $response->addCommand(new MessageCommand($this->t('Your contribution has been cancelled.')));
    // Reload the page so the View no longer shows the cancelled row.
    $response->addCommand(new RedirectCommand(Url::fromUri('internal:/member/home')->toString()));

    return $response;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('civicrm')->initialize();

    $uid = \Drupal::currentUser()->id();

    $contactId = UFMatch::get(FALSE)
      ->addSelect('contact_id')
      ->addWhere('uf_id', '=', $uid)
      ->execute()
      ->first()['contact_id'] ?? NULL;

    if (!$contactId) {
      $this->messenger()->addError($this->t('Could not verify your account.'));
      return;
    }

    // Re-verify ownership + pending status at submit time.
    $contribution = Contribution::get(FALSE)
      ->addSelect('id')
      ->addWhere('id', '=', $this->contributionId)
      ->addWhere('contact_id', '=', $contactId)
      ->addWhere('contribution_status_id:name', '=', 'Pending')
      ->execute()
      ->first();

    if (!$contribution) {
      $form_state->setErrorByName('', $this->t('Contribution not found or cannot be cancelled.'));
      return;
    }

    // Elevate permissions for the BAO's internal financial queries.
    $config = \CRM_Core_Config::singleton();
    $originalPermissions = $config->userPermissionClass->permissions;
    $config->userPermissionClass->permissions = ['access CiviCRM', 'access CiviContribute', 'edit contributions'];

    try {

      Contribution::update(FALSE)
        ->addWhere('id', '=', $this->contributionId)
        ->addValue('contribution_status_id:name', 'Cancelled')
        ->addValue('cancel_date', 'now')
        ->execute();

      $this->messenger()->addStatus($this->t('Your contribution has been cancelled.'));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Something went wrong cancelling your contribution.'));
      \Drupal::logger('kin_civi')->error($e->getMessage());
    }
    finally {
      $config->userPermissionClass->permissions = $originalPermissions;
    }

    //$form_state->setRedirectUrl($this->getCancelUrl());
  }
}
