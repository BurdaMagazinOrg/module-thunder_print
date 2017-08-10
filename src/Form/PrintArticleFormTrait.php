<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\thunder_print\Ajax\QuickPreviewCommand;
use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Trait PrintArticleFormTrait.
 */
trait PrintArticleFormTrait {

  /**
   * Empty image data.
   *
   * @var string
   */
  static protected $emptyImagaDataUri = "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==";

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

  /**
   * Grab a quick preview from InDesign server.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   Print article.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Response with command.
   */
  public function genericAjaxQuickPreviewCallback(PrintArticleInterface $printArticle) {

    drupal_get_messages();

    try {
      $jobId = $this->indesignServer->createIdmsJob($printArticle);

      $response = new AjaxResponse();
      $response->addCommand(new QuickPreviewCommand($jobId, '#thunder-print-preview-image'));

      return $response;
    }
    catch (\Exception $e) {
      drupal_set_message($this->t('Error while generating preview.'), 'error');
    }
  }

}
