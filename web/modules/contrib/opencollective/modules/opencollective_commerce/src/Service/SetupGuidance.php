<?php

namespace Drupal\opencollective_commerce\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\opencollective_api\Service\ApiClient;
use Drupal\opencollective_commerce\Plugin\Commerce\PaymentGateway\ContributionFlowPaymentGateway;

/**
 * Service for helping set up an Open Collective store.
 */
class SetupGuidance implements SetupGuidanceInterface {

  use StringTranslationTrait;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\opencollective_api\Service\ApiClient $apiClient
   *   Open Collective api client.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirectDestination
   *   Redirect destination.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   Field manager.
   */
  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private ConfigFactoryInterface $configFactory,
    private ApiClient $apiClient,
    private RedirectDestinationInterface $redirectDestination,
    private EntityFieldManagerInterface $entityFieldManager,
  ) {}

  /**
   * Gather and test all requirements.
   *
   * @return array
   *   Requirements for templating.
   */
  public function gatherRequirements(): array {
    return [
      'api_client' => $this->requireApiClient(),
      'commerce_order_type' => $this->requireCommerceOrderType(),
      'commerce_order_item_type' => $this->requireCommerceOrderItemType(),
      'commerce_store_type' => $this->requireCommerceStoreType(),
      'commerce_store' => $this->requireCommerceStoreOfType(),
      'commerce_store_field' => $this->requireCommerceStoreCollectiveField(),
      'commerce_payment_gateway' => $this->requireCommercePaymentGateway(),
      'event_product_type' => $this->recommendEventTicketProduct(),
      'sync_event_products' => $this->recommendSyncEventProducts(),
    ];
  }

  /**
   * Open Collective api client is ready.
   *
   * @return array
   *   Requirement.
   */
  protected function requireApiClient(): array {
    $requirement = [
      'title' => $this->t('Api Client'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('The Api Client is required for Commerce integration. Please ensure it has a working API key.'),
        '#action_title' => $this->t('Configure Client'),
        '#action_url' => Url::fromRoute('opencollective_api.settings_form', [], [
          'query' => $this->redirectDestination->getAsArray(),
        ]),
      ],
    ];

    if ($this->apiClient->isReady()) {
      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Api Client is Ready.');
    }

    return $requirement;
  }

  /**
   * Require a special order type.
   *
   * @return array
   *   Requirement.
   */
  protected function requireCommerceOrderType(): array {
    $requirement = [
      'title' => $this->t('Order Type'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Order type does not exist.'),
        '#action_title' => $this->t('Create Order Type'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'setup_order_type',
        ]),
      ],
    ];

    $found = $this->entityTypeManager->getStorage('commerce_order_type')->load(SetupGuidanceInterface::COMMERCE_ORDER_TYPE);

    if ($found) {
      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Open Collective order type exists.');
    }

    return $requirement;
  }

  /**
   * Require our special order item type.
   *
   * @return array
   *   Requirement.
   */
  protected function requireCommerceOrderItemType(): array {
    $requirement = [
      'title' => $this->t('Order Item Type'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Order item type does not exist..'),
        '#action_title' => $this->t('Create Order Item Type'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'setup_order_item_type',
        ]),
      ],
    ];

    $found = $this->entityTypeManager->getStorage('commerce_order_item_type')->load(SetupGuidanceInterface::COMMERCE_ORDER_ITEM_TYPE);

    if ($found) {
      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Open Collective order item type exists.');
    }

    return $requirement;
  }

  /**
   * Commerce store type exists.
   *
   * @return array
   *   Requirement.
   */
  protected function requireCommerceStoreType(): array {
    $requirement = [
      'title' => $this->t('Store Type'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Commerce store_type "@type" does not exist.', [
          '@type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        ]),
        '#action_title' => $this->t('Setup Store Type'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'setup_store_type',
        ]),
      ],
    ];

    $found = $this->entityTypeManager->getStorage('commerce_store_type')->loadByProperties([
      'id' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
    ]);

    if ($found) {
      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Commerce store type "@type" found.', [
        '@type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
      ]);
    }

    return $requirement;
  }

  /**
   * Commerce store is setup.
   *
   * @return array
   *   Requirement.
   */
  protected function requireCommerceStoreOfType(): array {
    $requirement = [
      'title' => $this->t('Store(s)'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Commerce store(s) of the type "@type" were not found.', [
          '@type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        ]),
        '#action_title' => $this->t('Create Store'),
        '#action_url' => Url::fromRoute('entity.commerce_store.add_form', [
          'commerce_store_type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        ], [
          'query' => $this->redirectDestination->getAsArray(),
        ]),
      ],
    ];

    $found = $this->entityTypeManager->getStorage('commerce_store')->loadByProperties([
      'type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
    ]);

    if ($found) {
      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Commerce store(s) of the type "@type" found.', [
        '@type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
      ]);
    }
    return $requirement;
  }

  /**
   * Commerce store is setup.
   *
   * @return array
   *   Requirement.
   */
  protected function requireCommerceStoreCollectiveField(): array {
    $requirement = [
      'title' => $this->t('Store(s) Collective Slug Field'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => $this->t('Commerce store(s) of type "@store_type" not configured with a field name "@field_name" of the type "@field_type".', [
        '@store_type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        '@field_name' => SetupGuidanceInterface::COMMERCE_STORE_COLLECTIVE_FIELD,
        '@field_type' => SetupGuidanceInterface::COMMERCE_STORE_COLLECTIVE_FIELD_TYPE,
      ]),
    ];

    /** @var \Drupal\commerce_store\Entity\Store[] $stores */
    $stores = $this->entityTypeManager->getStorage('commerce_store')->loadByProperties([
      'type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
    ]);

    $all_stores_have_field = !empty($stores);
    foreach ($stores as $store) {
      /** @var \Drupal\field\Entity\FieldConfig $field */
      $field = $store->getFieldDefinitions()[SetupGuidanceInterface::COMMERCE_STORE_COLLECTIVE_FIELD] ?? NULL;
      if (!$field || $field->getType() !== SetupGuidanceInterface::COMMERCE_STORE_COLLECTIVE_FIELD_TYPE) {
        $all_stores_have_field = FALSE;
        break;
      }
    }

    if ($all_stores_have_field) {
      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Commerce stores of type "@store_type" have field "@field_name" of the type "@field_type".', [
        '@store_type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        '@field_name' => SetupGuidanceInterface::COMMERCE_STORE_COLLECTIVE_FIELD,
        '@field_type' => SetupGuidanceInterface::COMMERCE_STORE_COLLECTIVE_FIELD_TYPE,
      ]);
    }

    return $requirement;
  }

  /**
   * Commerce payment gateway exists and setup for the store.
   *
   * @return array
   *   Requirement.
   */
  protected function requireCommercePaymentGateway(): array {
    $requirement = [
      'title' => $this->t('Payment Gateway'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Payment gateway of the type "@type" was not found.', [
          '@type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        ]),
        '#action_title' => $this->t('Create Payment Gateway'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'setup_payment_gateway',
        ]),
      ],
    ];

    $found_payment_gateway = $this->entityTypeManager->getStorage('commerce_payment_gateway')->loadByProperties([
      'plugin' => ContributionFlowPaymentGateway::PLUGIN_ID,
    ]);

    if ($found_payment_gateway) {
      /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway */
      $payment_gateway = reset($found_payment_gateway);
      $order_store_condition = NULL;
      $requirement['description'] = [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Payment gateway of the type "@plugin" found, but it is not configured to be limited to only stores of type "@type".', [
          '@plugin' => ContributionFlowPaymentGateway::PLUGIN_ID,
          '@type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        ]),
        '#action_title' => $this->t('Configure Payment Gateway'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'configure_payment_gateway',
        ]),
      ];

      // Check the payment gateway conditions to ensure it is limited to an
      // opencollective_commerce_store.
      foreach ($payment_gateway->getConditions() as $condition) {
        if ($condition->getPluginId() === 'order_store') {
          $order_store_condition = $condition;
          break;
        }
      }

      if (!$order_store_condition) {
        return $requirement;
      }

      $stores = $order_store_condition->getConfiguration()['stores'] ?? [];
      if (!$stores) {
        return $requirement;
      }

      $all_stores_limited_correctly = TRUE;
      foreach ($stores as $store_uuid) {
        $found_condition_store = $this->entityTypeManager->getStorage('commerce_store')->loadByProperties([
          'uuid' => $store_uuid,
          'type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
        ]);

        if (!$found_condition_store) {
          $all_stores_limited_correctly = FALSE;
          break;
        }
      }

      if (!$all_stores_limited_correctly) {
        return $requirement;
      }

      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Payment gateway of the type "@plugin" found and configured to be limited to only stores of the type "@type".', [
        '@plugin' => ContributionFlowPaymentGateway::PLUGIN_ID,
        '@type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
      ]);
    }
    return $requirement;
  }

  /**
   * Commerce product types are setup.
   *
   * @return array
   *   Requirement.
   */
  protected function recommendEventTicketProduct(): array {
    $requirement = [
      'title' => $this->t('Event Ticket Product Variation Type'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_ERROR,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Product variation type "@type" does not exist.', [
          '@type' => SetupGuidanceInterface::COMMERCE_EVENT_TICKET_PRODUCT_VARIATION_TYPE,
        ]),
        '#action_title' => $this->t('Create Event Ticket Product Variation Type'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'setup_event_ticket_product_variation_type',
        ]),
      ],
    ];

    // Look for variant type.
    $found_variation_type = $this->entityTypeManager->getStorage('commerce_product_variation_type')->loadByProperties([
      'id' => SetupGuidanceInterface::COMMERCE_EVENT_TICKET_PRODUCT_VARIATION_TYPE,
    ]);

    if ($found_variation_type) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationTypeInterface $found_variation_type */
      $found_variation_type = reset($found_variation_type);

      // Next, look for product type.
      $requirement['description'] = [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('Product type "@type" does not exist.', [
          '@type' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE,
        ]),
        '#action_title' => $this->t('Create Event ProductType'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'setup_event_product_type',
        ]),
      ];

      $found_product_type = $this->entityTypeManager->getStorage('commerce_product_type')->loadByProperties([
        'id' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE,
      ]);

      if ($found_product_type) {
        /** @var \Drupal\commerce_product\Entity\ProductTypeInterface $found_product_type */
        $found_product_type = reset($found_product_type);

        // Next, look for event slug field.
        $requirement['description'] = [
          '#theme' => 'opencollective_commerce_status_action',
          '#description' => $this->t('Product type "@type" does not have an Open Collective Slug field named "@field_name". You will need to manually create the field. Add a new field of the type "Open Collective Slug" with the machine name "@field_name" to the product type "@type".', [
            '@type' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE,
            '@field_name' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE_SLUG_FIELD,
          ]),
          '#action_title' => $this->t('Edit Event Product Type Fields'),
          '#action_url' => Url::fromRoute('entity.commerce_product_type.edit_form', [
            'commerce_product_type' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE,
          ], [
            'query' => $this->redirectDestination->getAsArray(),
          ]),
        ];

        $field = $this->entityFieldManager->getFieldDefinitions('commerce_product', SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE)[SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE_SLUG_FIELD] ?? NULL;
        if ($field) {
          $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
          $requirement['description'] = $this->t('Product variation type "@variation_type" exists. And product type "@product_type" has field "@field_name" of the type "@field_type".', [
            '@variation_type' => SetupGuidanceInterface::COMMERCE_EVENT_TICKET_PRODUCT_VARIATION_TYPE,
            '@product_type' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE,
            '@field_name' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE_SLUG_FIELD,
            '@field_type' => SetupGuidanceInterface::COMMERCE_STORE_COLLECTIVE_FIELD_TYPE,
          ]);
        }
      }
    }

    return $requirement;
  }

  /**
   * Recommend sync events from Open Collective into commerce.
   *
   * @return array
   *   Requirement.
   */
  public function recommendSyncEventProducts(): array {
    $requirement = [
      'title' => $this->t('Event Products'),
      'severity' => SetupGuidanceInterface::REQUIREMENT_WARNING,
      'description' => [
        '#theme' => 'opencollective_commerce_status_action',
        '#description' => $this->t('There are no event products or ticket variations created.'),
        '#action_title' => $this->t('Sync Events'),
        '#action_url' => Url::fromRoute('opencollective_commerce.dashboard_setup_actions', [
          'action_id' => 'sync_event_products',
        ]),
      ],
    ];

    $event_products = $this->entityTypeManager->getStorage('commerce_product')->loadByProperties([
      'type' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE,
    ]);

    if ($event_products) {
      $requirement['severity'] = SetupGuidanceInterface::REQUIREMENT_OK;
      $requirement['description'] = $this->t('Event products exist.');
    }

    return $requirement;
  }

}
