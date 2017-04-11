<?php

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder_print\Annotation\TagMappingType;
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
  public function optionsForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

}
