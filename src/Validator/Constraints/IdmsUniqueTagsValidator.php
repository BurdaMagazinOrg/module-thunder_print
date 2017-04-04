<?php

namespace Drupal\thunder_print\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for IdmsUniqueTags.
 */
class IdmsUniqueTagsValidator extends ConstraintValidator {

  /**
   * Validates an idms file.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   An idms objct.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint.
   */
  public function validate($idms, Constraint $constraint) {

    foreach ($idms->getTags() as $tag) {
      $xpath = "//Story/XMLElement[@MarkupTag='$tag']";
      $elements = $idms->getXml()->xpath($xpath);
      if (count($elements) > 1) {
        $this->context->buildViolation($constraint->message)
          ->setParameter('%xmltag%', $tag)
          ->addViolation();
      }
    }
  }

}
