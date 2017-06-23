<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Print article entities.
 *
 * @ingroup thunder_print
 */
interface PrintArticleInterface extends EntityInterface, RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface {

  /**
   * Gets the Print article type.
   *
   * @return string
   *   The Print article type.
   */
  public function getType();

  /**
   * Gets the Print article name.
   *
   * @return string
   *   Name of the Print article.
   */
  public function getName();

  /**
   * Sets the Print article name.
   *
   * @param string $name
   *   The Print article name.
   *
   * @return \Drupal\thunder_print\Entity\PrintArticleInterface
   *   The called Print article entity.
   */
  public function setName($name);

  /**
   * Gets the Print article creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Print article.
   */
  public function getCreatedTime();

  /**
   * Sets the Print article creation timestamp.
   *
   * @param int $timestamp
   *   The Print article creation timestamp.
   *
   * @return \Drupal\thunder_print\Entity\PrintArticleInterface
   *   The called Print article entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns print article metadata.
   *
   * @return array
   *   Metadata array
   */
  public function getMetadata();

}
