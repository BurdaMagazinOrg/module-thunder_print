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

    /** @var \Drupal\thunder_print\Plugin\TagMappingTypeManager $mapping_type_manager */
    $mapping_type_manager = \Drupal::service('plugin.manager.thunder_print_tag_mapping_type');

    $form['mapping_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Mapping type'),
      '#maxlength' => 255,
      '#default_value' => $tag_mapping->getMappingTypeId(),
      '#description' => $this->t("Type for the mapping."),
      '#options' => $mapping_type_manager->getOptions(),
      '#required' => TRUE,
    ];

    $plugin = $tag_mapping->getMappingType();

    if ($plugin) {

      $properties = $plugin->getPropertyDefinitions();
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

      $options_form = $plugin->optionsForm([], $form_state);

      if (!empty($options_form)) {
        $form['options'] = [
          '#tree' => TRUE,
          '#type' => 'fieldset',
          '#title' => $this->t('Options'),
        ] + $options_form;
      }
    };

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
