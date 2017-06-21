<?php

namespace Drupal\thunder_print\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\image\Entity\ImageStyle;
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
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Queue factory service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * Indesign server.
   *
   * @var \Drupal\thunder_print\IndesignServer
   */
  protected $indesignServer;

  /**
   * Constructs a IdmsFetching object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   Queue service.
   * @param \Drupal\thunder_print\IndesignServer $indesignServer
   *   Indesing server object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, QueueFactory $queueFactory, IndesignServer $indesignServer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->queueFactory = $queueFactory;
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
      $container->get('entity_type.manager'),
      $container->get('queue'),
      $container->get('thunder_print.indesign_server')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    if ($body = $this->indesignServer->fetchJob($data['job_id'])) {

      $printArticle = $this->entityTypeManager
        ->getStorage('print_article')
        ->load($data['print_article_id']);

      $zip = new \ZipArchive();
      $zipFilename = tempnam("tmp", "zip");

      file_put_contents($zipFilename, $body);

      $zip->open($zipFilename);

      $dir = 'public://print-article/';
      file_prepare_directory($dir, FILE_CREATE_DIRECTORY);
      $thumbnail = file_save_data($zip->getFromName('preview.jpg'), 'public://print-article/' . $printArticle->label() . '-preview.jpg', FILE_EXISTS_REPLACE);

      $imageStyles = ImageStyle::loadMultiple();
      /** @var \Drupal\image\Entity\ImageStyle $imageStyle */
      foreach ($imageStyles as $imageStyle) {
        $imageStyle->flush($thumbnail->getFileUri());
      }

      $printArticle->set('image', $thumbnail);
      $printArticle->save();

      $zip->close();
      unlink($zipFilename);
    }
    else {

      /** @var \Drupal\Core\Queue\QueueInterface $queue */
      $queue = $this->queueFactory->get('thunder_print_idms_fetching');
      $item = [
        'job_id' => $data['job_id'],
        'print_article_id' => $data['print_article_id'],
      ];

      $queue->createItem($item);
    }

  }

}
