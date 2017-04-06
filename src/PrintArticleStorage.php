<?php

namespace Drupal\thunder_print;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Defines the storage handler class for Print article entities.
 *
 * This extends the base storage class, adding required special handling for
 * Print article entities.
 *
 * @ingroup thunder_print
 */
class PrintArticleStorage extends SqlContentEntityStorage implements PrintArticleStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(PrintArticleInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {print_article_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {print_article_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(PrintArticleInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {print_article_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('print_article_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
