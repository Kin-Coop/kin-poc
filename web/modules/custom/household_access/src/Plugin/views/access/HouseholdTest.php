<?php

namespace Drupal\household_access\Plugin\views\access;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;

/**
 * Test access plugin.
 *
 * @ViewsAccess(
 *   id = "household_test",
 *   title = @Translation("Household Test"),
 *   help = @Translation("Test plugin for debugging.")
 * )
 */
class HouseholdTest extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['test_setting'] = ['default' => 'default_value'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['test_setting'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test Setting'),
      '#description' => $this->t('This is a test setting.'),
      '#default_value' => $this->options['test_setting'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return TRUE;
  }

}
