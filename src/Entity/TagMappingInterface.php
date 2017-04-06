<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Tag Mapping entities.
 */
interface TagMappingInterface extends ConfigEntityInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * @return string
   */
  public function getMappingType();

  /**
   * @return array
   */
  public function getMapping();

  /**
   * @param $property
   *
   * @return mixed
   */
  public function getTag($property);

  public function getTags();
}
