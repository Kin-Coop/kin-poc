<?php

namespace Drupal\opencollective_commerce\Controller;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Url;
use Drupal\opencollective_commerce\Service\EventSyncManager;
use Drupal\opencollective_commerce\Service\SetupGuidance;
use Drupal\opencollective_commerce\Service\SetupGuidanceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for OpenCollective Commerce Dashboard routes.
 */
class DashboardController extends ControllerBase {

  /**
   * Construct.
   *
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extensionPathResolver
   *   Path resolver.
   * @param \Drupal\opencollective_commerce\Service\SetupGuidance $guidance
   *   Setup guidance.
   * @param \Drupal\opencollective_commerce\Service\EventSyncManager $eventSyncManager
   *   Event sync manager.
   */
  public function __construct(
    private ExtensionPathResolver $extensionPathResolver,
    private SetupGuidance $guidance,
    private EventSyncManager $eventSyncManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.path.resolver'),
      $container->get('opencollective_commerce.setup_guidance'),
      $container->get('opencollective_commerce.event_sync_manager')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {
    $build['requirements'] = [
      '#theme' => 'opencollective_commerce_status_report',
      '#title' => $this->t('Commerce Store Setup'),
      '#requirements' => $this->guidance->gatherRequirements(),
    ];

    return $build;
  }

  /**
   * Perform a setup action.
   *
   * @param string $action_id
   *   Name of the setup action to perform.
   */
  public function performSetupAction(string $action_id) {
    // Instantiate configFactory on controller base.
    $this->config('');

    switch ($action_id) {
      case 'setup_order_type':
        $this->actionSetupOrderType();
        break;

      case 'setup_order_item_type':
        $this->actionSetupOrderItemType();
        break;

      case 'setup_store_type':
        $this->actionSetupStoreType();
        break;

      case 'setup_payment_gateway':
        $this->actionSetupPaymentGateway();
        break;

      case 'configure_payment_gateway':
        $this->actionConfigurePaymentGateway();
        break;

      case 'setup_event_ticket_product_variation_type':
        $this->actionSetupEventTicketProductVariationType();
        break;

      case 'setup_event_product_type':
        $this->actionSetupEventProductType();
        break;

      case 'sync_event_products':
        $this->eventSyncManager->syncEventProducts();
        break;
    }

    $this->messenger()->addMessage("Action {$action_id} performed");
    return new RedirectResponse(Url::fromRoute('opencollective_commerce.dashboard')->toString());
  }

  /**
   * Import order type.
   */
  private function actionSetupOrderType(): void {
    $config_path = DRUPAL_ROOT . "/{$this->extensionPathResolver->getPath('module', 'opencollective_commerce')}/migrations/setup-guidance/order-type/";
    $source = new FileStorage($config_path);

    // Import order_type.
    $this->entityTypeManager()->getStorage('commerce_order_type')
      ->create($source->read('commerce_order.commerce_order_type.opencollective_order'))
      ->save();

    // Form.
    $this->configFactory->getEditable('core.entity_form_display.commerce_order.opencollective_order.default')
      ->setData($source->read('core.entity_form_display.commerce_order.opencollective_order.default'))
      ->save();

    // Display.
    $this->configFactory->getEditable('core.entity_view_display.commerce_order.opencollective_order.default')
      ->setData($source->read('core.entity_view_display.commerce_order.opencollective_order.default'))
      ->save();

    $this->messenger()->addStatus('Open Collective order_type created.');
    $this->actionSetupOrderItemType();
  }

  /**
   * Import order item type.
   */
  private function actionSetupOrderItemType(): void {
    $config_path = DRUPAL_ROOT . "/{$this->extensionPathResolver->getPath('module', 'opencollective_commerce')}/migrations/setup-guidance/order-item-type/";
    $source = new FileStorage($config_path);

    // Import order_type.
    $this->entityTypeManager()->getStorage('commerce_order_item_type')
      ->create($source->read('commerce_order.commerce_order_item_type.opencollective_order_item'))
      ->save();

    // Form default.
    $this->configFactory->getEditable('core.entity_form_display.commerce_order_item.opencollective_order_item.add_to_cart')
      ->setData($source->read('core.entity_form_display.commerce_order_item.opencollective_order_item.add_to_cart'))
      ->save();

    // Form add-to-cart.
    $this->configFactory->getEditable('core.entity_form_display.commerce_order_item.opencollective_order_item.default')
      ->setData($source->read('core.entity_form_display.commerce_order_item.opencollective_order_item.default'))
      ->save();

    // Display.
    $this->configFactory->getEditable('core.entity_view_display.commerce_order_item.opencollective_order_item.default')
      ->setData($source->read('core.entity_view_display.commerce_order_item.opencollective_order_item.default'))
      ->save();

    $this->messenger()->addStatus('Open Collective order_item_type created.');
  }

  /**
   * Import store type.
   */
  private function actionSetupStoreType(): void {
    $config_path = DRUPAL_ROOT . "/{$this->extensionPathResolver->getPath('module', 'opencollective_commerce')}/migrations/setup-guidance/store-type/";
    $source = new FileStorage($config_path);

    // Import store_type.
    $this->entityTypeManager()->getStorage('commerce_store_type')
      ->create($source->read('commerce_store.commerce_store_type.opencollective_commerce_store'))
      ->save();

    // Import slug field storage.
    $this->entityTypeManager()->getStorage('field_storage_config')
      ->create($source->read('field.storage.commerce_store.field_collective_slug'))
      ->save();

    // Import slug field instance.
    $this->entityTypeManager()->getStorage('field_config')
      ->create($source->read('field.field.commerce_store.opencollective_commerce_store.field_collective_slug'))
      ->save();

    // Form.
    $this->configFactory->getEditable('core.entity_form_display.commerce_store.opencollective_commerce_store.default')
      ->setData($source->read('core.entity_form_display.commerce_store.opencollective_commerce_store.default'))
      ->save();

    // Display.
    $this->configFactory->getEditable('core.entity_view_display.commerce_store.opencollective_commerce_store.default')
      ->setData($source->read('core.entity_view_display.commerce_store.opencollective_commerce_store.default'))
      ->save();

    $this->messenger()->addStatus('Open Collective commerce store type created.');
  }

  /**
   * Import payment gateway.
   */
  private function actionSetupPaymentGateway(): void {
    $config_path = DRUPAL_ROOT . "/{$this->extensionPathResolver->getPath('module', 'opencollective_commerce')}/migrations/setup-guidance/store-type/";
    $source = new FileStorage($config_path);
    // Instantiate configFactory on controller base.
    $this->config('');

    // Import payment gateway.
    $this->entityTypeManager()->getStorage('commerce_payment_gateway')
      ->create($source->read('commerce_payment.commerce_payment_gateway.open_collective_contribution_flow'))
      ->save();

    $this->messenger()->addStatus($this->t('Open Collective payment gateway created, but more setup is required.'));

    // Now configure them.
    $this->actionConfigurePaymentGateway();
  }

  /**
   * Update payment gateway configuration.
   */
  private function actionConfigurePaymentGateway(): void {
    /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface[] $found_payment_gateways */
    $found_payment_gateways = $this->entityTypeManager()->getStorage('commerce_payment_gateway')->loadByProperties([
      'plugin' => SetupGuidanceInterface::COMMERCE_PAYMENT_GATEWAY_TYPE,
    ]);

    if (!$found_payment_gateways) {
      $this->messenger()->addError($this->t('No payment gateway of the type "@plugin" found.', [
        'plugin' => SetupGuidanceInterface::COMMERCE_PAYMENT_GATEWAY_TYPE,
      ]));
      return;
    }

    /** @var \Drupal\commerce_store\Entity\StoreInterface[] $found_stores */
    $found_stores = $this->entityTypeManager->getStorage('commerce_store')->loadByProperties([
      'type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
    ]);

    if (!$found_stores) {
      $this->messenger()->addError($this->t('No store of the type "@type" found.', [
        'type' => SetupGuidanceInterface::COMMERCE_STORE_TYPE,
      ]));
      return;
    }

    $store_uuids = array_map(function ($store) {
      return $store->uuid();
    }, $found_stores);

    foreach ($found_payment_gateways as $payment_gateway) {
      $conditions = $payment_gateway->get('conditions');
      $conditions[] = [
        'plugin' => 'order_store',
        'configuration' => [
          'stores' => $store_uuids,
        ],
      ];
      $payment_gateway->set('conditions', $conditions);
      $payment_gateway->save();
      $this->messenger()->addStatus($this->t('Updated payment gateway @label. Added conditions for stores: @stores.', [
        '@label' => $payment_gateway->label(),
        '@stores' => implode(', ', array_map(function ($store) {
          return $store->label();
        }, $found_stores)),
      ]));
    }
  }

  /**
   * Import the opencollective_event_type product variation type.
   */
  private function actionSetupEventTicketProductVariationType(): void {
    $config_path = DRUPAL_ROOT . "/{$this->extensionPathResolver->getPath('module', 'opencollective_commerce')}/migrations/setup-guidance/product-variation-event-ticket/";
    $source = new FileStorage($config_path);

    // Import product variation type.
    $this->entityTypeManager()->getStorage('commerce_product_variation_type')
      ->create($source->read('commerce_product.commerce_product_variation_type.opencollective_event_ticket'))
      ->save();

    // Form.
    $this->configFactory->getEditable('core.entity_form_display.commerce_product_variation.opencollective_event_ticket.default')
      ->setData($source->read('core.entity_form_display.commerce_product_variation.opencollective_event_ticket.default'))
      ->save();

    // Display.
    $this->configFactory->getEditable('core.entity_view_display.commerce_product_variation.opencollective_event_ticket.default')
      ->setData($source->read('core.entity_view_display.commerce_product_variation.opencollective_event_ticket.default'))
      ->save();

    $this->messenger()->addStatus($this->t('Product variation type @type created.', [
      '@type' => SetupGuidanceInterface::COMMERCE_EVENT_TICKET_PRODUCT_VARIATION_TYPE,
    ]));

    // Now set up the product type.
    $this->actionSetupEventProductType();
  }

  /**
   * Import the opencollective_event product type.
   */
  private function actionSetupEventProductType(): void {
    $config_path = DRUPAL_ROOT . "/{$this->extensionPathResolver->getPath('module', 'opencollective_commerce')}/migrations/setup-guidance/product-type-event/";
    $source = new FileStorage($config_path);

    // Import product type.
    $this->entityTypeManager()->getStorage('commerce_product_type')
      ->create($source->read('commerce_product.commerce_product_type.opencollective_event'))
      ->save();

    // Import slug field storage.
    $this->entityTypeManager()->getStorage('field_storage_config')
      ->create($source->read('field.storage.commerce_product.field_event_slug'))
      ->save();

    // Import slug field instance.
    $this->entityTypeManager()->getStorage('field_config')
      ->create($source->read('field.field.commerce_product.opencollective_event.field_event_slug'))
      ->save();

    // Import body field instance.
    $this->entityTypeManager()->getStorage('field_config')
      ->create($source->read('field.field.commerce_product.opencollective_event.body'))
      ->save();

    // Form.
    $this->configFactory->getEditable('core.entity_form_display.commerce_product.opencollective_event.default')
      ->setData($source->read('core.entity_form_display.commerce_product.opencollective_event.default'))
      ->save();

    // Display.
    $this->configFactory->getEditable('core.entity_view_display.commerce_product.opencollective_event.default')
      ->setData($source->read('core.entity_view_display.commerce_product.opencollective_event.default'))
      ->save();

    $this->messenger()->addStatus($this->t('Product type @type created.', [
      '@type' => SetupGuidanceInterface::COMMERCE_EVENT_PRODUCT_TYPE,
    ]));
  }

}
