<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for Tag mapping type plugins.
 */
abstract class TagMappingTypeBase extends PluginBase implements TagMappingTypeInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function optionsForm(array $form, FormStateInterface $form_state) {
    return [];
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

}
