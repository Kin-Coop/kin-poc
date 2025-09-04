<?php

  namespace Drupal\household_access\Plugin\views\access;

  use Drupal\Core\Form\FormStateInterface;
  use Drupal\Core\Session\AccountInterface;
  use Drupal\views\Plugin\views\access\AccessPluginBase;
  use Symfony\Component\Routing\Route;

  /**
   * Access plugin that provides household-based access control.
   *
   * @ingroup views_access_plugins
   *
   * @ViewsAccess(
   *   id = "household_working",
   *   title = @Translation("Household Working"),
   *   help = @Translation("Access will be granted based on household membership.")
   * )
   */
  class HouseholdWorking extends AccessPluginBase {

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
        '#description' => $this->t('Users with this permission will have full access.'),
        '#default_value' => $this->options['bypass_permission'],
        '#size' => 40,
      ];
    }

    /**
     * {@inheritdoc}
     */
    public function access(AccountInterface $account) {
      // For testing, let's just log and return TRUE
      \Drupal::logger('household_access')->notice('Access method called for user @uid', ['@uid' => $account->id()]);
      return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function alterRouteDefinition(Route $route) {
      // No route alterations needed
    }

    /**
     * {@inheritdoc}
     */
    public function summaryTitle() {
      return $this->t('Household working: @arg with bypass @perm', [
        '@arg' => $this->options['household_argument'],
        '@perm' => $this->options['bypass_permission'],
      ]);
    }

  }
