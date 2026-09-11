<?php

  namespace Drupal\kin_civi\Controller;

  use Drupal\Core\Controller\ControllerBase;
  use Symfony\Component\HttpFoundation\RedirectResponse;
  use Civi\Api4\Contribution;
  use Civi\Api4\UFMatch;

  class CancelContributionController extends ControllerBase {

    public function cancel($contribution_id) {
      \Drupal::service('civicrm')->initialize();

      $uid = \Drupal::currentUser()->id();

      // Resolve the logged-in user's CiviCRM contact ID.
      $contactId = UFMatch::get(FALSE)
                          ->addSelect('contact_id')
                          ->addWhere('uf_id', '=', $uid)
                          ->execute()
                          ->first()['contact_id'] ?? NULL;

      if (!$contactId) {
        $this->messenger()->addError($this->t('Could not verify your account.'));
        return $this->redirect('<front>');
      }

      // Fetch the contribution and confirm ownership + pending status.
      $contribution = Contribution::get(FALSE)
                                  ->addSelect('id', 'contact_id', 'contribution_status_id:name')
                                  ->addWhere('id', '=', $contribution_id)
                                  ->addWhere('contact_id', '=', $contactId)
                                  ->addWhere('contribution_status_id:name', '=', 'Pending')
                                  ->execute()
                                  ->first();

      if (!$contribution) {
        $this->messenger()->addError($this->t('Contribution not found or cannot be cancelled.'));
        return $this->redirect('<front>');
      }

      // After your ownership check, before cancelling:
      $originalPermissions = \CRM_Core_Config::singleton()->userPermissionClass->permissions;
      \CRM_Core_Config::singleton()->userPermissionClass->permissions = ['access CiviCRM', 'access CiviContribute', 'edit contributions'];

      try {
        /*
        \Civi\Api4\Order::cancel(FALSE)
                        ->addWhere('id', '=', $contribution_id)
                        ->execute();
        */
        // Cancel it.
        Contribution::update(FALSE)
                    ->addWhere('id', '=', $contribution_id)
                    ->addValue('contribution_status_id:name', 'Cancelled')
                    ->addValue('cancel_date', 'now')
                    ->execute();
      }
      finally {
        \CRM_Core_Config::singleton()->userPermissionClass->permissions = $originalPermissions;
      }

      $this->messenger()->addStatus($this->t('Your contribution has been cancelled.'));

      // Redirect back to the members page.
      return new RedirectResponse(\Drupal::request()->headers->get('referer') ?: '/');
    }
  }
