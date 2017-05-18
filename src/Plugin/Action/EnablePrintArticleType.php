<?php

namespace Drupal\thunder_print\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\Entity\PrintArticleTypeInterface;

/**
 * Enable a print article type entity.
 *
 * @Action(
 *   id = "print_article_type_enable_action",
 *   label = @Translation("Enable print article type"),
 *   type = "print_article_type"
 * )
 */
class EnablePrintArticleType extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(PrintArticleTypeInterface $entity = NULL) {
    $entity->setStatus(TRUE)->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $object */
    $result = $object->access('update', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }

}
