<?php

namespace Drupal\thunder_print\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginContextualInterface;
use Drupal\ckeditor\CKEditorPluginCssInterface;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "stylescombo" plugin.
 *
 * @CKEditorPlugin(
 *   id = "thunder_print_idmsstyle",
 *   label = @Translation("IDMS style")
 * )
 */
class IDMSStyles extends PluginBase implements CKEditorPluginInterface, CKEditorPluginContextualInterface, CKEditorPluginCssInterface, ContainerFactoryPluginInterface {

  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'));
  }

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

    $files = ['public://thunder-print-css/fonts.css'];

    $printArticleTypes = $this->entityTypeManager->getStorage('print_article_type')
      ->loadMultiple();

    foreach ($printArticleTypes as $printArticleType) {
      $files[] = 'public://thunder-print-css/' . Html::getClass($printArticleType->label());
    }

    return $files;
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
