<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Tag Mapping entities.
 */
interface TagMappingInterface extends ConfigEntityInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Provides the raw mapping type id.
   *
   * @return string
   */
  public function getMappingTypeId();

  /**
   * Provides the mapping relation
   *
   * @return array
   */
  public function getMapping();

  /**
   * Provides the tag for a specific property.
   *
   * @param string $property
   *
   * @return string
   */
  public function getTag($property);

  /**
   * Provides a list of tags used by this mapping.
   *
   * @return array
   */
  public function getTags();

  /**
   * Provides the associated mapping type plugin instance.
   *
   * @return \Drupal\thunder_print\Plugin\TagMappingTypeInterface
   */
  public function getMappingType();
}
