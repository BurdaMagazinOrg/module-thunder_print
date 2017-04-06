<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for Tag mapping type plugins.
 */
abstract class TagMappingTypeBase extends PluginBase implements TagMappingTypeInterface {

  use StringTranslationTrait;
}
