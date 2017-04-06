<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Tag mapping type plugins.
 */
interface TagMappingTypeInterface extends PluginInspectionInterface {

  /**
   * Defines a list of properties.
   *
   * @return array
   */
  public function getPropertyDefinitions();

}
