<?php

namespace Drupal\thunder_print\Plugin\IdmsBuilder;

use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderBase;

/**
 * Provides Tag Mapping for media entity reference.
 *
 * @IdmsBuilder(
 *   id = "embedded",
 *   label = @Translation("Local builder"),
 *   buildMode = "singlefile"
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
