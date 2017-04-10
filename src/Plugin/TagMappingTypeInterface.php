<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for Tag mapping type plugins.
 */
interface TagMappingTypeInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

  /**
   * Defines a list of properties.
   *
   * @return array
   */
  public function getPropertyDefinitions();

  /**
   * Retrieve the options for this mapping type instance.
   *
   * @return array
   */
  public function getOptions();

  /**
   * Retrieve a single option value for this mapping type instance.
   *
   * @param string $key
   *
   * @return mixed
   */
  public function getOption($key);

  /**
   * Set options for this mapping type instance.
   *
   * @param array $options
   */
  public function setOptions(array $options);


  /**
   * Generates options form for Tag Mapping.
   *
   * @param array $form
   *   A minimally prepopulated form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the (entire) configuration form.
   *
   * @return array
   *   The $form array with additional form elements for the options of this
   *   tag mapping.
   */
  public function optionsForm(array $form, FormStateInterface $form_state);

}
