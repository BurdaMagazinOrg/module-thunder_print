<?php

namespace Drupal\thunder_print;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Class ArticleConvert.
 *
 * @package Drupal\thunder_print
 */
class ArticleConverter {

  protected $entityFieldManager;

  protected $entityTypeManager;

  protected $entityReferenceSelectionManager;

  /**
   * ArticleConvert constructor.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   Field manager service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selectionPluginManager
   *   Entity reference selection plugin manager.
   */
  public function __construct(EntityFieldManagerInterface $entityFieldManager, EntityTypeManagerInterface $entityTypeManager, SelectionPluginManagerInterface $selectionPluginManager) {

    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityReferenceSelectionManager = $selectionPluginManager;
  }

  /**
   * Converts a print article into an other one.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   Print article.
   * @param \Drupal\Core\Config\Entity\ConfigEntityBundleBase $bundleBase
   *   Bundle for new article.
   */
  public function printToOnline(PrintArticleInterface $printArticle, ConfigEntityBundleBase $bundleBase) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $printArticleType */
    $printArticleType = $printArticle->type->entity;

    $entity_type_id = $bundleBase->getEntityType()->getBundleOf();
    $bundle = $bundleBase->id();

    $entity = $this->entityTypeManager->getStorage($entity_type_id)
      ->create(['type' => $bundle, 'title' => 'Thunder 4 Print entity']);
    $entity->save();

    foreach ($printArticleType->getMappings() as $fieldName => $mapping) {

      foreach ($mapping->getConvertTargets() as $target) {
        if ($target['entity_type'] == $entity_type_id . ':' . $bundle) {
          $paths = explode('::', $target['property_path']);
          $this->setValue($paths, $entity, $printArticle->{$fieldName});
        }
      }
    }

    $entity->save();
  }

  /**
   * Sets value to a property path.
   *
   * @param array $property_path
   *   Property path.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to set the value on.
   * @param mixed $value
   *   The value to set.
   */
  protected function setValue(array $property_path, EntityInterface $entity, $value) {

    $fields = $this->entityFieldManager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());

    $path = array_shift($property_path);

    list($fieldName, $bundle) = explode(':', $path);

    if (empty($fields[$fieldName])) {
      return;
    }

    $fieldConfig = $fields[$fieldName];

    // If bundle is defined, it's an ER field.
    if ($bundle) {

      $target_type = $fieldConfig->getFieldStorageDefinition()
        ->getSetting('target_type');

      /** @var \Drupal\Core\Entity\EntityInterface $newEntity */
      $newEntity = $this->entityReferenceSelectionManager->getSelectionHandler($fieldConfig)
        ->createNewEntity($target_type, $bundle, 'Thunder 4 Print migrated', 0);

      $entity->{$fieldName}[] = $newEntity;
      if (!empty($property_path)) {
        $this->setValue($property_path, $newEntity, $value);
      }
    }
    else {
      $entity->{$fieldName} = $value;
      $entity->save();
    }

  }

}
