<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\thunder_print\Annotation\IdmsBuilder;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\IDMS;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Idms builder plugins.
 */
abstract class IdmsBuilderBase extends PluginBase implements IdmsBuilderInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MediaImage constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Use snippet template from bundle and replaces the placeholder with content.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   The print article.
   *
   * @return \Drupal\thunder_print\IDMS
   *   New IDMS with replaced content.
   */
  protected function replaceSnippetPlaceholders(PrintArticleInterface $printArticle) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $bundle */
    $bundle = $printArticle->type->entity;

    $idms = new IDMS($bundle->getIdms());

    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $tagMapping */
    foreach ($bundle->getTags() as $tagMapping) {

      /** @var \Drupal\Core\Field\FieldItemList $field */
      $field = $printArticle->{$tagMapping->id()};

      if ($fieldItem = $field->first()) {
        $mappingType = $tagMapping->getMappingType();

        $idms = $this->replace($idms, $fieldItem, $mappingType);
      }
    }
    return $idms;
  }

  protected function replace(IDMS $idms, $fieldItem, TagMappingTypeInterface $mappingType) {
    return $mappingType->replacePlaceholder($idms, $fieldItem->getValue());
  }
}
