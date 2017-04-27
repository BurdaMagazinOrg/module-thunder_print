<?php

namespace Drupal\thunder_print\Plugin\CKeditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "stylescombo" plugin.
 *
 * @CKEditorPlugin(
 *   id = "thunder_print_idmsstyle",
 *   label = @Translation("IDMS style")
 * )
 */
class IDMSStyles extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'thunder_print') . '/js/plugins/idmsstyle/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
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
