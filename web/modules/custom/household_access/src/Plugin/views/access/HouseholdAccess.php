<?php

namespace Drupal\household_access\Plugin\views\access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['household_argument'] = ['default' => 'arg_0'];
    $options['bypass_permission'] = ['default' => 'administer civicrm'];
    $options['relationship_types'] = ['default' => ''];
    $options['check_active_only'] = ['default' => TRUE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['household_argument'] = [
      '#type' => 'select',
      '#title' => $this->t('Household ID argument'),
      '#description' => $this->t('Select which contextual filter contains the household ID.'),
      '#options' => $this->getContextualFilterOptions(),
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

    $form['relationship_types'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Allowed relationship types'),
      '#description' => $this->t('Comma-separated list of CiviCRM relationship type IDs. Leave empty to allow all relationship types.'),
      '#default_value' => $this->options['relationship_types'],
      '#size' => 40,
      '#maxlength' => 255,
    ];

    $form['check_active_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Check active relationships only'),
      '#description' => $this->t('If checked, only active relationships will be considered for access control.'),
      '#default_value' => $this->options['check_active_only'],
    ];
  }

  /**
   * Get available contextual filter options.
   */
  protected function getContextualFilterOptions() {
    $options = [];

    // Get arguments from the view
    $arguments = $this->view->display_handler->getHandlers('argument');

    if (empty($arguments)) {
      $options['arg_0'] = $this->t('First argument (arg_0)');
      $options['arg_1'] = $this->t('Second argument (arg_1)');
      $options['arg_2'] = $this->t('Third argument (arg_2)');
    } else {
      $i = 0;
      foreach ($arguments as $id => $argument) {
        $options['arg_' . $i] = $this->t('Argument @num: @title', [
          '@num' => $i,
          '@title' => $argument->adminLabel(),
        ]);
        $i++;
      }
    }

    return $options;
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

      // Build base API parameters
      $params = [
        'sequential' => 1,
        'options' => ['limit' => 1],
      ];

      // Add relationship type filter if specified
      if (!empty($this->options['relationship_types'])) {
        $relationship_types = array_map('trim', explode(',', $this->options['relationship_types']));
        $relationship_types = array_filter($relationship_types, 'is_numeric');
        if (!empty($relationship_types)) {
          $params['relationship_type_id'] = ['IN' => $relationship_types];
        }
      }

      // Add active relationship filter if specified
      if ($this->options['check_active_only']) {
        $params['is_active'] = 1;
      }

      // Check contact_id_a -> contact_id_b relationship
      $params['contact_id_a'] = $contact_id;
      $params['contact_id_b'] = $household_id;

      $result = civicrm_api3('Relationship', 'get', $params);

      if (!empty($result['values'])) {
        return TRUE;
      }

      // Check reverse relationship contact_id_b -> contact_id_a
      $params['contact_id_a'] = $household_id;
      $params['contact_id_b'] = $contact_id;

      $result = civicrm_api3('Relationship', 'get', $params);

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
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_household_access_check', 'TRUE');
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
    $count = 0;
    $title_parts = [];

    if (!empty($this->options['bypass_permission'])) {
      $title_parts[] = $this->t('Bypass: @perm', ['@perm' => $this->options['bypass_permission']]);
      $count++;
    }

    if (!empty($this->options['relationship_types'])) {
      $title_parts[] = $this->t('Relationship types: @types', ['@types' => $this->options['relationship_types']]);
      $count++;
    }

    if ($this->options['check_active_only']) {
      $title_parts[] = $this->t('Active only');
      $count++;
    }

    if (empty($title_parts)) {
      return $this->t('Household access');
    }

    return $this->t('Household access: @settings', ['@settings' => implode(', ', $title_parts)]);
  }

}
