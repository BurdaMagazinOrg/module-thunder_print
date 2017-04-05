<?php

namespace Drupal\thunder_print;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface PrintArticleStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Print article revision IDs for a specific Print article.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $entity
   *   The Print article entity.
   *
   * @return int[]
   *   Print article revision IDs (in ascending order).
   */
  public function revisionIds(PrintArticleInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Print article author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Print article revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $entity
   *   The Print article entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(PrintArticleInterface $entity);

  /**
   * Unsets the language for all Print article with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
