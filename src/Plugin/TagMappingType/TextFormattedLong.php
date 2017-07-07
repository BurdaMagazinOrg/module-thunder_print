<?php

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\Core\Form\FormStateInterface;
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
  public function replacePlaceholder(IDMS $idms, $fieldValue) {
    $tagname = $this->configuration['mapping']['value'];
    foreach ($idms->getTags() as $tag) {
      if ($tag->getSelf() == $tagname) {
        $tag->replaceComplex($fieldValue['value']);
      }
    }
    return $idms;
  }

  /**
   * {@inheritdoc}
   */
  public function hookFieldWidgetFormAlter(array &$element, FormStateInterface $form_state, array $context) {

    /** @var \Drupal\Core\Field\WidgetInterface $widget */
    $widget = $context['widget'];

    if ($widget->getPluginId() == 'text_textarea') {
      /** @var \Drupal\Core\Field\FieldItemListInterface $items */
      $items = $context['items'];
      $type = $items->getFieldDefinition()->getTargetEntityTypeId();

      if ($type != 'print_article') {
        return;
      }

      $field_name = $items->getFieldDefinition()->getName();
      $bundle = $items->getFieldDefinition()->getTargetBundle();
      $print_article_type = $this->entityTypeManager->getStorage('print_article_type')->load($bundle);

      $tag = $this->getMappedTag($print_article_type->getNewIdms(), 'value');
      if (empty($tag)) {
        return;
      }

      $element['#thunder_print'] = [
        'field' => $field_name,
        'bundle' => $bundle,
        'type' => $type,
        'styles' => [],
      ];

      foreach ($tag->getParagraphStyles() as $style) {
        $element['#thunder_print']['styles'][] = [
          'element' => 'p',
          'attributes' => ['class' => $style->getClass()],
          'name' => $style->getDisplayName(),
          'styles' => ['font-family' => $style->getFontFamily()],
        ];
      }

      foreach ($tag->getCharacterStyles() as $style) {
        $element['#thunder_print']['styles'][] = [
          'element' => 'span',
          'attributes' => ['class' => $style->getClass()],
          'name' => $style->getDisplayName(),
        ];
      }

      // For now we restrict the text format to the included one, so users do
      // do not get confused with irrelevant styles and buttons.
      $element['#allowed_formats'] = ['tfp'];
    }
  }

}
