<?php

namespace Drupal\thunder_print;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Font entity.
 *
 * @see \Drupal\thunder_print\Entity\Font.
 */
class FontAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\thunder_print\Entity\FontInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::forbidden();

      case 'update':
        return AccessResult::forbidden();

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete font entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add font entities');
  }

}
