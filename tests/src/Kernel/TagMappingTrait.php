<?php

namespace Drupal\Tests\thunder_print\Kernel;

/**
 * Class TagMappingTrait.
 */
trait TagMappingTrait {

  /**
   * Create tag mappings.
   */
  protected function createTagMappings() {

    $storage = $this->container->get('entity_type.manager')
      ->getStorage('thunder_print_tag_mapping');

    $providedData = $this->tagMappingProvider();
    foreach ($providedData as $data) {
      /** @var \Drupal\thunder_print\Entity\TagMapping $tag_xmltag_story */
      $tag_xmltag_story = $storage->create($data[0]);
      $tag_xmltag_story->validate();
      $tag_xmltag_story->save();
    }
  }

  /**
   * Data provider for tag mapping config data.
   *
   * @return array
   *   Different tag mappings for testing.
   */
  public function tagMappingProvider() {
    return [
      'title' => [
        [
          'id' => 'xmltag_title',
          'mapping_type' => 'text_plain',
          'mapping' => [
            'value' => 'XMLTag/Title',
          ],
          'options' => [
            'title' => TRUE,
          ],
        ],
      ],
      'long_text' => [
        [
          'id' => 'xmltag_story',
          'mapping_type' => 'text_formatted_long',
          'mapping' => [
            'value' => 'XMLTag/Story',
          ],
          'options' => [],
        ],
      ],
      'image' => [
        [
          'id' => 'xmltag_image',
          'mapping_type' => 'media_image',
          'mapping' => [
            'field_image' => 'XMLTag/Image',
            'field_description' => 'XMLTag/Caption',
          ],
          'options' => [],
        ],
      ],
    ];
  }

}
