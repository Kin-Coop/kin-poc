<?php

namespace Drupal\kin_forum\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures only one group_forum node per household.
 *
 * @Constraint(
 *   id = "UniqueGroupForum",
 *   label = @Translation("Unique Group Forum per household", context = "Validation")
 * )
 */
class UniqueGroupForumConstraint extends Constraint {
  public $message = 'A group forum already exists for this household.';
}
