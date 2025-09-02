<?php

namespace Drupal\household_access\Plugin\views\access;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Simple household access control.
 *
 * @ViewsAccess(
 *   id = "simple_household",
 *   title = @Translation("Simple Household"),
 *   help = @Translation("Simple household access control.")
 * )
 */
class SimpleHousehold extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    $test_value = isset($this->options['test']) ? $this->options['test'] : 'default';
    return $this->t('Simple household access: @value', ['@value' => $test_value]);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['test'] = ['default' => 'test_value'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    \Drupal::logger('household_access')->notice('buildOptionsForm called for Simple Household');
    parent::buildOptionsForm($form, $form_state);

    $form['test'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test field'),
      '#default_value' => $this->options['test'],
      '#description' => $this->t('This is a test field to verify the form is working.'),
    ];

    \Drupal::logger('household_access')->notice('Form array: @form', ['@form' => print_r($form, TRUE)]);
  }

  /**
   * {@inheritdoc}
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    \Drupal::logger('household_access')->notice('validateOptionsForm called');
    parent::validateOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    \Drupal::logger('household_access')->notice('submitOptionsForm called');
    $this->options['test'] = $form_state->getValue('test');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    // No route alterations needed for this access plugin
  }

}
