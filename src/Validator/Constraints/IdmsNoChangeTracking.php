<?php

namespace Drupal\thunder_print\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for checking that there isn't changes tracking in idms.
 *
 * @Constraint(
 *   id = "IdmsNoChangeTracking",
 *   label = @Translation("No changes tracking.", context = "Validation"),
 * )
 */
class IdmsNoChangeTracking extends Constraint {
  public $message = 'The idms contains stories with activated change tracking.';

}
