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
  public function getOriginalIdms();

  /**
   * Provides new IDMS object based on the original IDMs.
   *
   * @return \Drupal\thunder_print\IDMS
   *   New idms object.
   */
  public function getNewIdms();

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
   * @return string|null
   *   The thumbnail's URL or NULL if icon does not exits.
   */
  public function getThumbnailUrl();

  /**
   * Extract thumbnail from idms and create file entity.
   *
   * @return \Drupal\file\FileInterface|null
   *   The thumbnail's file entity or NULL if thumbnail does not exist.
   */
  public function buildThumbnail();

  /**
   * Creates fields at bundle from an idms file.
   */
  public function createBundleFields();

  /**
   * Get tag mappings associated to this bundle's idms.
   *
   * @return \Drupal\thunder_print\Entity\TagMappingInterface[]
   *   Tag mappings.
   */
  public function getMappings();

  /**
   * Retrieve mapping definition for given field.
   *
   * @param string $field_name
   *   The field name a mapping may be associated to.
   *
   * @return \Drupal\thunder_print\Entity\TagMappingInterface
   *   Tag mappings.
   */
  public function getMappingForField($field_name);

  /**
   * Get the intersection of tags from idms and tag mapping.
   *
   * @return \Drupal\thunder_print\Entity\TagMappingInterface[]
   *   Each mapping is keyed by the tag.
   */
  public function getTags();

  /**
   * Get number of print articles of the current bundle.
   *
   * @return int
   *   Number of articles.
   */
  public function getEntityCount();

  /**
   * Get all bundles to that could be switched.
   *
   * @return array
   *   Array of bundles.
   */
  public function getSwitchableBundles();

}
