<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Print article type entities.
 */
interface PrintArticleTypeInterface extends ConfigEntityInterface {

  /**
   * Brief description of this print article type.
   *
   * @return string
   *   The description.
   */
  public function getDescription();

  /**
   * Grid size to render an idms.
   *
   * @return int
   *   The grid size.
   */
  public function getGrid();

  /**
   * The complete idms xml.
   *
   * @return string
   *   Xml of the idms.
   */
  public function getIdms();

  /**
   * Returns the thumbnail file entity.
   *
   * @return \Drupal\file\FileInterface|bool
   *   The thumbnail's file entity or FALSE if thumbnail does not exist.
   */
  public function getThumbnailFile();

  /**
   * Returns the thumbnail's URL.
   *
   * @return string|bool
   *   The thumbnail's URL or FALSE if icon does not exits.
   */
  public function getThumbnailUrl();

  /**
   * Extract thumbnail from idms and create file entity.
   *
   * @return \Drupal\file\FileInterface|bool
   *   The thumbnail's file entity or FALSE if thumbnail does not exist.
   */
  public function retrieveThumbnail();

}
