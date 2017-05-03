<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests TagMapping storage basics.
 *
 * @group thunder_print
 */
class TagMappingTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_print',
    'text',
  ];

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

  /**
   * Data provider for tag mapping config data.
   *
   * @return array
   */
  public function tagMappingProvider() {
    return [
      'title' => [
        [
          'id' => 'xmltag_story',
          'mapping_type' => 'text_plain',
          'mapping' => [
            [
              'property' => 'value',
              'tag' => 'XMLTag/Title',
            ],
          ],
          'options' => [
            'title' => TRUE,
          ],
        ]
      ],
      'long_text' => [
        [
          'id' => 'xmltag_story',
          'mapping_type' => 'text_formatted_long',
          'mapping' => [
            [
              'property' => 'value',
              'tag' => 'XMLTag/Story',
            ],
          ],
          'options' => [],
        ]
      ],
      'image' => [
        [
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
        ]
      ]
    ];
  }


}