<?php

namespace Drupal\thunder_print\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class PrintArticleController.
 *
 *  Returns responses for Print article routes.
 *
 * @package Drupal\thunder_print\Controller
 */
class PrintArticleTypeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public function disable($print_article_type) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
    $print_article_type = $this->entityTypeManager()
      ->getStorage('print_article_type')
      ->load($print_article_type);

    $print_article_type->disable()
      ->save();

    return $this->redirect('entity.print_article_type.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function enable($print_article_type) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
    $print_article_type = $this->entityTypeManager()
      ->getStorage('print_article_type')
      ->load($print_article_type);

    $print_article_type->enable()
      ->save();

    return $this->redirect('entity.print_article_type.collection');
  }

}
