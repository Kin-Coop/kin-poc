<?php

namespace Drupal\kin_civi\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

\Drupal::service('civicrm')->initialize();

/**
 * Form for editing CiviCRM recurring contributions.
 */
class EditRecurringContributionForm extends FormBase {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs the form.
   */
  public function __construct(MessengerInterface $messenger, RouteMatchInterface $route_match) {
    $this->messenger = $messenger;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kin_civi_edit_recurring_contribution';
  }

  /**
   * Custom access check.
   */
  public static function checkAccess(AccountInterface $account, $recurring_contribution_id = NULL) {
    // User must be logged in
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    // Check if CiviCRM is available
    if (!function_exists('civicrm_initialize')) {
      return AccessResult::forbidden();
    }

    civicrm_initialize();

    try {
      // Get the CiviCRM contact ID for the current user
      $contact_id = self::getCiviCrmContactId($account);

      if (!$contact_id) {
        return AccessResult::forbidden();
      }

      // Get the recurring contribution
      $recurring_contribution = \Civicrm\Api4\ContributionRecur::get(FALSE)
        ->addWhere('id', '=', $recurring_contribution_id)
        ->addWhere('contact_id', '=', $contact_id)
        ->execute()
        ->first();

      if ($recurring_contribution) {
        return AccessResult::allowed();
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('kin_civi')->error('Access check failed: @message', [
        '@message' => $e->getMessage(),
      ]);
    }

    return AccessResult::forbidden();
  }

  /**
   * Get CiviCRM contact ID for a Drupal user.
   */
  protected static function getCiviCrmContactId(AccountInterface $account) {
    try {
      // Try to get contact by email
      $email = $account->getEmail();

      if (empty($email)) {
        return NULL;
      }

      $contact = \Civicrm\Api4\Contact::get(FALSE)
        ->addWhere('email_primary.email', '=', $email)
        ->execute()
        ->first();

      return $contact['id'] ?? NULL;
    }
    catch (\Exception $e) {
      \Drupal::logger('kin_civi')->error('Error getting CiviCRM contact: @message', [
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $recurring_contribution_id = NULL) {
    civicrm_initialize();

    // Get current user's CiviCRM contact ID
    $contact_id = self::getCiviCrmContactId($this->currentUser());

    if (!$contact_id) {
      $this->messenger->addError($this->t('Unable to find your CiviCRM contact record.'));
      return $form;
    }

    try {
      // Fetch the recurring contribution
      $recurring_contribution = \Civicrm\Api4\ContributionRecur::get(FALSE)
        ->addWhere('id', '=', $recurring_contribution_id)
        ->addWhere('contact_id', '=', $contact_id)
        ->execute()
        ->first();

      if (!$recurring_contribution) {
        $this->messenger->addError($this->t('Recurring contribution not found or access denied.'));
        return $form;
      }

      // Store the recurring contribution data
      $form_state->set('recurring_contribution', $recurring_contribution);
      $form_state->set('recurring_contribution_id', $recurring_contribution_id);

      // Display ID (read-only)
      $form['id'] = [
        '#type' => 'item',
        '#title' => $this->t('Contribution ID'),
        '#markup' => $recurring_contribution['id'],
      ];

      // Display frequency (read-only)
      $frequency_interval = $recurring_contribution['frequency_interval'] ?? 1;
      $frequency_unit = $recurring_contribution['frequency_unit'] ?? '';
      $frequency_display = $frequency_interval . ' ' . $frequency_unit;

      if ($frequency_interval > 1) {
        $frequency_display .= 's';
      }

      $form['frequency'] = [
        '#type' => 'item',
        '#title' => $this->t('Frequency'),
        '#markup' => ucfirst($frequency_display),
      ];

      // Amount field (editable)
      $form['amount'] = [
        '#type' => 'number',
        '#title' => $this->t('Amount'),
        '#default_value' => $recurring_contribution['amount'] ?? '',
        '#min' => 0.01,
        '#step' => 0.01,
        '#required' => TRUE,
        '#field_prefix' => $recurring_contribution['currency'] ?? '£',
      ];

      // Next scheduled date (editable)
      $next_sched_date = $recurring_contribution['next_sched_contribution_date'] ?? '';

      // Convert from CiviCRM format (YYYY-MM-DD HH:MM:SS) to date input format (YYYY-MM-DD)
      if (!empty($next_sched_date)) {
        $next_sched_date = date('Y-m-d', strtotime($next_sched_date));
      }

      $form['next_sched_contribution_date'] = [
        '#type' => 'date',
        '#title' => $this->t('Next Scheduled Payment'),
        '#default_value' => $next_sched_date,
        '#required' => TRUE,
      ];

      // Display current values for reference
      $form['current_values'] = [
        '#type' => 'details',
        '#title' => $this->t('Current Values'),
        '#open' => FALSE,
      ];

      $form['current_values']['info'] = [
        '#markup' => '<p><strong>' . $this->t('Current Amount:') . '</strong> ' .
          ($recurring_contribution['currency'] ?? '£') .
          number_format($recurring_contribution['amount'] ?? 0, 2) . '</p>' .
          '<p><strong>' . $this->t('Current Next Payment:') . '</strong> ' .
          date('d/m/Y', strtotime($recurring_contribution['next_sched_contribution_date'] ?? 'now')) . '</p>',
      ];

      $form['actions'] = [
        '#type' => 'actions',
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Update Recurring Contribution'),
        '#button_type' => 'primary',
      ];

      $form['actions']['cancel'] = [
        '#type' => 'link',
        '#title' => $this->t('Cancel'),
        '#url' => \Drupal\Core\Url::fromRoute('<front>'),
        '#attributes' => ['class' => ['button']],
      ];

    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Error loading recurring contribution: @message', [
        '@message' => $e->getMessage(),
      ]));
      \Drupal::logger('kin_civi')->error('Error loading recurring contribution: @message', [
        '@message' => $e->getMessage(),
      ]);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $amount = $form_state->getValue('amount');
    $next_date = $form_state->getValue('next_sched_contribution_date');

    // Validate amount
    if ($amount <= 0) {
      $form_state->setErrorByName('amount', $this->t('Amount must be greater than zero.'));
    }

    // Validate date is in the future
    if (!empty($next_date)) {
      $next_timestamp = strtotime($next_date);
      $today = strtotime('today');

      if ($next_timestamp < $today) {
        $form_state->setErrorByName('next_sched_contribution_date',
          $this->t('Next scheduled payment must be today or in the future.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    civicrm_initialize();

    $recurring_contribution_id = $form_state->get('recurring_contribution_id');
    $amount = $form_state->getValue('amount');
    $next_date = $form_state->getValue('next_sched_contribution_date');

    // Convert date to CiviCRM format (YYYY-MM-DD HH:MM:SS)
    $next_date_formatted = $next_date . ' 00:00:00';

    try {
      // Update the recurring contribution
      $result = \Civicrm\Api4\ContributionRecur::update(FALSE)
        ->addWhere('id', '=', $recurring_contribution_id)
        ->addValue('amount', $amount)
        ->addValue('next_sched_contribution_date', $next_date_formatted)
        ->execute();

      if ($result->count() > 0) {
        $this->messenger->addStatus($this->t('Your recurring contribution has been updated successfully.'));

        \Drupal::logger('kin_civi')->info('Recurring contribution @id updated by user @user', [
          '@id' => $recurring_contribution_id,
          '@user' => $this->currentUser()->id(),
        ]);

        // Redirect to a success page or back to user profile
        // $form_state->setRedirect('<front>');
      }
      else {
        $this->messenger->addError($this->t('Failed to update recurring contribution.'));
      }
    }
    catch (\Exception $e) {
      $this->messenger->addError($this->t('Error updating recurring contribution: @message', [
        '@message' => $e->getMessage(),
      ]));

      \Drupal::logger('kin_civi')->error('Error updating recurring contribution: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }

}
