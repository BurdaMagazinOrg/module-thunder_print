<?php

namespace Drupal\thunder_print\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Print article entities.
 */
class PrintArticleViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
