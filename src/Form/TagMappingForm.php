<?php

namespace Drupal\thunder_print\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
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
      ];

      $targets = $this->entity->getConvertTargets();
      $target = reset($targets);
      $form['configuration']['convert_targets']['entity_type'] = [
        '#type' => 'textfield',
        '#title' => 'entity type',
        '#default_value' => !empty($target['entity_type']) ? $target['entity_type'] : '',
      ];
      $form['configuration']['convert_targets']['property_path'] = [
        '#type' => 'textfield',
        '#title' => 'property_path',
        '#default_value' => !empty($target['property_path']) ? $target['property_path'] : '',
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
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    parent::copyFormValuesToEntity($entity, $form, $form_state);
    $entity->set('convert_targets', [$form_state->getValue('convert_targets')]);
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
