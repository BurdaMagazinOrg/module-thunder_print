<?php

namespace Drupal\thunder_print\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TagMappingForm.
 *
 * @package Drupal\thunder_print\Form
 *
 * @property \Drupal\thunder_print\Entity\TagMappingInterface $entity
 */
class TagMappingForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $tag_mapping = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $tag_mapping->label(),
      '#description' => $this->t("Label for the Tag Mapping."),
      '#required' => TRUE,
    ];

    /** @var \Drupal\thunder_print\Plugin\TagMappingTypeManager $mapping_type_manager */
    $mapping_type_manager = \Drupal::service('plugin.manager.thunder_print_tag_mapping_type');

    $wrapper_id = Html::getId('tap-mapping-form-ajax-wrapper');

    $form['mapping_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Mapping type'),
      '#maxlength' => 255,
      '#default_value' => $tag_mapping->getMappingTypeId(),
      '#description' => $this->t("Type for the mapping."),
      '#options' => $mapping_type_manager->getOptions(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => $wrapper_id,
      ],
    ];

    $plugin = $tag_mapping->getMappingType();

    $form['configuration'] = [
      '#type' => 'container',
      '#id' => $wrapper_id,
      '#attributes' => array(
        'id' => $wrapper_id,
      ),
    ];

    if ($plugin) {

      $properties = $plugin->getPropertyDefinitions();
      $form['configuration']['mapping'] = [
        '#tree' => TRUE,
        '#type' => 'fieldset',
        '#title' => $this->t('Mapping'),
      ];

      foreach ($properties as $property => $spec) {
        $form['configuration']['mapping'][] = [
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
        $form['configuration']['options'] = [
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

  /**
   * Callback for ajax requests.
   */
  public static function ajaxCallback(array $form, FormStateInterface $form_state) {
    return $form['configuration'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $new_entity */
    $new_entity = $this->buildEntity($form, $form_state);
    $main_tag = $new_entity->getMainTag();
    if (!strlen($main_tag)) {
      $form_state->setErrorByName('mapping', $this->t('The main tag must not be empty.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    // We generate the machine name from the main tag in case the entity is new.
    if ($entity->isNew()) {
      /** @var \Drupal\thunder_print\MachineNameGeneratorInterface $generator */
      $generator = \Drupal::service('thunder_print.machine_name');
      $generator->setExistsCallback('\Drupal\thunder_print\Entity\TagMapping::load');
      $entity->set('id', $generator->generateUniqueMachineName($entity->getMainTag()));
    }
    return $entity;
  }
}
