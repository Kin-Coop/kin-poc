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

    public function usesOptions() {
      return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function summaryTitle() {
      return $this->t('Simple household access');
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
      parent::buildOptionsForm($form, $form_state);

      $form['test'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Test field'),
        '#default_value' => $this->options['test'],
      ];
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
