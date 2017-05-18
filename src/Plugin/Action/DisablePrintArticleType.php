<?php

namespace Drupal\thunder_print\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\Entity\PrintArticleTypeInterface;

/**
 * Disable a print article type entity.
 *
 * @Action(
 *   id = "print_article_type_disable_action",
 *   label = @Translation("Disable print article type"),
 *   type = "print_article_type"
 * )
 */
class DisablePrintArticleType extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute(PrintArticleTypeInterface $entity = NULL) {
    $entity->setStatus(FALSE)->save();
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
