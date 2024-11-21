<?php

namespace Drupal\opencollective_webhooks\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a form for deleting a WebhookEvent entity.
 *
 * @internal
 */
class WebhookEventDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getDeletionMessage() {
    /** @var \Drupal\opencollective_webhooks\Entity\WebhookEventInterface $entity */
    $entity = $this->getEntity();

    return $this->t('The @type %title has been deleted.', [
      '@type' => $entity->getEntityTypeId(),
      '%title' => $entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function logDeletionMessage() {
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $this->getEntity();
    $this->logger($entity->getEntityTypeId())->notice('@type: deleted %title.', [
      '@type' => $entity->getEntityTypeId(),
      '%title' => $entity->label(),
    ]);
  }

}
