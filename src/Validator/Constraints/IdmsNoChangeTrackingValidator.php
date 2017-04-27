<?php

namespace Drupal\thunder_print\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for IdmsNoChangeTracking.
 */
class IdmsNoChangeTrackingValidator extends ConstraintValidator {

  /**
   * Validates an idms file.
   *
   * @param mixed|\Drupal\thunder_print\IDMS $idms
   *   An idms objct.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint.
   */
  public function validate($idms, Constraint $constraint) {

    $xpath = "//Story[@TrackChanges='true']";
    $elements = $idms->getXml()->xpath($xpath);
    if (count($elements)) {
      $this->context->buildViolation($constraint->message)
        ->addViolation();
    }
  }

}
