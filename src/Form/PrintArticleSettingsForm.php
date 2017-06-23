<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PrintArticleSettingsForm.
 *
 * @package Drupal\thunder_print\Form
 *
 * @ingroup thunder_print
 */
class PrintArticleSettingsForm extends ConfigFormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'PrintArticle_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'thunder_print.settings',
    ];
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('thunder_print.settings');

    $keys = [
      'api_url',
    ];
    foreach ($keys as $key) {
      $config->set('general_settings.' . $key, $form_state->getValue($key));
    }
    $config->save();
  }

  /**
   * Defines the settings form for Print article entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('thunder_print.settings');

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Url'),
      '#default_value' => $config->get('general_settings.api_url'),
      '#description' => $this->t('Url to the API that is connected with the InDesign server'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
