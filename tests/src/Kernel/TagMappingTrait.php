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

    /** @var \Drupal\thunder_print\Entity\TagMapping $tag_xmltag_story */
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

    /** @var \Drupal\thunder_print\Entity\TagMapping $tag_xmltag_image */
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
