<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder_print\IDMS;

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
   * Replace placeholder from IDMS with real content.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   The IDMS with placeholders.
   * @param mixed $fieldValue
   *   Field value to replace.
   *
   * @return \Drupal\thunder_print\IDMS
   *   New idms with replaced placeholders.
   */
  public function replacePlaceholder(IDMS $idms, $fieldValue);

  /**
   * Alters a render element.
   *
   * @param array $element
   *   A render element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the (entire) configuration form.
   * @param array $context
   *   Field widget context.
   */
  public function hookFieldWidgetFormAlter(array &$element, FormStateInterface $form_state, array $context);

  /**
   * Retrieve a single tag for the given column.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   The IDMS to extract tags from.
   * @param string $column
   *   The column of the tag mapping to get tag instance for.
   *
   * @return \Drupal\thunder_print\IDMSTag
   *   Returns a single tag instance or NULL if no mapping tag exists.
   */
  public function getMappedTag(IDMS $idms, $column);

  /**
   * Extract tags from given IDMS that are mapped with this instance.
   *
   * @param \Drupal\thunder_print\IDMS $idms
   *   The IDMS to extract tags from.
   *
   * @return \Drupal\thunder_print\IDMSTag[]
   *   Returns list of mapped Tags, keyed by column.
   */
  public function getMappedTags(IDMS $idms);

}
