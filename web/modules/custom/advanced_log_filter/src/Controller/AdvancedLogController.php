<?php

namespace Drupal\advanced_log_filter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Returns responses for advanced log filter routes.
 */
class AdvancedLogController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The pager manager.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected $pagerManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a AdvancedLogController object.
   */
  public function __construct(Connection $database, DateFormatterInterface $date_formatter, FormBuilderInterface $form_builder, PagerManagerInterface $pager_manager, ModuleHandlerInterface $module_handler) {
    $this->database = $database;
    $this->dateFormatter = $date_formatter;
    $this->formBuilder = $form_builder;
    $this->pagerManager = $pager_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('form_builder'),
      $container->get('pager.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Displays a listing of database log messages with advanced filtering.
   */
  public function overview(Request $request) {
    $build['filter'] = $this->formBuilder->getForm('Drupal\advanced_log_filter\Form\AdvancedLogFilterForm');

    $filters = $this->getFiltersFromRequest($request);
    $build['table'] = $this->buildLogTable($filters);

    return $build;
  }

  /**
   * Extract filters from the request.
   */
  protected function getFiltersFromRequest(Request $request) {
    $filters = [];

    // Handle simple scalar values
    $scalar_filters = ['user', 'text_search', 'severity'];
    foreach ($scalar_filters as $filter) {
      $value = $request->query->get($filter);
      if ($value !== null && $value !== '') {
        $filters[$filter] = $value;
      }
    }

    // Handle array values safely
    $exclude_categories = $request->query->all('exclude_categories');
    if (!empty($exclude_categories) && is_array($exclude_categories)) {
      $filters['exclude_categories'] = $exclude_categories;
    }

    // Handle date values - could be strings or arrays depending on form submission
    $date_from = $request->query->get('date_from');
    if (!empty($date_from)) {
      $filters['date_from'] = $date_from;
    }

    $date_to = $request->query->get('date_to');
    if (!empty($date_to)) {
      $filters['date_to'] = $date_to;
    }

    return $filters;
  }

  /**
   * Build the log messages table with filters applied.
   */
  protected function buildLogTable($filters) {
    $header = [
      '',
      ['data' => $this->t('Type'), 'field' => 'w.type', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
      ['data' => $this->t('Date'), 'field' => 'w.timestamp', 'sort' => 'desc', 'class' => [RESPONSIVE_PRIORITY_LOW]],
      ['data' => $this->t('Message'), 'field' => 'w.message'],
      ['data' => $this->t('User'), 'field' => 'ufd.name', 'class' => [RESPONSIVE_PRIORITY_MEDIUM]],
      ['data' => $this->t('Operations'), 'class' => [RESPONSIVE_PRIORITY_LOW]],
    ];

    $query = $this->database->select('watchdog', 'w')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender');

    $query->fields('w', [
      'wid',
      'uid',
      'severity',
      'type',
      'timestamp',
      'message',
      'variables',
      'link',
    ]);

    $query->leftJoin('users_field_data', 'ufd', '[w].[uid] = [ufd].[uid] AND [ufd].[default_langcode] = 1');
    $query->fields('ufd', ['name', 'uid']);

    // Apply filters
    $this->applyFilters($query, $filters);

    $result = $query
      ->limit(50)
      ->orderByHeader($header)
      ->execute();

    $severity = $this->getSeverityLevels();
    $rows = [];

    foreach ($result as $dblog) {
      $message = $this->formatMessage($dblog);
      if ($message && isset($severity[$dblog->severity])) {
        $title = $this->t('Severity level: @level', ['@level' => $severity[$dblog->severity]['title']]);

        // Create a proper account object for the username theme
        $account = NULL;
        if ($dblog->uid) {
          $account = User::load($dblog->uid);
        }

        // If we couldn't load the user, create a minimal account object
        if (!$account) {
          $account = (object) [
            'uid' => $dblog->uid,
            'name' => $dblog->name ?: $this->t('Anonymous'),
          ];
        }

        $rows[] = [
          // Cells.
          ['class' => ['icon'], 'data' => ['#markup' => $severity[$dblog->severity]['icon']], 'title' => $title],
          $dblog->type,
          $this->dateFormatter->format($dblog->timestamp, 'short'),
          Link::fromTextAndUrl($message, new Url('dblog.event', ['event_id' => $dblog->wid], [
            'attributes' => [
              'title' => $this->t('View details'),
            ],
          ]))->toString(),
          $account instanceof User ? ['data' => ['#theme' => 'username', '#account' => $account]] : ($account->name ?? $this->t('Anonymous')),
          Link::fromTextAndUrl($this->t('View'), new Url('dblog.event', ['event_id' => $dblog->wid]))->toString(),
        ];
      }
    }

    $build['dblog_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => ['id' => 'admin-dblog', 'class' => ['admin-dblog']],
      '#empty' => $this->t('No log messages available.'),
      '#attached' => [
        'library' => ['dblog/drupal.dblog'],
      ],
    ];
    $build['dblog_pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Apply filters to the database query.
   */
  protected function applyFilters($query, $filters) {
    // Date from filter
    if (!empty($filters['date_from'])) {
      $date_from = strtotime($filters['date_from'] . ' 00:00:00');
      if ($date_from !== FALSE) {
        $query->condition('w.timestamp', $date_from, '>=');
      }
    }

    // Date to filter
    if (!empty($filters['date_to'])) {
      $date_to = strtotime($filters['date_to'] . ' 23:59:59');
      if ($date_to !== FALSE) {
        $query->condition('w.timestamp', $date_to, '<=');
      }
    }

    // User filter
    if (!empty($filters['user'])) {
      if (is_numeric($filters['user'])) {
        $query->condition('w.uid', $filters['user']);
      } else {
        // Search by username
        $query->condition('ufd.name', $filters['user'], 'CONTAINS');
      }
    }

    // Exclude categories filter
    if (!empty($filters['exclude_categories']) && is_array($filters['exclude_categories'])) {
      $query->condition('w.type', $filters['exclude_categories'], 'NOT IN');
    }

    // Text search filter
    if (!empty($filters['text_search'])) {
      $query->condition('w.message', '%' . $this->database->escapeLike($filters['text_search']) . '%', 'LIKE');
    }

    // Severity filter
    if (!empty($filters['severity']) && is_array($filters['severity'])) {
      $query->condition('w.severity', $filters['severity'], 'IN');
    }
  }

  /**
   * Format a database log message.
   */
  protected function formatMessage($row) {
    // Legacy messages and user specified text.
    if ($row->variables === 'N;') {
      return $row->message;
    }
    // Message to translate with injected variables.
    else {
      $variables = @unserialize($row->variables);
      if (is_array($variables)) {
        return $this->t($row->message, $variables);
      }
      else {
        return $row->message;
      }
    }
  }

  /**
   * Get severity levels with icons.
   */
  protected function getSeverityLevels() {
    return [
      RfcLogLevel::EMERGENCY => [
        'title' => $this->t('Emergency'),
        'icon' => '🔴',
      ],
      RfcLogLevel::ALERT => [
        'title' => $this->t('Alert'),
        'icon' => '🟠',
      ],
      RfcLogLevel::CRITICAL => [
        'title' => $this->t('Critical'),
        'icon' => '🔴',
      ],
      RfcLogLevel::ERROR => [
        'title' => $this->t('Error'),
        'icon' => '🔴',
      ],
      RfcLogLevel::WARNING => [
        'title' => $this->t('Warning'),
        'icon' => '🟡',
      ],
      RfcLogLevel::NOTICE => [
        'title' => $this->t('Notice'),
        'icon' => '🔵',
      ],
      RfcLogLevel::INFO => [
        'title' => $this->t('Info'),
        'icon' => '🟢',
      ],
      RfcLogLevel::DEBUG => [
        'title' => $this->t('Debug'),
        'icon' => '⚪',
      ],
    ];
  }

}
