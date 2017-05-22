<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\thunder_print\IDMS;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Tag mapping type plugins.
 */
abstract class TagMappingTypeBase extends PluginBase implements TagMappingTypeInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The widget or formatter plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerBase
   */
  protected $pluginManager;

  protected $entityTypeManager;

  /**
   * MediaImage constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Plugin\PluginManagerBase $plugin_manager
   *   The widget or formatter plugin manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerBase $plugin_manager, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->pluginManager = $plugin_manager;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.field.widget'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function optionsForm(array $form, FormStateInterface $form_state) {

    $wrapper_id = Html::getId('tap-mapping-form-ajax-wrapper');

    $form['widget_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Form widget'),
      '#options' => $this->getApplicablePluginOptions($this->getFieldStorageDefinition()['type']),
      '#default_value' => $this->getOption('widget_type'),
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => $wrapper_id,
      ],
    ];

    if ($widget_type = $this->getOption('widget_type')) {
      $fieldStorage = $this->entityTypeManager
        ->getStorage('field_storage_config')
        ->create(
          [
            'field_name' => 'bar',
            'entity_type' => 'foo',
            'cardinality' => 1,
            'locked' => TRUE,
          ] + $this->getFieldStorageDefinition());

      $fieldConfig = $this->entityTypeManager
        ->getStorage('field_config')
        ->create(
          [
            'field_storage' => $fieldStorage,
            'bundle' => 'foo',
            'label' => 'foo',
            'translatable' => FALSE,
          ] + $this->getFieldConfigDefinition());

      $widget = $this->pluginManager->getInstance([
        'field_definition' => $fieldConfig,
        'form_mode' => 'default',
        // No need to prepare, defaults have been merged in setComponent().
        'prepare' => FALSE,
        'configuration' => [
          'weight' => 1,
          'type' => $widget_type,
          'settings' => [],
          'third_party_settings' => [],
        ],
      ]);

      $form['field_settings'] = $widget->settingsForm($form, $form_state);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->getConfiguration()['options'];
  }

  /**
   * {@inheritdoc}
   */
  public function getOption($key) {
    $options = $this->getOptions();
    if (isset($options[$key])) {
      return $options[$key];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    $this->configuration['options'] = $options;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    if (empty($this->configuration)) {
      return $this->defaultConfiguration();
    }
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'mapping' => [],
      'options' => [],
    ];
  }

  /**
   * Returns an array of applicable widget or formatter options for a field.
   *
   * @param string $field_type
   *   The field definition.
   *
   * @return array
   *   An array of applicable widget or formatter options.
   */
  protected function getApplicablePluginOptions($field_type) {
    $options = $this->pluginManager->getOptions($field_type);
    $applicable_options = [];
    foreach ($options as $option => $label) {
      $applicable_options[$option] = $label;
    }
    return $applicable_options;
  }

  /**
   * Replace one content tag.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   The idms template.
   * @param string $tag
   *   The tag within content should be replaced.
   * @param string $value
   *   The new value.
   *
   * @return \Drupal\thunder_print\IDMS
   *   New idms with replaced content.
   */
  protected function replacePlain(IDMS $idms, $tag, $value) {

    $xpath = "//Story//XMLElement[@MarkupTag='$tag']//Content";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $idms->getXml()->xpath($xpath);
    if ($xmlElement) {
      $xmlElement[0][0] = trim(strip_tags($value));
    }
    return $idms;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfigDefinition() {
    return [];
  }

}
