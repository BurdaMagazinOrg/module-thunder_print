<?php

namespace Drupal\thunder_print\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that starts a watcher on the idms job queue.
 *
 * @ingroup ajax
 */
class QuickPreviewCommand implements CommandInterface {

  /**
   * Id of print article.
   *
   * @var string
   */
  protected $printArticleId;

  /**
   * Job id of the current running job.
   *
   * @var string
   */
  protected $jobId;

  /**
   * Constructs a InitQueueWatcherCommand object.
   *
   * @param string $jobId
   *   Job id of the current running job.
   */
  public function __construct($jobId) {
    $this->jobId = $jobId;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'thunderPrintQuickPreview',
      'job_id' => $this->jobId,
    ];
  }

}
