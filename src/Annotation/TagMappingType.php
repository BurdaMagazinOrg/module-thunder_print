<?php

namespace Drupal\thunder_print\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Tag mapping type item annotation object.
 *
 * @see \Drupal\thunder_print\Plugin\TagMappingTypeManager
 * @see plugin_api
 *
 * @Annotation
 */
class TagMappingType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
