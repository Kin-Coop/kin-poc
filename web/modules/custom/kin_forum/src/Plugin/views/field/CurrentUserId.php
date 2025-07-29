<?php

namespace Drupal\kin_forum\Plugin\views\field;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide the current user ID.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("current_user_id")
 */
class CurrentUserId extends FieldPluginBase {

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a CurrentUserId field plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This field doesn't require any query modifications.
    // We don't need to add anything to the query since we're getting
    // the current user ID from the service, not from the database.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // Add any custom options here if needed
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Add a description for the field
    $form['description'] = [
      '#type' => 'item',
      '#title' => $this->t('Current User ID'),
      '#description' => $this->t('This field displays the user ID of the currently logged-in user.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return $this->currentUser->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    return $this->currentUser->id();
  }

  /**
   * {@inheritdoc}
   */
  public function clickSort($order) {
    // This field shouldn't be sortable since it's the same for all rows
    return FALSE;
  }

}
