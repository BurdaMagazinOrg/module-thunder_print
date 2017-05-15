<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\thunder_print\IDMS;

/**
 * Defines an interface for embedded files.
 */
interface AdditionalFilesInterface {

  /**
   * Replaces placeholders and include images with relative path.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   The IDMS with placeholders.
   * @param mixed $fieldItem
   *   Field value to replace.
   *
   * @return \Drupal\thunder_print\IDMS
   *   New idms with replaced placeholders.
   */
  public function replacePlaceholderUseRelativeLinks(IDMS $idms, $fieldItem);

  /**
   * Get the files to a tag mapping.
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
