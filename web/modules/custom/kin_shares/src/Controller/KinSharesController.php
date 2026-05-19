<?php

namespace Drupal\kin_shares\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\Entity\User;

class KinSharesController extends ControllerBase {

  use MessengerTrait;

  /**
   * Create a Kin Shares household.
   */
  public function create_kinshare() {

    // Get logged in Drupal user.
    $account = $this->currentUser();
    $uid = $account->id();

    // Load Drupal user entity.
    $user = User::load($uid);

    if (!$user) {
      $this->messenger()->addError('Unable to load user.');
      return new RedirectResponse('/');
    }

    try {

      // Bootstrap CiviCRM if needed.
      civicrm_initialize();

      // Get matching CiviCRM contact ID from Drupal user ID.
      $ufMatch = \Civi\Api4\UFMatch::get(FALSE)
        ->addSelect('contact_id')
        ->addWhere('uf_id', '=', $uid)
        ->execute()
        ->first();

      if (empty($ufMatch['contact_id'])) {
        throw new \Exception('No matching CiviCRM contact found.');
      }

      $contactId = $ufMatch['contact_id'];

      // Load the individual contact.
      $contact = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('first_name', 'last_name')
        ->addWhere('id', '=', $contactId)
        ->execute()
        ->first();

      if (!$contact) {
        throw new \Exception('Unable to load contact.');
      }

      $householdName = trim(
        $contact['first_name'] . ' ' .
        $contact['last_name'] . ' Kin Shares'
      );

      /**
       * Create household contact.
       */
      $household = \Civi\Api4\Contact::create(FALSE)
        ->addValue('contact_type', 'Household')
        ->addValue('contact_sub_type', ['Kin_Share'])
        ->addValue('household_name', $householdName)
        ->execute()
        ->first();

      if (empty($household['id'])) {
        throw new \Exception('Failed to create household.');
      }

      $householdId = $household['id'];

      /**
       * Create relationship.
       *
       * Assumes relationship type already exists:
       * "Member of Household"
       */
      \Civi\Api4\Relationship::create(FALSE)
        ->addValue('contact_id_a', $contactId)
        ->addValue('contact_id_b', $householdId)
        ->addValue('relationship_type_id', 8) // Member of household
        ->addValue('is_active', TRUE)
        ->addValue('start_date', date('Y-m-d'))
        ->execute();

      // Success message.
      $this->messenger()->addStatus(
        $this->t('Successfully created new Kin Shares household.')
      );

      // Redirect.
      return new RedirectResponse('/members/group/' . $householdId);

    }
    catch (\Exception $e) {

      \Drupal::logger('kin_shares')->error($e->getMessage());

      $this->messenger()->addError(
        $this->t('Unable to create Kin Shares household.')
      );

      return new RedirectResponse('/');
    }
  }

}
