<?php

namespace Drupal\opencollective_commerce\Service;

/**
 * Service for helping set up an Open Collective store.
 */
interface SetupGuidanceInterface {

  /**
   * Requirement severity -- Requirement successfully met.
   */
  public const REQUIREMENT_OK = 0;

  /**
   * Requirement severity -- Warning condition; proceed but flag warning.
   */
  public const REQUIREMENT_WARNING = 1;

  /**
   * Requirement severity -- Informational message only.
   */
  public const REQUIREMENT_INFO = -1;

  /**
   * Requirement severity -- Error condition; abort installation.
   */
  public const REQUIREMENT_ERROR = 2;

  /**
   * Order type.
   */
  public const COMMERCE_ORDER_TYPE = 'opencollective_order';

  /**
   * Order item type.
   */
  public const COMMERCE_ORDER_ITEM_TYPE = 'opencollective_order_item';

  /**
   * Commerce store type.
   */
  public const COMMERCE_STORE_TYPE = 'opencollective_commerce_store';

  /**
   * Commerce store field to indicate the OC collective.
   *
   * Prefixed with "field_" so that admins can easily create it manually.
   */
  public const COMMERCE_STORE_COLLECTIVE_FIELD = 'field_collective_slug';

  /**
   * Type of field expected for self::COMMERCE_STORE_COLLECTIVE_FIELD.
   */
  public const COMMERCE_STORE_COLLECTIVE_FIELD_TYPE = 'opencollective_slug';

  /**
   * Type of (plugin id for) commerce_payment_gateway.
   */
  public const COMMERCE_PAYMENT_GATEWAY_TYPE = 'opencollective_commerce_contribution_flow';

  /**
   * Product variation type.
   */
  public const COMMERCE_EVENT_TICKET_PRODUCT_VARIATION_TYPE = 'opencollective_event_ticket';

  /**
   * Product type.
   */
  public const COMMERCE_EVENT_PRODUCT_TYPE = 'opencollective_event';

  /**
   * Required product_type field.
   */
  public const COMMERCE_EVENT_PRODUCT_TYPE_SLUG_FIELD = 'field_event_slug';

  /**
   * Gather and test all requirements.
   *
   * @return array
   *   Requirements for templating.
   */
  public function gatherRequirements(): array;

}
