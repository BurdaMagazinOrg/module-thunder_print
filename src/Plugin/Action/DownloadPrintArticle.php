<?php

namespace Drupal\thunder_print\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderManager;
use Drupal\user\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Download a print article entity.
 *
 * @Action(
 *   id = "print_article_download_action",
 *   label = @Translation("Download print article"),
 *   type = "print_article",
 *   confirm_form_route_name ="thunder_print.print_article.downloadMultipleIdms"
 * )
 */
class DownloadPrintArticle extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The tempstore object.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Idms builder manager.
   *
   * @var \Drupal\thunder_print\Plugin\IdmsBuilderManager
   */
  protected $idmsBuilderManager;

  /**
   * PrintArticleController constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\user\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\thunder_print\Plugin\IdmsBuilderManager $idmsBuilderManager
   *   IDMS Builder manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user, IdmsBuilderManager $idmsBuilderManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->tempStore = $temp_store_factory->get('thunder_print_download');
    $this->idmsBuilderManager = $idmsBuilderManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('user.private_tempstore'),
      $container->get('current_user'),
      $container->get('plugin.manager.thunder_print_idms_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $object */
    $result = $object->access('update', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface[] $entities */
    $selection = [];
    foreach ($entities as $entity) {
      $langcode = $entity->language()->getId();
      $selection[$entity->id()][$langcode] = $langcode;
    }
    $this->tempStore->set($this->currentUser->id(), $selection);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(PrintArticleInterface $entity = NULL) {
    $this->executeMultiple([$entity]);
  }

}
