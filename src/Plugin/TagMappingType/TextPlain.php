<?php

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Plugin\TagMappingTypeBase;

/**
 * Provides tag mapping for plain text field.
 *
 * @package Drupal\thunder_print\Plugin\TagMappingType
 *
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
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function optionsForm(array $form, FormStateInterface $form_state) {
    $form = parent::optionsForm($form, $form_state);

    $form['title'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Can be used as title'),
      '#default_value' => $this->getOption('title'),
    ];
    return $form;
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
      'type' => 'string',
      'settings' => [
        'max_length' => 1024,
        'is_ascii' => FALSE,
        'case_sensitive' => FALSE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function replacePlaceholder(IDMS $idms, $fieldValue) {
    $tagname = $this->configuration['mapping']['value'];
    foreach ($idms->getTags() as $tag) {
      if ($tag->getSelf() == $tagname) {
        $tag->replacePlain($fieldValue['value']);
      }
    }
    return $idms;
  }

}
