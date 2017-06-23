<?php

namespace Drupal\thunder_print\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\RequeueException;
use Drupal\image\Entity\ImageStyle;
use Drupal\thunder_print\IndesignServer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class IdmsGeneration.
 *
 * @QueueWorker(
 *   id = "thunder_print_idms_thumbnail_collector",
 *   title = @Translation("Thunder print idms fetching"),
 *   cron = {"time" = 2}
 * )
 */
class IDMSThumbnailCollector extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\thunder_print\IndesignServer $indesignServer
   *   Indesing server object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, IndesignServer $indesignServer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
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
      $container->get('thunder_print.indesign_server')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {

    // Check if the preview
    if ($body = $this->indesignServer->getPreviewById($data['job_id'])) {

      $printArticle = $this->entityTypeManager
        ->getStorage('print_article')
        ->load($data['print_article_id']);

      $zip = new \ZipArchive();
      $zipFilename = tempnam("tmp", "zip");

      file_put_contents($zipFilename, $body);

      $zip->open($zipFilename);

      $dir = 'public://print-article/';
      file_prepare_directory($dir, FILE_CREATE_DIRECTORY);
      $thumbnail = file_save_data($zip->getFromName('preview.jpg'), 'public://print-article/' . $printArticle->id() . '-preview.jpg', FILE_EXISTS_REPLACE);

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
    // Otherwise we requeue the element.
    else {
      throw new RequeueException();
    }

  }

}
