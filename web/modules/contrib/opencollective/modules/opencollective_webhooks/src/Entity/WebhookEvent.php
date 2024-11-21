<?php

namespace Drupal\opencollective_webhooks\Entity;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\opencollective_webhooks\Model\IncomingWebhookPayload;
use Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface;

/**
 * Defines the webhook event entity class.
 *
 * @ContentEntityType(
 *   id = "opencollective_webhook_event",
 *   label = @Translation("Webhook Event"),
 *   label_collection = @Translation("Webhook Events"),
 *   label_singular = @Translation("webhook event"),
 *   label_plural = @Translation("webhook events"),
 *   label_count = @PluralTranslation(
 *     singular = "@count webhook events",
 *     plural = "@count webhook events",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\opencollective_webhooks\WebhookEventListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "delete" = "Drupal\opencollective_webhooks\Form\WebhookEventDeleteForm",
 *     },
 *   },
 *   base_table = "opencollective_webhook_event",
 *   admin_permission = "administer opencollective webhook event",
 *   entity_keys = {
 *     "id" = "event_id",
 *     "label" = "event_id",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "collection" = "/admin/config/services/opencollective/webhook/events",
 *     "canonical" = "/admin/config/services/opencollective/webhook/event/{opencollective_webhook_event}",
 *     "delete-form" = "/admin/config/services/opencollective/webhook/event/{opencollective_webhook_event}/delete",
 *   },
 * )
 */
class WebhookEvent extends ContentEntityBase implements WebhookEventInterface {

  /**
   * Payload instance.
   *
   * @var \Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface|null
   */
  private ?IncomingWebhookPayloadInterface $payloadObject = NULL;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->get('event_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function eventReceivedAt(): int {
    return (int) $this->get('event_received_at')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function eventContext(): array {
    return Json::decode($this->get('event_context')->value);
  }

  /**
   * {@inheritdoc}
   */
  public function payloadCreatedAt(): int {
    return $this->get('payload_created_at')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function payloadId(): int {
    return (int) $this->get('payload_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function payloadType(): string {
    return (string) $this->get('payload_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function payloadCollectiveId(): int {
    return (int) $this->get('payload_collective_id')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function payload(): IncomingWebhookPayloadInterface {
    if (!$this->payloadObject) {
      $this->payloadObject = new IncomingWebhookPayload(Json::decode($this->get('payload')->value));
    }

    return $this->payloadObject;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['event_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Event ID'))
      ->setDescription(t('The ID of the Webhook Event entity in Drupal.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['event_received_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Event Received At'))
      ->setDescription(t('When Drupal received the incoming webhook request.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['event_context'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Context'))
      ->setDescription(t('Webhook event metadata.'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['payload_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Payload ID'))
      ->setDescription(t('The ID the Payload was given by Open Collective.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['payload_created_at'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Payload Created At'))
      ->setDescription(t('Webhook payload "createdAt" as unix timestamp.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['payload_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Payload Type'))
      ->setDescription(t('Webhook payload "type".'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['payload_collective_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Collective ID'))
      ->setDescription(t('Webhook payload "CollectiveId".'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    $fields['payload'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Payload'))
      ->setDescription(t('Entire webhook payload.'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    return $fields;
  }

}
