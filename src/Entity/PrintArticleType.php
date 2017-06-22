<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Validator\Constraints\IdmsUniqueTags;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validation;

/**
 * Defines the Print article type entity.
 *
 * @ConfigEntityType(
 *   id = "print_article_type",
 *   label = @Translation("Snippet Template"),
 *   handlers = {
 *     "list_builder" = "Drupal\thunder_print\PrintArticleTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\thunder_print\Form\PrintArticleTypeAddForm",
 *       "edit" = "Drupal\thunder_print\Form\PrintArticleTypeForm",
 *       "delete" = "Drupal\thunder_print\Form\PrintArticleTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\thunder_print\PrintArticleTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "print_article_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "print_article",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "grid",
 *     "idms",
 *     "status",
 *     "thumbnail_uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/thunder_print/print_article_type/{print_article_type}",
 *     "add-form" = "/admin/structure/thunder_print/print_article_type/add",
 *     "edit-form" = "/admin/structure/thunder_print/print_article_type/{print_article_type}/edit",
 *     "delete-form" = "/admin/structure/thunder_print/print_article_type/{print_article_type}/delete",
 *     "collection" = "/admin/structure/thunder_print/print_article_type"
 *   }
 * )
 */
class PrintArticleType extends ConfigEntityBundleBase implements PrintArticleTypeInterface {

  /**
   * Machine name of the print article type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the print article type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this print article type.
   *
   * @var string
   */
  protected $description;

  /**
   * Grid size to render an idms.
   *
   * @var int
   */
  protected $grid;

  /**
   * The complete idms xml.
   *
   * @var string
   */
  protected $idms;

  /**
   * UUID of the print type icon file.
   *
   * @var string
   */
  protected $thumbnail_uuid;

  /**
   * Whether entity validation was performed.
   *
   * @var bool
   */
  protected $validated = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getGrid() {
    return $this->grid;
  }

  /**
   * {@inheritdoc}
   */
  public function getOriginalIdms() {
    return $this->idms;
  }

  /**
   * {@inheritdoc}
   */
  public function getNewIdms() {
    return new IDMS($this->idms);
  }

  /**
   * {@inheritdoc}
   */
  public function getThumbnailFile() {
    if ($this->thumbnail_uuid) {
      $files = $this->entityTypeManager()
        ->getStorage('file')
        ->loadByProperties(['uuid' => $this->thumbnail_uuid]);

      if ($files) {
        return array_shift($files);
      }
    }

    return $this->buildThumbnail();
  }

  /**
   * {@inheritdoc}
   */
  public function getThumbnailUrl() {
    if ($image = $this->getThumbnailFile()) {
      return file_create_url($image->getFileUri());
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildThumbnail() {

    if (!$this->idms) {
      return FALSE;
    }

    $idms = $this->getNewIdms();

    list ($data, $extension) = $idms->extractThumbnail();

    $dir = 'public://idms/';
    file_prepare_directory($dir, FILE_CREATE_DIRECTORY);
    $thumbnail = file_save_data($data, 'public://idms/' . $this->label . '.' . $extension, FILE_EXISTS_REPLACE);

    // Set the file UUID to the print article type configuration.
    if (!empty($thumbnail)) {
      $this->set('thumbnail_uuid', $thumbnail->uuid());
      $this->save();
    }
    else {
      $this->set('thumbnail_uuid', NULL);
      return FALSE;
    }

    return $thumbnail;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $violations = $this->validate();
    if ($violations->count()) {

      /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
      foreach ($violations as $violation) {
        throw new \Exception($violation->getMessage());
      }
    }
    return parent::save();
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
   * {@inheritdoc}
   */
  public function validate() {
    $this->validated = TRUE;

    $idms = $this->getNewIdms();

    // Validate IDMS.
    $idmsViolations = $idms->validate();

    // Validate $this.
    $printArticleTypeViolations = Validation::createValidatorBuilder()
      ->addMethodMapping('loadValidatorMetadata')
      ->getValidator()
      ->validate($this);

    $printArticleTypeViolations->addAll($idmsViolations);

    return $printArticleTypeViolations;
  }

  /**
   * Provides metadata for validator.
   *
   * @param \Symfony\Component\Validator\Mapping\ClassMetadata $metadata
   *   Symfony valdiator metadata object.
   */
  public static function loadValidatorMetadata(ClassMetadata $metadata) {
    $metadata->addGetterMethodConstraint('idms', 'getTags', new NotBlank(['message' => "IDMS doesn't contain defined tags from the tag-mapping."]));
    $metadata->addConstraint(new IdmsUniqueTags());
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    if (!$update) {
      $this->createBundleFields();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createBundleFields() {

    $entity_type_id = $this->getEntityType()->getBundleOf();

    foreach ($this->getMappings() as $tagMapping) {
      $tagMapping->createField($entity_type_id, $this->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMappings() {
    $tags = $this->getTags();
    $mappings = [];
    foreach ($tags as $mapping) {
      $mappings[$mapping->id()] = $mapping;
    }
    return $mappings;
  }

  /**
   * Retrieve mapping definition for given field.
   *
   * @param string $field_name
   *   The field name a mapping may be associated to.
   *
   * @return \Drupal\thunder_print\Entity\TagMappingInterface
   */
  public function getMappingForField($field_name) {
    $mappings = $this->getMappings();
    if (isset($mappings[$field_name])) {
      return $mappings[$field_name];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTags() {

    $idms = $this->getNewIdms();

    $tags = [];
    foreach ($idms->getTagNames() as $tag) {
      if ($tagMapping = TagMapping::loadMappingForTag($tag)) {
        $tags[$tag] = $tagMapping;
      }
    }

    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityCount() {

    return \Drupal::entityQuery($this->getEntityType()->getBundleOf())
      ->condition('type', $this->id())
      ->count()
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $tagMapping */
    foreach ($this->getMappings() as $tagMapping) {
      $dependencies->addDependency('config', $tagMapping->getConfigDependencyName());
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getSwitchableBundles() {

    $fieldManager = \Drupal::service('entity_field.manager');

    $fieldDefinitions = $fieldManager->getFieldDefinitions('print_article', $this->id());
    $fieldDefinitions = array_keys($fieldDefinitions);
    sort($fieldDefinitions);

    $all_print_article_types = $this->entityTypeManager()->getStorage('print_article_type')
      ->loadMultiple();

    $options = [];
    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $entity */
    foreach ($all_print_article_types as $entity) {
      $bundleFieldDefinitions = $fieldManager->getFieldDefinitions('print_article', $entity->id());
      $bundleFieldDefinitions = array_keys($bundleFieldDefinitions);
      sort($bundleFieldDefinitions);

      if ($entity->id() != $this->id() && empty(array_diff($fieldDefinitions, $bundleFieldDefinitions))) {
        $options[$entity->id()] = $entity;
      }
    }

    return $options;
  }

}
