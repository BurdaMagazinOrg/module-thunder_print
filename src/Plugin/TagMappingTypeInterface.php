<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for Tag mapping type plugins.
 */
interface TagMappingTypeInterface extends PluginInspectionInterface {

  /**
   * Defines a list of properties.
   *
   * @return array
   */
  public function getPropertyDefinitions();

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
