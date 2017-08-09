<?php

namespace Drupal\thunder_print;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Font entities.
 *
 * @ingroup thunder_print
 */
class FontListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['font'] = $this->t('Font');
    $header['font_style'] = $this->t('Font style');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\thunder_print\Entity\Font */
    $row['font'] = Link::createFromRoute(
      $entity->get('font')->value,
      'entity.thunder_print_font.edit_form',
      ['thunder_print_font' => $entity->id()]
    );
    $row['font_style'] = $entity->get('font_style')->value;
    return $row + parent::buildRow($entity);
  }

}
