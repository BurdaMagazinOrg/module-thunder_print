<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Font entities.
 *
 * @ingroup thunder_print
 */
interface FontInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Font name.
   *
   * @return string
   *   Name of the Font.
   */
  public function getName();

  /**
   * Sets the Font name.
   *
   * @param string $name
   *   The Font name.
   *
   * @return \Drupal\thunder_print\Entity\FontInterface
   *   The called Font entity.
   */
  public function setName($name);

  /**
   * Gets the Font creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Font.
   */
  public function getCreatedTime();

  /**
   * Sets the Font creation timestamp.
   *
   * @param int $timestamp
   *   The Font creation timestamp.
   *
   * @return \Drupal\thunder_print\Entity\FontInterface
   *   The called Font entity.
   */
  public function setCreatedTime($timestamp);

}
