<?php

namespace Drupal\advanced_log_filter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LogLevel;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Advanced log filter form.
 */
class AdvancedLogFilterForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new AdvancedLogFilterForm object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'advanced_log_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filters'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Filter Options'),
      '#collapsible' => FALSE,
    ];

    // Date range filter
    $form['filters']['date_from'] = [
      '#type' => 'datetime',
      '#title' => $this->t('From Date'),
      '#default_value' => $form_state->getValue('date_from'),
    ];

    $form['filters']['date_to'] = [
      '#type' => 'datetime',
      '#title' => $this->t('To Date'),
      '#default_value' => $form_state->getValue('date_to'),
    ];

    // Severity filter
    $form['filters']['severity'] = [
      '#type' => 'select',
      '#title' => $this->t('Severity'),
      '#options' => [
        '' => $this->t('- Any -'),
        RfcLogLevel::EMERGENCY => $this->t('Emergency'),
        RfcLogLevel::ALERT => $this->t('Alert'),
        RfcLogLevel::CRITICAL => $this->t('Critical'),
        RfcLogLevel::ERROR => $this->t('Error'),
        RfcLogLevel::WARNING => $this->t('Warning'),
        RfcLogLevel::NOTICE => $this->t('Notice'),
        RfcLogLevel::INFO => $this->t('Info'),
        RfcLogLevel::DEBUG => $this->t('Debug'),
      ],
      '#default_value' => $form_state->getValue('severity', ''),
    ];

    // Type/Module filter
    $form['filters']['type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Type/Module'),
      '#description' => $this->t('Filter by log type or module name.'),
      '#default_value' => $form_state->getValue('type', ''),
    ];

    // Message filter
    $form['filters']['message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Search within log messages.'),
      '#default_value' => $form_state->getValue('message', ''),
    ];

    // User filter
    $form['filters']['uid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('User'),
      '#target_type' => 'user',
      '#description' => $this->t('Filter by user who triggered the log entry.'),
      '#default_value' => $form_state->getValue('uid') ? \Drupal\user\Entity\User::load($form_state->getValue('uid')) : NULL,
    ];

    // IP address filter
    $form['filters']['hostname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('IP Address'),
      '#description' => $this->t('Filter by IP address.'),
      '#default_value' => $form_state->getValue('hostname', ''),
    ];

    $form['filters']['actions'] = [
      '#type' => 'actions',
    ];

    $form['filters']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply Filters'),
    ];

    $form['filters']['actions']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#submit' => ['::resetForm'],
    ];

    // Display results if form has been submitted
    if ($form_state->isSubmitted() && $form_state->getValue('op') !== $this->t('Reset')) {
      $form['results'] = $this->buildResultsTable($form_state);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form state is maintained automatically
    $form_state->setRebuild(TRUE);
  }

  /**
   * Reset form handler.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValues([]);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Builds the results table based on current filters.
   */
  protected function buildResultsTable(FormStateInterface $form_state) {
    $query = $this->database->select('watchdog', 'w');
    $query->fields('w');

    // Apply filters
    $this->applyFilters($query, $form_state);

    // Add paging
    $query = $query->extend('\Drupal\Core\Database\Query\PagerSelectExtender');
    $query->limit(50);

    // Order by timestamp descending
    $query->orderBy('timestamp', 'DESC');

    $results = $query->execute()->fetchAll();

    if (empty($results)) {
      return [
        '#markup' => '<p>' . $this->t('No log entries found matching the specified criteria.') . '</p>',
      ];
    }

    $header = [
      $this->t('Date'),
      $this->t('Type'),
      $this->t('Severity'),
      $this->t('Message'),
      $this->t('User'),
      $this->t('Location'),
    ];

    $rows = [];
    foreach ($results as $result) {
      $severity_labels = [
        RfcLogLevel::EMERGENCY => $this->t('Emergency'),
        RfcLogLevel::ALERT => $this->t('Alert'),
        RfcLogLevel::CRITICAL => $this->t('Critical'),
        RfcLogLevel::ERROR => $this->t('Error'),
        RfcLogLevel::WARNING => $this->t('Warning'),
        RfcLogLevel::NOTICE => $this->t('Notice'),
        RfcLogLevel::INFO => $this->t('Info'),
        RfcLogLevel::DEBUG => $this->t('Debug'),
      ];

      $user = $result->uid ? \Drupal\user\Entity\User::load($result->uid) : NULL;
      $username = $user ? $user->getDisplayName() : $this->t('Anonymous');

      // Unserialize variables and format message
      $variables = $result->variables ? unserialize($result->variables) : [];
      $message = $result->message;
      if (!empty($variables)) {
        $message = strtr($message, $variables);
      }

      $rows[] = [
        \Drupal::service('date.formatter')->format($result->timestamp, 'short'),
        $result->type,
        $severity_labels[$result->severity] ?? $this->t('Unknown'),
        [
          'data' => [
            '#markup' => substr(strip_tags($message), 0, 100) . (strlen($message) > 100 ? '...' : ''),
          ],
        ],
        $username,
        $result->location ? substr($result->location, 0, 50) . '...' : '',
      ];
    }

    $build = [];
    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No log entries found.'),
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

  /**
   * Applies filters to the database query.
   */
  protected function applyFilters($query, FormStateInterface $form_state) {
    // Date range filter
    if ($date_from = $form_state->getValue('date_from')) {
      if ($date_from instanceof \Drupal\Core\Datetime\DrupalDateTime) {
        $query->condition('timestamp', $date_from->getTimestamp(), '>=');
      }
    }

    if ($date_to = $form_state->getValue('date_to')) {
      if ($date_to instanceof \Drupal\Core\Datetime\DrupalDateTime) {
        $query->condition('timestamp', $date_to->getTimestamp(), '<=');
      }
    }

    // Severity filter
    if ($severity = $form_state->getValue('severity')) {
      if ($severity !== '') {
        $query->condition('severity', $severity);
      }
    }

    // Type filter
    if ($type = $form_state->getValue('type')) {
      $query->condition('type', '%' . $type . '%', 'LIKE');
    }

    // Message filter
    if ($message = $form_state->getValue('message')) {
      $query->condition('message', '%' . $message . '%', 'LIKE');
    }

    // User filter
    if ($uid = $form_state->getValue('uid')) {
      if (is_object($uid)) {
        $uid = $uid->id();
      }
      $query->condition('uid', $uid);
    }

    // IP address filter
    if ($hostname = $form_state->getValue('hostname')) {
      $query->condition('hostname', '%' . $hostname . '%', 'LIKE');
    }
  }

}
