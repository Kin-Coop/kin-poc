<?php

namespace Drupal\opencollective_api\Form;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\Collective;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\CollectiveEvents;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\CollectiveMembers;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\CollectiveTiers;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\LoggedInAccount;
use Drupal\opencollective_api\Service\ApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a OpenCollective GraphQL Client form.
 */
class TestForm extends FormBase {

  /**
   * Client.
   *
   * @var \Drupal\opencollective_api\Service\ApiClient
   */
  private ApiClient $graphQLClient;

  /**
   * Constructor.
   *
   * @param \Drupal\opencollective_api\Service\ApiClient $graphQLClient
   *   Client.
   */
  public function __construct(ApiClient $graphQLClient) {
    $this->graphQLClient = $graphQLClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('opencollective_api.client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opencollective_api_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['auth_tests'] = [
      '#type' => 'container',
      'actions' => [
        '#type' => 'actions',
        'loggedin_account' => [
          '#type' => 'submit',
          '#value' => $this->t('Get Logged In Account'),
        ],
      ],
    ];

    $form['collective_tests'] = [
      '#type' => 'details',
      '#title' => $this->t('Collective Tests'),
      '#open' => TRUE,
      'collective_slug' => [
        '#type' => 'textfield',
        '#title' => $this->t('Get Collective (slug)'),
        '#description' => $this->t('* Required for the below test actions.'),
      ],
      'actions' => [
        '#type' => 'actions',
        'get_collective' => [
          '#type' => 'submit',
          '#value' => $this->t('Get Collective'),
        ],
        'get_collective_tiers' => [
          '#type' => 'submit',
          '#value' => $this->t('Get Collective Tiers'),
        ],
        'get_collective_backers' => [
          '#type' => 'submit',
          '#value' => $this->t('Get Collective Backers'),
        ],
        'get_collective_events' => [
          '#type' => 'submit',
          '#value' => $this->t('Get Collective Events'),
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (str_starts_with($this->getTriggerButton($form_state), 'get_collective') && !$form_state->getValue('collective_slug')) {
      $form_state->setErrorByName('collective_slug', $this->t('Collective slug is required for performing the collective test actions.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $trigger_button = $this->getTriggerButton($form_state);
    $collective_slug = $form_state->getValue('collective_slug');

    switch ($trigger_button) {
      case 'loggedin_account':
        $query = $this->graphQLClient->queryPluginManager()->createInstance(LoggedInAccount::PLUGIN_ID);
        $result = $this->graphQLClient->performQuery($query);
        $message = $this->valueToSafeMarkup($result);
        break;

      case 'get_collective':
        $query = $this->graphQLClient->queryPluginManager()->createInstance(Collective::PLUGIN_ID);
        $result = $this->graphQLClient->performQuery($query, [
          'collective_slug' => $collective_slug,
        ]);
        $message = $this->valueToSafeMarkup($result);
        break;

      case 'get_collective_tiers':
        $query = $this->graphQLClient->queryPluginManager()->createInstance(CollectiveTiers::PLUGIN_ID);
        $result = $this->graphQLClient->performQuery($query, [
          'collective_slug' => $collective_slug,
        ]);
        $message = $this->valueToSafeMarkup($result);
        break;

      case 'get_collective_backers':
        $query = $this->graphQLClient->queryPluginManager()->createInstance(CollectiveMembers::PLUGIN_ID);
        $result = $this->graphQLClient->performQuery($query, [
          'collective_slug' => $collective_slug,
        ]);
        $message = $this->valueToSafeMarkup($result);
        break;

      case 'get_collective_events':
        $query = $this->graphQLClient->queryPluginManager()->createInstance(CollectiveEvents::PLUGIN_ID);
        $result = $this->graphQLClient->performQuery($query, [
          'collective_slug' => $collective_slug,
        ]);
        $message = $this->valueToSafeMarkup($result);
        break;

      default:
        $message = $this->t('No operation run.');
        break;
    }

    $this->messenger()->addStatus($message);
  }

  /**
   * Get the ID of the button that triggered form submission.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return string
   *   Hopefully the id of the button.
   */
  private function getTriggerButton(FormStateInterface $form_state): string {
    $trigger = $form_state->getTriggeringElement();
    $trigger_button = 'submit';
    if (isset($trigger['#parents'], $trigger['#parents'][0])) {
      $trigger_button = $trigger['#parents'][0];
    }

    return (string) $trigger_button;
  }

  /**
   * Convert any value to safe markup for formatted messages.
   *
   * @param mixed $value
   *   Any value.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   Markup string.
   */
  private function valueToSafeMarkup($value): MarkupInterface {
    ob_start();
    if (function_exists('dump')) {
      dump($value);
    }
    else {
      print "<pre>" . var_export($value, 1) . "</pre>";
    }
    $string = ob_get_clean();
    return Markup::create($string);
  }

}
