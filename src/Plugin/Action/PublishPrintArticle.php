<?php

namespace Drupal\thunder_print\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Publishes a print article entity.
 *
 * @Action(
 *   id = "print_article_publish_action",
 *   label = @Translation("Publish print article"),
 *   type = "print_article"
 * )
 */
class PublishPrintArticle extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(PrintArticleInterface $entity = NULL) {
    $entity->setPublished(TRUE)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $object */
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->status->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
