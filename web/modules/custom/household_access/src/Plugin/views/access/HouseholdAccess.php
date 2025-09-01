<?php

namespace Drupal\household_access\Plugin\views\access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Access plugin for household-based view access control.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "household_access",
 *   title = @Translation("Household Access"),
 *   help = @Translation("Access will be granted to users who belong to the household specified in the view's contextual filter.")
 * )
 */
class HouseholdAccess extends AccessPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['household_argument'] = ['default' => 'arg_0'];
    $options['bypass_permission'] = ['default' => 'administer civicrm'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    \Drupal::logger('household_access')->notice('buildOptionsForm called');
    parent::buildOptionsForm($form, $form_state);

    $form['household_argument'] = [
      '#type' => 'select',
      '#title' => $this->t('Household ID argument'),
      '#description' => $this->t('Select which contextual filter contains the household ID.'),
      '#options' => [
        'arg_0' => $this->t('First argument (arg_0)'),
        'arg_1' => $this->t('Second argument (arg_1)'),
        'arg_2' => $this->t('Third argument (arg_2)'),
      ],
      '#default_value' => $this->options['household_argument'],
      '#required' => TRUE,
    ];

    $form['bypass_permission'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bypass permission'),
      '#description' => $this->t('Users with this permission will have full access regardless of household membership.'),
      '#default_value' => $this->options['bypass_permission'],
      '#size' => 40,
      '#maxlength' => 255,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    parent::validateOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    $this->options['household_argument'] = $form_state->getValue('household_argument');
    $this->options['bypass_permission'] = $form_state->getValue('bypass_permission');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // Check bypass permission first
    if (!empty($this->options['bypass_permission']) &&
      $account->hasPermission($this->options['bypass_permission'])) {
      return TRUE;
    }

    // Get household ID from the specified argument
    $household_id = $this->getHouseholdIdFromArgument();

    if (!$household_id) {
      return FALSE;
    }

    // Get CiviCRM contact ID for the current user
    $contact_id = $this->getContactIdFromUser($account);

    if (!$contact_id) {
      return FALSE;
    }

    // Check if contact belongs to the household
    return $this->contactBelongsToHousehold($contact_id, $household_id);
  }

  /**
   * Get household ID from the view's contextual filter.
   */
  protected function getHouseholdIdFromArgument() {
    $argument_key = $this->options['household_argument'];
    $argument_index = (int) str_replace('arg_', '', $argument_key);

    if (isset($this->view->args[$argument_index])) {
      return (int) $this->view->args[$argument_index];
    }

    return NULL;
  }

  /**
   * Get CiviCRM contact ID from Drupal user account.
   */
  protected function getContactIdFromUser(AccountInterface $account) {
    try {
      civicrm_initialize();

      $result = civicrm_api3('UFMatch', 'get', [
        'uf_id' => $account->id(),
        'sequential' => 1,
      ]);

      if (!empty($result['values'][0]['contact_id'])) {
        return (int) $result['values'][0]['contact_id'];
      }
    } catch (\Exception $e) {
      \Drupal::logger('household_access')->error('Error getting contact ID: @error', [
        '@error' => $e->getMessage()
      ]);
    }

    return FALSE;
  }

  /**
   * Check if a contact belongs to a specific household.
   */
  protected function contactBelongsToHousehold($contact_id, $household_id) {
    try {
      civicrm_initialize();

      // Check contact_id_a -> contact_id_b relationship
      $result = civicrm_api3('Relationship', 'get', [
        'contact_id_a' => $contact_id,
        'contact_id_b' => $household_id,
        'is_active' => 1,
        'sequential' => 1,
        'options' => ['limit' => 1],
      ]);

      if (!empty($result['values'])) {
        return TRUE;
      }

      // Check reverse relationship
      $result = civicrm_api3('Relationship', 'get', [
        'contact_id_a' => $household_id,
        'contact_id_b' => $contact_id,
        'is_active' => 1,
        'sequential' => 1,
        'options' => ['limit' => 1],
      ]);

      return !empty($result['values']);

    } catch (\Exception $e) {
      \Drupal::logger('household_access')->error('Error checking household relationship: @error', [
        '@error' => $e->getMessage()
      ]);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Household access');
  }

  public function alterRouteDefinition(Route $route)
  {
    // TODO: Implement alterRouteDefinition() method.
  }
}
