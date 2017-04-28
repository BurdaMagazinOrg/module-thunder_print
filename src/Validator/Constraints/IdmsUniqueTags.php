<?php

namespace Drupal\thunder_print\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for checking that tags are used unique in idms file.
 *
 * @Constraint(
 *   id = "IdmsUniqueTags",
 *   label = @Translation("Unique tags in idms file.", context = "Validation"),
 * )
 */
class IdmsUniqueTags extends Constraint {
  public $message = 'The xml contains a multiple amount of "%xmltag%" tags.';

  /**
   * {@inheritdoc}
   */
  public function getTargets() {
    return self::CLASS_CONSTRAINT;
  }

}
