<?php

namespace Drupal\kin_forum\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\node\Entity\Node;

class UniqueGroupForumConstraintValidator extends ConstraintValidator {

  public function validate($field_item, Constraint $constraint) {
    // Only apply to group_forum nodes.
    $entity = $field_item->getEntity();
    if (!$entity instanceof Node || $entity->bundle() !== 'group_forum') {
      return;
    }

    $household_id = $field_item->target_id;
    if (!$household_id) {
      return;
    }

    // Check for existing group_forum node with the same field_group.
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->condition('type', 'group_forum')
      ->condition('field_group', $household_id)
      ->accessCheck(FALSE);

    if (!$entity->isNew()) {
      $query->condition('nid', $entity->id(), '!=');
    }

    $result = $query->range(0, 1)->execute();

    if (!empty($result)) {
      $this->context->addViolation($constraint->message);
    }
  }
}
