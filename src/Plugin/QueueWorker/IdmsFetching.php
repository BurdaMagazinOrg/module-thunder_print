<?php

namespace Drupal\thunder_print\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\thunder_print\IndesignServer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IdmsGeneration.
 *
 * @QueueWorker(
 *   id = "thunder_print_idms_fetching",
 *   title = @Translation("Thunder print idms fetching"),
 *   cron = {"time" = 10}
 * )
 */
class IdmsFetching extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\thunder_print\IndesignServer
   */
  protected $indesignServer;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, IndesignServer $indesignServer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->indesignServer = $indesignServer;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('thunder_print.indesign_server')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    
    $body = $this->indesignServer->fetchJob($data['job_id']);


    $printArticle = \Drupal::service('entity_type.manager')->getStorage('print_article')->load($data['print_article_id']);



    $zip = new \ZipArchive();
    $zipFilename = tempnam("tmp", "zip");

    file_put_contents($zipFilename, $body);

    $zip->open($zipFilename);

    $dir = 'public://print-article/';
    file_prepare_directory($dir, FILE_CREATE_DIRECTORY);
    $thumbnail = file_save_data($zip->getFromName('preview.jpg'), 'public://print-article/' . $printArticle->label() . '-preview.jpg', FILE_EXISTS_REPLACE);

    $printArticle->set('image', $thumbnail);
    $printArticle->save();

    $zip->close();
    unlink($zipFilename);

  }

}
