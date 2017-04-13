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

    $data['print_article']['print_article_bulk_form'] = [
      'title' => $this->t('Print article operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple print article entities.'),
      'field' => [
        'id' => 'bulk_form',
      ],
    ];

    return $data;
  }

}
