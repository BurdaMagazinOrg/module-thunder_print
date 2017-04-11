<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\thunder_print\IDMS;

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
  public function getIdms() {
    return $this->idms;
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

    $idms = new IDMS($this->idms);

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
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    $idms = new IDMS($this->idms);

    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
    $errors = $idms->validate();

    if ($errors->count()) {
      throw new \Exception('IDMS file not valid.');
    }
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
   * Create fields for the current bundle.
   */
  public function createBundleFields() {

    $mapping = [
      'tag1' => 'text',
    ];

    $entity_type_id = $this->getEntityType()->getBundleOf();

    /** @var \Drupal\Core\Entity\Entity\EntityFormDisplay $form_display */
    $form_display = $this->entityTypeManager()
      ->getStorage('entity_form_display')
      ->create([
        'targetEntityType' => $entity_type_id,
        'bundle' => $this->id(),
        'mode' => 'default',
        'status' => TRUE,
      ]);

    foreach ($mapping as $item => $value) {
      $fieldStorage = $this->entityTypeManager()
        ->getStorage('field_storage_config')
        ->load("$entity_type_id.$item");

      if (!$fieldStorage) {
        $fieldStorage = $this->entityTypeManager()
          ->getStorage('field_storage_config')
          ->create([
            'field_name' => $item,
            'entity_type' => $entity_type_id,
            'type' => 'text',
            'cardinality' => 1,
            'locked' => TRUE,
          ]);
        $fieldStorage->save();
      }

      $this->entityTypeManager()
        ->getStorage('field_config')
        ->create([
          'field_storage' => $fieldStorage,
          'bundle' => $this->id(),
          'label' => $item,
          'translatable' => FALSE,
        ])->save();

      $form_display->setComponent($item, [
        'type' => 'text_textfield',
        'settings' => [
          'size' => 60,
          'placeholder' => '',
        ],
      ]);
    }

    $form_display->save();
  }

}
