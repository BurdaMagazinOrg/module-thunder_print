<?php

namespace Drupal\thunder_print\Plugin\IdmsBuilder;

use Drupal\thunder_print\Plugin\IdmsBuilderBase;

/**
 * Provides Tag Mapping for media entity reference.
 *
 * @IdmsBuilder(
 *   id = "local",
 *   label = @Translation("Local builder"),
 * )
 */
class LocalBuilder extends IdmsBuilderBase {

  /**
   * {@inheritdoc}
   */
  protected function getContent() {

    $replacedIdms = $this->replaceSnippetPlaceholders();

    return $replacedIdms->getXml()->asXml();
  }

  /**
   * {@inheritdoc}
   */
  protected function getFilename() {
    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle */
    $printArticle = $this->getPrintArticle();
    return $printArticle->label() . '.idms';
  }

}
