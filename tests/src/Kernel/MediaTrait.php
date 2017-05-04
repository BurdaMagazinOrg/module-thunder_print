<?php

namespace Drupal\Tests\thunder_print\Kernel;

/**
 * Class MediaTrait.
 */
trait MediaTrait {

  /**
   * Create tag mappings.
   */
  protected function createMediaBundle() {

    $entityTypeManager = $this->container->get('entity_type.manager');

    $imageBundle = $entityTypeManager->getStorage('media_bundle')
      ->create(
        [
          'id' => 'image',
          'label' => 'image',
          'type' => 'generic',
        ]);
    $imageBundle->save();

    $fieldStorage = $entityTypeManager
      ->getStorage('field_storage_config')
      ->create(
        [
          'field_name' => 'field_image',
          'entity_type' => 'media',
          'cardinality' => 1,
          'locked' => TRUE,
          'type' => 'entity_reference',
          'settings' => [
            'target_type' => 'media',
          ],
        ]);
    $fieldStorage->save();

    $fieldConfig = $entityTypeManager
      ->getStorage('field_config')
      ->create(
        [
          'field_storage' => $fieldStorage,
          'bundle' => 'image',
          'label' => 'foo',
          'required' => TRUE,
          'translatable' => FALSE,
          'handler' => 'default:media',
          'handler_settings' => [
            'target_bundles' => ['image'],
            'sort' => [
              'field' => '_none',
            ],
            'auto_create' => FALSE,
            'auto_create_bundle' => '',
          ],
        ]);
    $fieldConfig->save();
  }

}
