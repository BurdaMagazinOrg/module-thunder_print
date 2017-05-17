<?php

namespace Drupal\thunder_print\Plugin\views\field;

use Drupal\thunder_print\Entity\PrintArticleType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for print article type status.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("print_article_type_status")
 */
class PrintArticleTypeStatus extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $printArticleType = PrintArticleType::load($values->id);
    if ($printArticleType->status()) {
      return $this->t('Enabled');
    }
    else {
      return $this->t('Disabled');
    }
  }

}
