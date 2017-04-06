<?php
/**
 * @file
 * TextPlain.php for tfp
 */

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\thunder_print\Annotation\TagMappingType;
use Drupal\thunder_print\Plugin\TagMappingTypeBase;

/**
 * Class TextPlain
 * @package Drupal\thunder_print\Plugin\TagMappingType
 * @TagMappingType(
 *   id = "text_plain",
 *   label = @Translation("Text plain"),
 * )
 */
class TextPlain extends TagMappingTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    return [
      'value' => [
        'required' => TRUE,
        'name' => $this->t('Value'),
      ]
    ];
  }
}
