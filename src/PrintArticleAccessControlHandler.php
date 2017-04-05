<?php

namespace Drupal\thunder_print;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Print article entity.
 *
 * @see \Drupal\thunder_print\Entity\PrintArticle.
 */
class PrintArticleAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished print article entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published print article entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit print article entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete print article entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add print article entities');
  }

}
