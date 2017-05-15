<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Defines an interface for Idms builder plugins.
 */
interface IdmsBuilderInterface extends PluginInspectionInterface {

  /**
   * Returns a unified response.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   The print article.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   A generic stream response.
   */
  public function getResponse(PrintArticleInterface $printArticle);

  /**
   * Binary content that can be streamed.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   The print article.
   *
   * @return string
   *   The content.
   */
  public function getContent(PrintArticleInterface $printArticle);

  /**
   * Filename for the returned file.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   The print article.
   *
   * @return string
   *   Filename.
   */
  public function getFilename(PrintArticleInterface $printArticle);

  /**
   * Use snippet template from bundle and replaces the placeholder with content.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   The print article.
   *
   * @return \Drupal\thunder_print\IDMS
   *   New IDMS with replaced content.
   */
  public function replaceSnippetPlaceholders(PrintArticleInterface $printArticle);

}
