<?php

namespace Drupal\thunder_print\Plugin\IdmsBuilder;

use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderBase;

/**
 * Provides an embedded idms builder.
 *
 * @IdmsBuilder(
 *   id = "embedded",
 *   label = @Translation("Local builder")
 * )
 */
class EmbeddedBuilder extends IdmsBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getContent(PrintArticleInterface $printArticle) {

    $replacedIdms = $this->replaceSnippetPlaceholders($printArticle);

    return $replacedIdms->getXml()->asXml();
  }

  /**
   * {@inheritdoc}
   */
  public function getFilename(PrintArticleInterface $printArticle) {
    return $printArticle->label() . '.idms';
  }

}
