<?php

namespace Drupal\thunder_print\Form;

use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Trait PrintArticleFormTrait.
 */
trait PrintArticleFormTrait {

  /**
   * Queues the preview image generation for a saved article.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $article
   *   Article object.
   *
   * @return string
   *   Job ID returned by the Indesign server.
   */
  protected function queuePreviewImageCreation(PrintArticleInterface $article) {
    $jobId = $this->indesignServer->createIdmsJob($article);

    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $queue = $this->queueFactory->get('thunder_print_idms_thumbnail_collector');
    $item = [
      'job_id' => $jobId,
      'print_article_id' => $article->id(),
    ];

    $queue->createItem($item);
    return $jobId;
  }

}
