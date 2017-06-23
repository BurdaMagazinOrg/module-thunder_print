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
   * Job id of the current running job.
   *
   * @var string
   */
  protected $jobId;

  /**
   * Selector for the preview image.
   *
   * @var string
   */
  protected $selector;

  /**
   * Constructs a InitQueueWatcherCommand object.
   *
   * @param string $jobId
   *   Job id of the current running job.
   * @param string $selector
   *   Selector of the preview image
   */
  public function __construct($jobId, $selector) {
    $this->jobId = $jobId;
    $this->selector = $selector;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'thunderPrintQuickPreview',
      'job_id' => $this->jobId,
      'selector' => $this->selector,
    ];
  }

}
