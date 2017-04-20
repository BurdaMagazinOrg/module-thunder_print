<?php

namespace Drupal\thunder_print\Validator\Constraints;

use Drupal\thunder_print\Entity\TagMapping;
use Drupal\thunder_print\Entity\TagMappingInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the constraint TagMappingTagsNotExist.
 */
class TagMappingTagsNotExistValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (!$value instanceof TagMappingInterface) {
      throw new \Exception('Validator is only meant for TagMapping checks');
    }

    $tags = $value->getTags();
    foreach ($tags as $tag) {
      $mapping = TagMapping::loadMappingForTag($tag);
      if ($mapping && $mapping->id() !== $value->id()) {
        $this->context->buildViolation($constraint->message)
          ->setParameter('%tag%', $tag)
          ->addViolation();
      }
    }
  }

}
