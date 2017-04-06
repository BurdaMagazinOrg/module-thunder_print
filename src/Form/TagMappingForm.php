<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TagMappingForm.
 *
 * @package Drupal\thunder_print\Form
 */
class TagMappingForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $thunder_print_tag_mapping = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $thunder_print_tag_mapping->label(),
      '#description' => $this->t("Label for the Tag Mapping."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $thunder_print_tag_mapping->id(),
      '#machine_name' => [
        'exists' => '\Drupal\thunder_print\Entity\TagMapping::load',
      ],
      '#disabled' => !$thunder_print_tag_mapping->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $thunder_print_tag_mapping = $this->entity;
    $status = $thunder_print_tag_mapping->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tag Mapping.', [
          '%label' => $thunder_print_tag_mapping->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tag Mapping.', [
          '%label' => $thunder_print_tag_mapping->label(),
        ]));
    }
    $form_state->setRedirectUrl($thunder_print_tag_mapping->toUrl('collection'));
  }

}
