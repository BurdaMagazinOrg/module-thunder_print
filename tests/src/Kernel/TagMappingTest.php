<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\media_entity\Entity\MediaBundle;

/**
 * Tests TagMapping storage basics.
 *
 * @group thunder_print
 */
class TagMappingTest extends KernelTestBase {

  use TagMappingTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_print',
    'text',
    'media_entity',
    'media_entity_image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $imageBundle = MediaBundle::create([
      'id' => 'image',
      'label' => 'image',
      'type' => 'image',
      'type_configuration' => [
        'source_field' => 'field_image',
      ],
    ]);
    $imageBundle->save();
  }

  /**
   * Test saving of a tag mapping.
   *
   * @dataProvider tagMappingProvider
   */
  public function testTagMappingCreation($data) {

    $storage = $this->container->get('entity_type.manager')
      ->getStorage('thunder_print_tag_mapping');

    /** @var \Drupal\thunder_print\Entity\TagMapping $tag_xmltag_story */
    $tag_xmltag_story = $storage->create($data);
    $tag_xmltag_story->validate();
    $tag_xmltag_story->save();
  }

}
