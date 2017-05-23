<?php

namespace Drupal\thunder_print\Plugin\views\field;

use Drupal\thunder_print\Entity\PrintArticleType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for print article type thumbnail.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("print_article_type_thumbnail")
 */
class PrintArticleTypeThumbnail extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $printArticleType = PrintArticleType::load($values->id);
    if ($thumbnail_url = $printArticleType->getThumbnailUrl()) {
      return [
        '#theme' => 'image',
        '#uri' => $thumbnail_url,
        '#height' => 50,
      ];
    }
  }

}
