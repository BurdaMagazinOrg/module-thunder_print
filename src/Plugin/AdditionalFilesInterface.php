<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\thunder_print\IDMS;

/**
 * Defines an interface for embedded files.
 */
interface AdditionalFilesInterface {

  /**
   * Replaces placeholders and embedded the images in idms.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   The IDMS with placeholders.
   * @param mixed $fieldItem
   *   Field value to replace.
   *
   * @return \Drupal\thunder_print\IDMS
   *   New idms with replaced placeholders.
   */
  public function replacePlaceholderWithAdditionalFiles(IDMS $idms, $fieldItem);

  /**
   * Get the connected files a type.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   The IDMS with placeholders.
   * @param mixed $fieldItem
   *   Field value to replace.
   *
   * @return \Drupal\file\Entity\File[]
   *   Array of additional files.
   */
  public function getAdditionalFiles(IDMS $idms, $fieldItem);

}
