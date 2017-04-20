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

    /** @var \Drupal\thunder_print\Plugin\TagMappingTypeManager $mapping_type_manager */
    $mapping_type_manager = \Drupal::service('plugin.manager.thunder_print_tag_mapping_type');

    $wrapper_id = Html::getId('tap-mapping-form-ajax-wrapper');

    $form['mapping_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Mapping type'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->getMappingTypeId(),
      '#description' => $this->t("Type for the mapping."),
      '#options' => $mapping_type_manager->getOptions(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => $wrapper_id,
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $plugin = $this->entity->getMappingType();

    $form['configuration'] = [
      '#type' => 'container',
      '#id' => $wrapper_id,
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
            '#default_value' => $this->entity->getTag($property),
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
    $status = $this->entity->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Tag Mapping.', [
          '%label' => $this->entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Tag Mapping.', [
          '%label' => $this->entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->entity->validate();
    $this->buildEntityId();
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

    $violations = $new_entity->validate();
    if ($violations->count()) {

      /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
      foreach ($violations as $violation) {
        $form_state->setErrorByName($violation->getPropertyPath(), $violation->getMessage());
      }
    }
  }

  /**
   * Ensures the entity ID is generated from the main tag on creation.
   */
  protected function buildEntityId() {
    // We generate the machine name from the main tag in case the entity is new.
    if ($this->entity->isNew()) {
      /** @var \Drupal\thunder_print\MachineNameGeneratorInterface $generator */
      $generator = \Drupal::service('thunder_print.machine_name');
      $generator->setExistsCallback('\Drupal\thunder_print\Entity\TagMapping::load');
      $this->entity->set('id', $generator->generateUniqueMachineName($this->entity->getMainTag()));
    }
  }

}
