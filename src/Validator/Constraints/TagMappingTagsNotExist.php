<?php

namespace Drupal\thunder_print\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for checking tag is only ised in a single mapping config.
 *
 * @Constraint(
 *   id = "TagMappingTagsNotExist",
 *   label = @Translation("Tags do not exists in tag mappings.", context = "Validation"),
 * )
 */
class TagMappingTagsNotExist extends Constraint {

  /**
   * Message to print for constraint violation.
   *
   * @var string
   */
  public $message = 'The tag "%tag%" already exists in another tag mapping.';

  /**
   * {@inheritdoc}
   */
  public function getTargets() {
    return self::CLASS_CONSTRAINT;
  }
}
