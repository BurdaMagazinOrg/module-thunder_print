<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class PrintArticleTypeTest extends KernelTestBase {

  use TagMappingTrait;

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
  ];

  /**
   * Test the proper saving of the print article type.
   */
  public function testBundleCreation() {

    $this->createTagMappings();

    $bundle = $this->container->get('entity_type.manager')
      ->getStorage('print_article_type')
      ->create([
        'id' => $this->randomMachineName(),
        'label' => 'Test',
        'grid' => 12,
        'idms' => file_get_contents(dirname(__FILE__) . '/../../fixtures/Zeitung1.idms'),
      ]);

    $bundle->save();
  }

  /**
   * Test the proper saving of the print article type.
   */
  public function testMultipleTagsException() {

    $this->setExpectedException('Exception', 'The xml contains a multiple amount of "XMLTag/Textabschnitt" tags.');

    $storage = $this->container->get('entity_type.manager')
      ->getStorage('thunder_print_tag_mapping');

    /** @var \Drupal\thunder_print\Entity\TagMapping $tag_xmltag_story */
    $tag_xmltag_story = $storage->create([
      'id' => 'xmltag_story',
      'mapping_type' => 'text_formatted_long',
      'mapping' => [
        'value' => 'XMLTag/Textabschnitt',
      ],
      'options' => [],
    ]);
    $tag_xmltag_story->validate();
    $tag_xmltag_story->save();

    $bundle = $this->container->get('entity_type.manager')
      ->getStorage('print_article_type')
      ->create([
        'id' => $this->randomMachineName(),
        'label' => 'Test',
        'grid' => 12,
        'idms' => file_get_contents(dirname(__FILE__) . '/../../fixtures/Zeitung2.idms'),
      ]);

    $bundle->save();
  }

  /**
   * Test the proper saving of the print article type.
   */
  public function testNotMatchingTagsException() {

    $this->setExpectedException('Exception', "IDMS doesn't contain defined tags from the tag-mapping.");

    $this->createMediaBundle();

    $storage = $this->container->get('entity_type.manager')
      ->getStorage('thunder_print_tag_mapping');

    /** @var \Drupal\thunder_print\Entity\TagMapping $tag_xmltag_story */
    $tag_xmltag_story = $storage->create([
      'id' => 'xmltag_story',
      'mapping_type' => 'text_formatted_long',
      'mapping' => [
        'value' => 'XMLTag/Story1',
      ],
      'options' => [
        'widget_type' => 'text_textarea',
      ],
    ]);
    $tag_xmltag_story->validate();
    $tag_xmltag_story->save();

    /** @var \Drupal\thunder_print\Entity\TagMapping $tag_xmltag_image */
    $tag_xmltag_image = $storage->create([
      'id' => 'xmltag_image',
      'mapping_type' => 'media_entity',
      'mapping' => [
        'field_image' => 'XMLTag/Image1',
        'field_description' => 'XMLTag/Caption1',
      ],
      'options' => [
        'bundle' => 'image',
        'widget_type' => 'entity_reference_autocomplete',
        'field_settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ],
    ]);
    $tag_xmltag_image->validate();
    $tag_xmltag_image->save();

    $bundle = $this->container->get('entity_type.manager')
      ->getStorage('print_article_type')
      ->create([
        'id' => $this->randomMachineName(),
        'label' => 'Test',
        'grid' => 12,
        'idms' => file_get_contents(dirname(__FILE__) . '/../../fixtures/Zeitung1.idms'),
      ]);

    $bundle->save();
  }

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
      $this->assertSame($fieldType, $field->getFieldStorageDefinition()
        ->getType());
    }

  }

}
