<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class PrintArticleTypeTest extends KernelTestBase {

  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_print',
    'field',
    'text',
    'media_entity',
    'entity_browser',
  ];

  /**
   * Test the automatic generation of fields on bundle creation.
   */
  public function testBundleFieldCreation() {

    $this->createTagMappings();

    $bundle_name = 'test';

    $values = [
      'id' => $bundle_name,
      'label' => 'Test',
      'grid' => 12,
      'idms' => file_get_contents(dirname(__FILE__) . '/../../fixtures/Zeitung1.idms'),
    ];

    $fields = [
      'xmltag_story' => 'text_long',
      'xmltag_image' => 'entity_reference',
    ];

    // Check that fields does not exists.
    foreach ($fields as $field) {
      $field = $this->container->get('entity_type.manager')
        ->getStorage('field_config')
        ->load("print_article.$bundle_name.$field");

      $this->assertNull($field);
    }

    $bundle = $this->container->get('entity_type.manager')
      ->getStorage('print_article_type')
      ->create($values);

    $bundle->save();

    // Check that fields exists now.
    foreach ($fields as $fieldName => $fieldType) {
      /** @var \Drupal\field\Entity\FieldConfig $field */
      $field = $this->container->get('entity_type.manager')
        ->getStorage('field_config')
        ->load("print_article.$bundle_name.$fieldName");

      $this->assertNotNull($field);
      $this->assertSame($fieldType, $field->getFieldStorageDefinition()->getType());
    }

  }

  /**
   * Create tag mappings.
   */
  protected function createTagMappings() {

    $storage = $this->container->get('entity_type.manager')
      ->getStorage('thunder_print_tag_mapping');

    $tag_xmltag_story = $storage->create([
      'id' => 'xmltag_story',
      'mapping_type' => 'text_formatted_long',
      'mapping' => [
        [
          'property' => 'value',
          'tag' => 'XMLTag/Story',
        ],
      ],
      'options' => [],
    ]);
    $tag_xmltag_story->validate();
    $tag_xmltag_story->save();

    $tag_xmltag_image = $storage->create([
      'id' => 'xmltag_image',
      'mapping_type' => 'media_image',
      'mapping' => [
        [
          'property' => 'field_image',
          'tag' => 'XMLTag/Image',
        ],
        [
          'property' => 'field_description',
          'tag' => 'XMLTag/Caption',
        ],
      ],
      'options' => [],
    ]);
    $tag_xmltag_image->validate();
    $tag_xmltag_image->save();
  }

}
