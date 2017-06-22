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
   * @param mixed|\Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type
   *   An idms objct.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint.
   */
  public function validate($print_article_type, Constraint $constraint) {

    $idms = $print_article_type->getNewIdms();
    foreach ($print_article_type->getTags() as $tag => $tagMapping) {
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
