<?php

namespace Drupal\thunder_print\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Idms builder item annotation object.
 *
 * @see \Drupal\thunder_print\Plugin\IdmsBuilderManager
 * @see plugin_api
 *
 * @Annotation
 */
class IdmsBuilder extends Plugin {

  const BUILDMODE_MULTIFILE = 0;
  const BUILDMODE_SINGLEFILE = 1;

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

  /**
   * Indicates multi or single file.
   *
   * @var int
   */
  public $buildMode;

}
