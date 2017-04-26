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
   *   List of property definitions, each keyed by the property key and holding
   *   - name: Translated property name
   *   - required: Boolean (optional) defaults to FALSE
   */
  public function getPropertyDefinitions();

  /**
   * Retrieve the options for this mapping type instance.
   *
   * @return array
   *   List of key value pairs of options.
   */
  public function getOptions();

  /**
   * Retrieve a single option value for this mapping type instance.
   *
   * @param string $key
   *   Option key to get value for.
   *
   * @return mixed
   *   Arbitrary value assigned to the option.
   */
  public function getOption($key);

  /**
   * Set options for this mapping type instance.
   *
   * @param array $options
   *   List of options (key-value pairs).
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

  /**
   * Provides the key of the main property to be used as ID.
   *
   * @return string
   *   Property key for the main property.
   */
  public function getMainProperty();

  /**
   * Returns the storage definitions for field creation.
   *
   * @return mixed
   *   Storage definition array.
   */
  public function getFieldStorageDefinition();

  /**
   * Returns the field definitions for field creation.
   *
   * @return mixed
   *   Field definition array.
   */
  public function getFieldConfigDefinition();

  /**
   * Returns the form display definitions for field creation.
   *
   * @return mixed
   *   Form display definition array.
   */
  public function getFormDisplayDefinition();

}
