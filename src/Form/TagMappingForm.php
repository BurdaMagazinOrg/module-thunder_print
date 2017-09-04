<?php

namespace Drupal\thunder_print\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder_print\MachineNameGeneratorInterface;
use Drupal\thunder_print\Plugin\TagMappingTypeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TagMappingForm.
 *
 * @package Drupal\thunder_print\Form
 *
 * @property \Drupal\thunder_print\Entity\TagMappingInterface $entity
 */
class TagMappingForm extends EntityForm {

  /**
   * Service for handling tag Mapping type plugins.
   *
   * @var \Drupal\thunder_print\Plugin\TagMappingTypeManager
   */
  protected $mappingTypeManager;

  /**
   * Machine name generator.
   *
   * @var \Drupal\thunder_print\MachineNameGeneratorInterface
   */
  protected $machineNameGenerator;

  /**
   * TagMappingForm constructor.
   *
   * @param \Drupal\thunder_print\Plugin\TagMappingTypeManager $manager
   *   Manager for accessing tag mapping types.
   * @param \Drupal\thunder_print\MachineNameGeneratorInterface $machine_name_generator
   *   Machine name generator service.
   */
  public function __construct(TagMappingTypeManager $manager, MachineNameGeneratorInterface $machine_name_generator) {
    $this->mappingTypeManager = $manager;
    $this->machineNameGenerator = $machine_name_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.thunder_print_tag_mapping_type'),
      $container->get('thunder_print.machine_name')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $wrapper_id = Html::getId('tap-mapping-form-ajax-wrapper');

    $form['mapping_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Mapping type'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->getMappingTypeId(),
      '#description' => $this->t("Type for the mapping."),
      '#options' => $this->mappingTypeManager->getOptions(),
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

      $options_form = $plugin->optionsForm([], $form_state);

      if (!empty($options_form)) {
        $form['configuration']['options'] = [
          '#tree' => TRUE,
          '#type' => 'fieldset',
          '#title' => $this->t('Options'),
        ] + $options_form;
      }

      $properties = $plugin->getPropertyDefinitions();
      $form['configuration']['mapping'] = [
        '#tree' => TRUE,
        '#type' => 'fieldset',
        '#title' => $this->t('Mapping'),
      ];

      foreach ($properties as $property => $spec) {
        $form['configuration']['mapping'][$property] = [
          '#type' => 'textfield',
          '#title' => $spec['name'],
          '#required' => !empty($spec['required']),
          '#default_value' => $this->entity->getTag($property),
        ];
      }

      $form['configuration']['convert_targets'] = [
        '#tree' => TRUE,
        '#type' => 'fieldset',
        '#title' => $this->t('Convert targets'),
        '#prefix' => '<div id="modules-wrapper">',
        '#suffix' => '</div>',
      ];

      $form['#attached']['library'][] = 'thunder_print/thunder_print.autocomplete';

      $targets = $this->entity->getConvertTargets();
      $max = $form_state->get('fields_count');
      if (is_null($max)) {
        $max = max(1, count($targets));
        $form_state->set('fields_count', $max);
      }

      for ($i = 0; $i < $max; $i++) {
        $form['configuration']['convert_targets'][] = [
          '#type' => 'textfield',
          '#title' => $this->t('Property path'),
          '#default_value' => !empty($targets[$i]) ? $targets[$i] : '',
          '#autocomplete_route_name' => 'thunder_print.tag_mapping.autocomplete',
          '#autocomplete_route_parameters' => array('type' => implode(',', $plugin->getPossibleConvertTargets())),
          '#attributes' => [
            'class' => ['property_path-autocomplete'],
          ],
        ];
      }

      $form['configuration']['convert_targets']['more_fields'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add one'),
        '#submit' => ['::addFieldSubmit'],

        '#ajax' => [
          'callback' => '::addFieldAjaxCallback',
          'wrapper' => 'modules-wrapper',
        ],
      ];
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
    $targets = $form_state->getValue('convert_targets');
    $form_state->setValue('convert_targets', array_filter($targets));

    parent::submitForm($form, $form_state);
    $this->entity->validate();
    $this->buildEntityId();
  }

  /**
   * Ajax submit to add new field.
   */
  public function addFieldSubmit(array &$form, FormStateInterface &$form_state) {
    $max = $form_state->get('fields_count') + 1;
    $form_state->set('fields_count', $max);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Callback for ajax requests.
   */
  public static function addFieldAjaxCallback(array $form, FormStateInterface $form_state) {
    return $form['configuration']['convert_targets'];
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
      $this->entity->set('id', $this->machineNameGenerator->generateUniqueMachineName($this->entity->getMainTag(), '\Drupal\thunder_print\Entity\TagMapping::load'));
    }
  }

}
