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

    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $tag_mapping */
    $tag_mapping = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $tag_mapping->label(),
      '#description' => $this->t("Label for the Tag Mapping."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $tag_mapping->id(),
      '#machine_name' => [
        'exists' => '\Drupal\thunder_print\Entity\TagMapping::load',
      ],
      '#disabled' => !$tag_mapping->isNew(),
    ];

    $form['mapping_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mapping type'),
      '#maxlength' => 255,
      '#default_value' => $tag_mapping->getMappingType(),
      '#description' => $this->t("Type for the mapping."),
      '#required' => TRUE,
    ];

    $properties = [
      'value' => [
        'name' => 'Value',
        'required' => TRUE,
      ],
    ];

    $form['mapping'] = [
      '#tree' => TRUE,
      '#type' => 'fieldset',
      '#title' => $this->t('Mapping'),
    ];

    foreach ($properties as $property => $spec) {
      $form['mapping'][] = [
        'property' => [
          '#type' => 'value',
          '#value' => $property,
        ],
        'tag' => [
          '#type' => 'textfield',
          '#title' => $spec['name'],
          '#required' => !empty($spec['required']),
          '#default_value' => $tag_mapping->getTag($property),
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $tag_mapping */
    $tag_mapping = $this->entity;
    $status = $tag_mapping->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tag Mapping.', [
          '%label' => $tag_mapping->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tag Mapping.', [
          '%label' => $tag_mapping->label(),
        ]));
    }
    $form_state->setRedirectUrl($tag_mapping->toUrl('collection'));
  }

}
