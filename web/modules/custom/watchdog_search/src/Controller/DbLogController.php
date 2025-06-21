<?php

declare(strict_types = 1);

namespace Drupal\watchdog_search\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Url;
use Drupal\dblog\Controller\DbLogController as DbLogControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a custom implementation of the Watchdog log messages page.
 */
class DbLogController extends DbLogControllerBase implements FormInterface {

  /**
   * The current request.
   */
  protected Request $currentRequest;

  /**
   * Constructs a DbLogController object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   A database connection.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   A module handler.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(Connection $database, ModuleHandlerInterface $module_handler, DateFormatterInterface $date_formatter, FormBuilderInterface $form_builder, RequestStack $requestStack) {
    parent::__construct($database, $module_handler, $date_formatter, $form_builder);
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('database'),
      $container->get('module_handler'),
      $container->get('date.formatter'),
      $container->get('form_builder'),
      $container->get('request_stack'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'watchdog_search_db_log';
  }

  /**
   * Builds and returns the renderable array for the page.
   *
   * @return array
   *   A renderable array representing the content of the page.
   */
  public function buildPage(): array {
    $build['#attached']['library'][] = 'dblog/drupal.dblog';
    $build['form'] = $this->formBuilder()->getForm($this);

    $header = $this->buildHeader();
    $rows = $this->buildRows($header);

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No log messages found.'),
      '#attributes' => [
        'class' => ['indicia-suite-overview-table', 'admin-dblog'],
      ],
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attributes']['class'][] = 'watchdog-search-form';
    $form['#attached']['library'][] = 'watchdog_search/watchdog_search';

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#default_value' => $this->getQueryParam('search'),
      '#placeholder' => $this->t('Search'),
      '#size' => 32,
      '#attributes' => ['autofocus' => 'autofocus'],
    ];

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => _dblog_get_message_types(),
      '#default_value' => $this->getQueryParam('type', TRUE),
      '#placeholder' => $this->t('Type'),
      '#multiple' => TRUE,
      '#sort_options' => TRUE,
    ];

    $form['severity'] = [
      '#type' => 'select',
      '#title' => $this->t('Severity'),
      '#options' => RfcLogLevel::getLevels(),
      '#default_value' => $this->getQueryParam('severity', TRUE),
      '#placeholder' => $this->t('Severity'),
      '#multiple' => TRUE,
      '#sort_options' => TRUE,
    ];

    $form['from'] = [
      '#type' => 'datetime',
      '#title' => $this->t('From', [], ['context' => 'time']),
      '#default_value' => ($from = $this->getQueryParam('from')) ? new DrupalDateTime($from) : NULL,
      '#attributes' => [
        'data-placeholder' => $this->t('From', [], ['context' => 'time']),
      ],
    ];

    $form['to'] = [
      '#type' => 'datetime',
      '#title' => $this->t('To', [], ['context' => 'time']),
      '#default_value' => ($to = $this->getQueryParam('to')) ? new DrupalDateTime($to) : NULL,
      '#attributes' => [
        'data-placeholder' => $this->t('To', [], ['context' => 'time']),
      ],
    ];

    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['watchdog-search-actions'],
      ],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply filters'),
    ];

    $form['actions']['clear'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset filters'),
      '#submit' => ['::clearFilters'],
      '#attributes' => [
        'title' => $this->t('Reset filters to their default value.'),
      ],
    ];

    return $form;
  }

  /**
   * Build the table header.
   *
   * @return array
   *   An array containing the table header.
   */
  protected function buildHeader(): array {
    return [
      '',
      [
        'data' => $this->t('ID'),
        'field' => 'watchdog.wid',
        'sort' => 'desc',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      [
        'data' => $this->t('Type'),
        'field' => 'watchdog.type',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Date'),
        'field' => 'watchdog.timestamp',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      $this->t('Message'),
      [
        'data' => $this->t('User'),
        'field' => 'users_field_data.name',
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ],
      [
        'data' => $this->t('Operations'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      [
        'data' => $this->t('Link'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];
  }

  /**
   * Build the table rows.
   *
   * @param array $header
   *   An array of table headers used for table sorting.
   *
   * @return array
   *   An array containing the table rows.
   */
  protected function buildRows(array &$header): array {
    $rows = [];
    foreach ($this->getData($header) as $data) {
      $text = NULL;
      $title = NULL;

      if ($message = (string) $this->formatMessage((object) $data)) {
        $title = Unicode::truncate(Html::decodeEntities(strip_tags($message)), 256, TRUE, TRUE);
        $text = Unicode::truncate($title, 56, TRUE, TRUE);
      }

      $row['data'] = [
        'icon' => [
          'class' => ['icon'],
        ],
        'id' => $data['wid'],
        'type' => $data['type'],
        'date' => $this->formatDate((int) $data['timestamp']),
        'message' => $message ? [
          'data' => [
            '#type' => 'link',
            '#title' => $text,
            '#url' => new Url('dblog.event', ['event_id' => $data['wid']], [
              'attributes' => [
                'title' => $title,
              ],
            ]),
            '#attributes' => [
              'title' => $title,
              'class' => ['use-ajax'],
              'data-dialog-type' => ['modal'],
              'data-dialog-options' => Json::encode([
                'width' => '100%',
                'title' => $this->t('Log message #@wid - @severity', [
                  '@wid' => $data['wid'],
                  '@severity' => match ((int) $data['severity']) {
                    RfcLogLevel::DEBUG => $this->t('Debug'),
                    RfcLogLevel::INFO => $this->t('Info'),
                    RfcLogLevel::NOTICE => $this->t('Notice'),
                    RfcLogLevel::WARNING => $this->t('Warning'),
                    RfcLogLevel::ERROR => $this->t('Error'),
                    RfcLogLevel::CRITICAL => $this->t('Critical'),
                    RfcLogLevel::ALERT => $this->t('Alert'),
                    RfcLogLevel::EMERGENCY => $this->t('Emergency'),
                  },
                ]),
              ]),
            ],
          ],
        ] : [],
        'user' => [
          'data' => [
            '#theme' => 'username',
            '#account' => $this->userStorage->load($data['uid']),
          ],
        ],
        'operations' => [
          'data' => [
            [
              '#markup' => $data['link'],
            ],
          ],
        ],
        'view' => [
          'data' => [
            '#type' => 'link',
            '#title' => $this->t('View'),
            '#url' => new Url('dblog.event', ['event_id' => $data['wid']], [
              'attributes' => [
                'title' => $this->t('View'),
              ],
            ]),
          ],
        ],
      ];

      $row['class'] = array_filter([
        Html::getClass('dblog-' . $data['type']),
        static::getLogLevelClassMap()[$data['severity']] ?? NULL,
      ]);

      $rows[] = $row;
    }
    return $rows;
  }

  /**
   * Retrieve a list of watchdog records.
   *
   * @param array $header
   *   An array of table headers used for table sorting.
   *
   * @return array
   *   An array containing the watchdog records.
   */
  protected function getData(array &$header): array {
    $query = $this->database->select('watchdog', 'watchdog');
    $query = $query->extend(PagerSelectExtender::class);
    $query = $query->extend(TableSortExtender::class);
    $query->fields('watchdog', [
      'wid',
      'uid',
      'severity',
      'type',
      'timestamp',
      'message',
      'variables',
      'link',
    ]);
    $query->leftJoin('users_field_data', 'users_field_data', '[watchdog].[uid] = [users_field_data].[uid]');

    if ($search = $this->getQueryParam('search')) {
      if ($searchTerms = array_filter(explode(' ', trim($search)))) {
        foreach ($searchTerms as $searchTerm) {
          $variations = [
            $searchTerm,
            strtolower($searchTerm),
            strtoupper($searchTerm),
            ucfirst($searchTerm),
            preg_replace('/[^a-zA-Z0-9_ -]/', '', $searchTerm),
            preg_replace('/[^a-zA-Z0-9_ -]/', '', strtolower($searchTerm)),
            preg_replace('/[^a-zA-Z0-9_ -]/', '', strtoupper($searchTerm)),
            preg_replace('/[^a-zA-Z0-9_ -]/', '', ucfirst($searchTerm)),
          ];

          $or = $query->orConditionGroup();
          $or->condition('wid', $searchTerm);
          foreach ($variations as $variation) {
            $or->condition('users_field_data.name', $variation);
            $or->condition('message', '%' . $this->database->escapeLike($variation) . '%', 'LIKE');
            $or->condition('variables', '%' . $this->database->escapeLike($variation) . '%', 'LIKE');
          }
          $query->condition($or);
        }
      }
    }

    if ($type = $this->getQueryParam('type', TRUE)) {
      $query->condition('type', $type, 'IN');
    }

    if ($severity = $this->getQueryParam('severity', TRUE)) {
      $query->condition('severity', $severity, 'IN');
    }

    if ($from = $this->getQueryParam('from')) {
      $dateTime = new DrupalDateTime($from);
      $query->condition('timestamp', $dateTime->getTimestamp(), '>=');
    }

    if ($to = $this->getQueryParam('to')) {
      $dateTime = new DrupalDateTime($to);
      $query->condition('timestamp', $dateTime->getTimestamp(), '<=');
    }

    $query->limit(50);
    $query->orderByHeader($header);

    if ($result = $query->execute()) {
      return $result->fetchAll(\PDO::FETCH_ASSOC);
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // No validation required.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $form_state->cleanValues();

    $form_state->setRedirect('<current>', array_filter($form_state->getValues(), static function ($value): bool {
      return $value !== NULL && $value !== '';
    }));
  }

  /**
   * Submit handler for resetting filter values.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function clearFilters(array &$form, FormStateInterface $form_state): void {
    $form_state->setRedirect('<current>');
  }

  /**
   * Format the given date.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime|int $date
   *   The date to format.
   *
   * @return string
   *   The formatted date.
   */
  protected function formatDate(DrupalDateTime|int $date): string {
    if (!$date instanceof DrupalDateTime) {
      $date = DrupalDateTime::createFromTimestamp($date);
    }
    return $this->dateFormatter->format($date->getTimestamp(), 'short');
  }

  /**
   * Retrieve the query parameter value from the current request object.
   *
   * @param string $key
   *   The query parameter key to retrieve the value for.
   * @param bool $multiple
   *   (Optional) A boolean flag indicating whether multiple values can be
   *   returned for the same query parameter key. Default is FALSE.
   *
   * @return mixed
   *   Returns the value of the specified query parameter.
   */
  protected function getQueryParam(string $key, bool $multiple = FALSE): mixed {
    if (!$this->isDrupal9() && $multiple) {
      return $this->currentRequest->query->all($key);
    }
    return $this->currentRequest->query->get($key);
  }

  /**
   * Determines if the current Drupal core installation is version 9.
   *
   * @return bool
   *   Returns true if the Drupal version is 9, otherwise returns false.
   */
  protected function isDrupal9(): bool {
    return (int) floatval(\Drupal::VERSION) === 9;
  }

}
