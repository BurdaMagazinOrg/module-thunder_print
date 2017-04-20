<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\thunder_print\Validator\Constraints\TagMappingTagsNotExist;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validation;

/**
 * Defines the Tag Mapping entity.
 *
 * @ConfigEntityType(
 *   id = "thunder_print_tag_mapping",
 *   label = @Translation("Tag Mapping"),
 *   handlers = {
 *     "list_builder" = "Drupal\thunder_print\TagMappingListBuilder",
 *     "form" = {
 *       "add" = "Drupal\thunder_print\Form\TagMappingForm",
 *       "edit" = "Drupal\thunder_print\Form\TagMappingForm",
 *       "delete" = "Drupal\thunder_print\Form\TagMappingDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\thunder_print\TagMappingHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "tag_mapping",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/thunder_print/tag_mapping/{thunder_print_tag_mapping}",
 *     "add-form" = "/admin/structure/thunder_print/tag_mapping/add",
 *     "edit-form" = "/admin/structure/thunder_print/tag_mapping/{thunder_print_tag_mapping}/edit",
 *     "delete-form" = "/admin/structure/thunder_print/tag_mapping/{thunder_print_tag_mapping}/delete",
 *     "collection" = "/admin/structure/thunder_print/tag_mapping"
 *   }
 * )
 */
class TagMapping extends ConfigEntityBase implements TagMappingInterface {

  /**
   * The Tag Mapping ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The machine name of the mapping type plugin to use.
   *
   * @var string
   */
  protected $mapping_type;

  /**
   * Instance of the mapping type manager service.
   *
   * @var \Drupal\thunder_print\Plugin\TagMappingTypeManager
   */
  protected $mapping_type_manager;

  /**
   * Holds mapping of tags to mapping properties.
   *
   * Each item consists of 'property' and 'tag'. It is defined by the schema
   * defintion `thunder_print.tag_mapping_map`.
   *
   * @var array
   */
  protected $mapping = [];

  /**
   * Holds optional plugin-specific options as key value pairs.
   *
   * @var array
   */
  protected $options = [];

  /**
   * Whether entity validation was performed.
   *
   * @var bool
   */
  protected $validated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getMainTag();
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingTypeId() {
    return $this->mapping_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapping() {
    $return = [];
    foreach ($this->mapping as $spec) {
      $return[$spec['property']] = $spec['tag'];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getTags() {
    return array_unique(array_values($this->getMapping()));
  }

  /**
   * {@inheritdoc}
   */
  public function getTag($property) {
    $mapping = $this->getMapping();
    if (isset($mapping[$property])) {
      return $mapping[$property];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingType() {
    if (!empty($this->mapping_type) && $this->getMappingTypeManager()->hasDefinition($this->mapping_type)) {
      $plugin = $this->getMappingTypeManager()->createInstance($this->mapping_type,
        [
          'mapping' => $this->getMapping(),
          'options' => $this->options,
        ]
      );
      return $plugin;
    }
  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\Core\Entity\ContentEntityBase::preSave()
   */
  public function preSave(EntityStorageInterface $storage) {
    // The entity should not be saved, unless it was validated succesfully.
    if (!$this->validated) {
      throw new \LogicException('Entity validation was skipped.');
    }
    // We reset validated to FALSE, so any further changes would end up in
    // checking validation again.
    else {
      $this->validated = FALSE;
    }

    parent::preSave($storage);
  }

  /**
   * Provides mapping type manager for internal usage.
   *
   * @return \Drupal\thunder_print\Plugin\TagMappingTypeManager
   *   Plugin manager for tag mapping types.
   */
  protected function getMappingTypeManager() {
    if (!isset($this->mapping_type_manager)) {
      $this->mapping_type_manager = \Drupal::service('plugin.manager.thunder_print_tag_mapping_type');
    }
    return $this->mapping_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getMainTag() {
    $main_property = $this->getMappingType()->getMainProperty();
    return $this->getTag($main_property);
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    // @todo: config schema validation.
    // @todo: unique tags validation
    $this->validated = TRUE;
    $validator = Validation::createValidatorBuilder()
      ->addMethodMapping('loadValidatorMetadata')
      ->getValidator();
    return $validator->validate($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getIterator() {
    return new \ArrayIterator([
      'id' => $this->id,
      'mapping_type' => $this->mapping_type,
      'mapping' => $this->mapping,
      'options' => $this->options,
    ]);
  }

  /**
   * Provides metadata for validator.
   *
   * @param \Symfony\Component\Validator\Mapping\ClassMetadata $metadata
   *   Symfony valdiator metadata object.
   */
  public static function loadValidatorMetadata(ClassMetadata $metadata) {
    $metadata->addGetterMethodConstraint('mapping', 'getMainTag', new NotBlank(['message' => 'The main tag must not be empty.']));
    $metadata->addConstraint(new TagMappingTagsNotExist());
  }

  /**
   * Provides list of mappings keyed by tag.
   *
   * @return \Drupal\thunder_print\Entity\TagMappingInterface[]
   *   The keys are the tag name.
   */
  public static function loadMappingsByTag() {
    $all = static::loadMultiple();

    $tags = [];
    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $mapping */
    foreach ($all as $mapping) {
      $add = $mapping->getTags();
      foreach ($add as $tag) {
        $tags[$tag] = $mapping;
      }
    }
    return $tags;
  }

  /**
   * Loads a single mapping using the given tag.
   *
   * @param string $tag
   *   Tag (from IDMS file) to load a mapping for.
   *
   * @return \Drupal\thunder_print\Entity\TagMappingInterface
   *   Returns the mapping when mapping is found. Otherwise NULL is returned.
   */
  public static function loadMappingForTag($tag) {
    $map = static::loadMappingsByTag();
    if (isset($map[$tag])) {
      return $map[$tag];
    }
  }

}
