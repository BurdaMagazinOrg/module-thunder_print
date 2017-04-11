<?php

namespace Drupal\thunder_print;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Print article type entities.
 */
class PrintArticleTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    $header['thumbnail_file'] = [
      'data' => $this->t('Thumbnail'),
    ];
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    $row['thumbnail_file'] = [];
    if ($thumbnail_url = $entity->getThumbnailUrl()) {
      $row['thumbnail_file']['data'] = [
        '#theme' => 'image',
        '#uri' => $thumbnail_url,
        '#height' => 50,
      ];
    }
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

}
