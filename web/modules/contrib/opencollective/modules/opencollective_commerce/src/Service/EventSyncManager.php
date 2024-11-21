<?php

namespace Drupal\opencollective_commerce\Service;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\CollectiveEvents;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\CollectiveEventTiers;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\Event;
use Drupal\opencollective_api\Service\ApiClient;

class EventSyncManager {

  public function __construct(
    private EntityTypeManagerInterface $entityTypeManager,
    private ApiClient $apiClient,
    private AccountProxyInterface $currentUser,
    private MessengerInterface $messenger,
  ) {}

  /**
   * Get stores of the opencollective_commerce_store type.
   *
   * @return \Drupal\commerce_store\Entity\StoreInterface[]
   *   Found stores of type.
   */
  protected function getOpenCollectiveStores(): array {
    return $this->entityTypeManager->getStorage('commerce_store')->loadByProperties([
      'type' => 'opencollective_commerce_store',
    ]);
  }

  /**
   * Get event product by open collective event slug.
   *
   * @param string $event_slug
   *   Open Collective event slug.
   *
   * @return \Drupal\commerce_product\Entity\ProductInterface|null
   *   Product if found.
   */
  protected function getEventProductByEventSlug(string $event_slug): ?ProductInterface {
    $found = $this->entityTypeManager->getStorage('commerce_product')->loadByProperties([
      'type' => 'opencollective_event',
      'field_event_slug' => $event_slug,
    ]);

    return $found ? reset($found) : NULL;
  }

  /**
   * Get event ticket product variation by sku = ticket_slug.
   *
   * @param string $ticket_slug
   *   Open Collective ticket slug.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface|null
   *   Event ticket product variation if found.
   */
  protected function getEventTicketProductVariationsByTicketSlug(string $ticket_slug): ?ProductVariationInterface {
    $found = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties([
      'type' => 'opencollective_event_ticket',
      'sku' => $ticket_slug,
    ]);

    return $found ? reset($found) : NULL;
  }

  /**
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function syncEventProducts() {
    $collective_events_query = $this->apiClient->queryPluginManager()->createInstance(CollectiveEvents::PLUGIN_ID);
    $event_query = $this->apiClient->queryPluginManager()->createInstance(Event::PLUGIN_ID);

    $products_to_update = [];
    $products_to_create = [];

    foreach ($this->getOpenCollectiveStores() as $store) {
      $results = $this->apiClient->performQuery($collective_events_query, [
        'collective_slug' => $store->field_collective_slug->value,
      ]);

      foreach ($results as $collective_event_account) {
        $product = $this->getEventProductByEventSlug($collective_event_account['slug']);
        $event = $this->apiClient->performQuery($event_query, [
          'event_slug' => $collective_event_account['slug'],
        ]);

        if ($product) {
          $products_to_update[$collective_event_account['slug']] = [
            'store_id' => $store->id(),
            'event' => $event,
            'product' => $product,
          ];
          continue;
        }

        if ($event) {
          $products_to_create[$collective_event_account['slug']] = [
            'store_id' => $store->id(),
            'event' => $event,
            'product' => NULL,
          ];
        }
      }
    }

    foreach ($products_to_create as $slug => &$create) {
      $event = $create['event'];
      /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
      $product = $this->entityTypeManager->getStorage('commerce_product')->create([
        'type' => 'opencollective_event',
        'title' => $event['name'],
        'field_event_slug' => $slug,
        'body' => [
          'summary' => $event['description'] ?: '',
          'value' => $event['description'] ?: '',
          // @todo This should be smarter.
          'format' => 'restricted_html',
        ],
        'uid' => $this->currentUser->id(),
        'stores' => [
          $create['store_id'],
        ],
      ]);
      $product->save();
      $create['product'] = $product;
      $this->messenger->addStatus("Product created: {$product->label()}");

      $this->syncEventTicketProductVariations($product);
    }

    foreach ($products_to_update as $update) {
      /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
      $product = $update['product'];
      $event = $update['event'];

      // @todo What do we want to update on a product?
      // @todo We don't want to overwrite something an admin wants to keep.
      $product->set('title', $event['name']);
      $product->set('body', [
        'summary' => $event['description'] ?: '',
        'value' => $event['description'] ?: '',
        'format' => $product->body->format,
      ]);
      $product->save();
      $this->messenger->addStatus("Product updated: {$product->label()}");

      $this->syncEventTicketProductVariations($product);
    }
  }

  /**
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function syncEventTicketProductVariations(ProductInterface $product) {
    $tickets_query = $this->apiClient->queryPluginManager()->createInstance(CollectiveEventTiers::PLUGIN_ID);
    $event_tickets = $this->apiClient->performQuery($tickets_query, [
      'event_slug' => $product->field_event_slug->value,
    ]);

    // Filter out tiers that aren't tickets.
    $event_tickets = array_filter($event_tickets, fn ($ticket) => $ticket['type'] === 'TICKET');

    $variations_to_create = [];
    $variations_to_update = [];

    foreach ($event_tickets as $ticket) {
      $variation = $this->getEventTicketProductVariationsByTicketSlug($ticket['slug']);
      if ($variation) {
        $variations_to_update[$ticket['slug']] = [
          'ticket' => $ticket,
          'variation' => $variation,
        ];
        continue;
      }

      $variations_to_create[$ticket['slug']] = [
        'ticket' => $ticket,
        'variation' => NULL,
      ];
    }

    foreach ($variations_to_create as $slug => &$create) {
      $ticket = $create['ticket'];
      $variation = $this->entityTypeManager->getStorage('commerce_product_variation')->create([
        'type' => 'opencollective_event_ticket',
        'title' => $ticket['name'],
        'sku' => "{$ticket['slug']}-{$ticket['legacyId']}",
        'price' => new Price((string) $ticket['amount']['value'], $ticket['amount']['currency']),
        'uid' => $this->currentUser->id(),
        'product_id' => $product->id(),
      ]);
      $variation->save();
      $this->messenger->addStatus("Variation {$variation->label()} created for product {$product->label()}");

      $create['variation'] = $variation;
    }

    foreach ($variations_to_update as $slug => $update) {
      $ticket = $update['ticket'];
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
      $variation = $update['variation'];

      // @todo What do we want to update on a product variation?
      // @todo We don't want to overwrite something an admin wants to keep.
      $variation->setTitle($ticket['name']);
      $variation->setPrice(new Price((string) $ticket['amount']['value'], $ticket['amount']['currency']));
      $variation->save();
      $this->messenger->addStatus("Variation {$variation->label()} updated for product {$product->label()}");
    }
  }

}
