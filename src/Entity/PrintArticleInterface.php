<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Print article entities.
 *
 * @ingroup thunder_print
 */
interface PrintArticleInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

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
   * Returns the Print article published status indicator.
   *
   * Unpublished Print article are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Print article is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Print article.
   *
   * @param bool $published
   *   TRUE to set this article to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\thunder_print\Entity\PrintArticleInterface
   *   The called Print article entity.
   */
  public function setPublished($published);

  /**
   * Gets the Print article revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Print article revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\thunder_print\Entity\PrintArticleInterface
   *   The called Print article entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Print article revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Print article revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\thunder_print\Entity\PrintArticleInterface
   *   The called Print article entity.
   */
  public function setRevisionUserId($uid);

}
