<?php

namespace Drupal\household_access\Plugin\views\access;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Alternative test access plugin.
 *
 * @ViewsAccess(
 *   id = "alternative_test",
 *   title = @Translation("Alternative Test"),
 *   help = @Translation("Alternative test plugin.")
 * )
 */
class AlternativeTest extends AccessPluginBase {

  /**
   * Overrides Drupal\views\Plugin\views\access\AccessPluginBase::summaryTitle().
   */
  public function summaryTitle() {
    return $this->t('Alternative test access');
  }

  /**
   * Overrides Drupal\views\Plugin\views\PluginBase::defineOptions().
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['my_setting'] = ['default' => 'default_value'];
    return $options;
  }

  /**
   * Provide the default form for setting options.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['description'] = [
      '#markup' => '<p>' . $this->t('Configure the alternative test plugin.') . '</p>',
    ];

    $form['my_setting'] = [
      '#title' => $this->t('My Setting'),
      '#type' => 'textfield',
      '#description' => $this->t('Enter a test value.'),
      '#default_value' => $this->options['my_setting'],
    ];
  }

  /**
   * Perform any necessary changes to the form values prior to storage.
   */
  public function submitOptionsForm(&$form, FormStateInterface $form_state) {
    $this->options['my_setting'] = $form_state->getValue('my_setting');
  }

  /**
   * Determine if the current user has access or not.
   */
  public function access(AccountInterface $account) {
    // Always allow access for testing
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    // No route alterations needed
  }

}
