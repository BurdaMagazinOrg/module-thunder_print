<?php

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\Core\Render\Element\Html;
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
  public function replacePlaceholder(IDMS $idms, $fieldItem) {
    return $idms;
  }

  /**
   * {@inheritdoc}
   */
  public function hookFieldWidgetFormAlter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context) {

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
      $print_article_type = \Drupal\thunder_print\Entity\PrintArticleType::load($bundle);

      $tag = $this->getMappedTag($print_article_type->getNewIdms(), 'value');
      if (empty($tag)) {
        return;
      }

      $element['#thunder_print'] = [
        'field' => $field_name,
        'bundle' => $bundle,
        'type' => $type,
        'styles' => [
//          [
//            'element' => 'span',
//            'attributes' => ['class' => 'idms-span1', 'style' => 'color: red'],
//            'name' => 'IDMS Span1',
//          ],
//          [
//            'element' => 'p',
//            'attributes' => ['class' => 'idms-block1', 'style' => 'background: yellow'],
//            'name' => 'IDMS Block1',
//          ],
//          [
//            'element' => 'p',
//            'attributes' => ['class' => 'idms-block2'],
//            'name' => 'IDMS Block2',
//          ],
        ]
      ];

      foreach ($tag->getParagraphStyles() as $style) {
        $element['#thunder_print']['styles'][] = [
          'element' => 'p',
          'attributes' => ['class' => $style->getClass()],
          'name' => $style->getName(),
        ];
      }

      foreach ($tag->getCharacterStyles() as $style) {
        $element['#thunder_print']['styles'][] = [
          'element' => 'span',
          'attributes' => ['class' => $style->getClass()],
          'name' => $style->getName(),
        ];
      }
    }
  }
}
