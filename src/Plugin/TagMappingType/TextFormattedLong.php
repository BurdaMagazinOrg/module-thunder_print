<?php

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Plugin\TagMappingTypeBase;

/**
 * Provides tag mapping for formatted long text.
 *
 * @package Drupal\thunder_print\Plugin\TagMappingType
 *
 * @TagMappingType(
 *   id = "text_formatted_long",
 *   label = @Translation("Text formatted (long)"),
 * )
 */
class TextFormattedLong extends TagMappingTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    return [
      'value' => [
        'required' => TRUE,
        'name' => $this->t('Value'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMainProperty() {
    return 'value';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldStorageDefinition() {
    return [
      'type' => 'text_long',
      'settings' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfigDefinition() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function replacePlaceholder(IDMS $idms, $fieldItem) {
    return $idms;
  }

}
