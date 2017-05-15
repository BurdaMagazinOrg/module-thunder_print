<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Defines an interface for Idms builder plugins.
 */
interface IdmsBuilderInterface extends PluginInspectionInterface {

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

}
