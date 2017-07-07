<?php

namespace Drupal\thunder_print\Plugin\CKeditorPlugin;

use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\ckeditor\CKEditorPluginCssInterface;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "stylescombo" plugin.
 *
 * @CKEditorPlugin(
 *   id = "thunder_print_idmsstyle",
 *   label = @Translation("IDMS style")
 * )
 */
class IDMSStyles extends PluginBase implements CKEditorPluginInterface, CKEditorPluginContextualInterface, CKEditorPluginCssInterface {

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(Editor $editor) {
    return ($editor->getEditor() == 'ckeditor');
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'thunder_print') . '/js/plugins/idmsstyle/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    return [
      'public://thunder-print-css/fonts.css',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'core/drupalSettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'thunder_print_idmsstyle' => [
        'field_xy',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'thunder_print_idmsstyle' => [
        'label' => $this->t('IDMS styles'),
        'image_alternative' => [
          '#type' => 'inline_template',
          '#template' => '<a href="#" role="button" aria-label="{{ styles_text }}"><span class="ckeditor-button-dropdown">{{ styles_text }}<span class="ckeditor-button-arrow"></span></span></a>',
          '#context' => [
            'styles_text' => $this->t('IDMS Styles'),
          ],
        ],
      ],
    ];
  }

}
