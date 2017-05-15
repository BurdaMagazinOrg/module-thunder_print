<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ProxyClass\File\MimeType\MimeTypeGuesser;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\thunder_print\Annotation\IdmsBuilder;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\IDMS;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\FileBinaryMimeTypeGuesser;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
   * MimeTypeGuesser service.
   *
   * @var \Drupal\Core\ProxyClass\File\MimeType\MimeTypeGuesser
   */
  protected $mimeTypeGuesser;

  /**
   * Transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

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
   * @param \Drupal\Core\ProxyClass\File\MimeType\MimeTypeGuesser $mimeTypeGuesser
   *   The mime type guesser service.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, MimeTypeGuesser $mimeTypeGuesser, TransliterationInterface $transliteration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->mimeTypeGuesser = $mimeTypeGuesser;
    $this->transliteration = $transliteration;

    $this->mimeTypeGuesser->addGuesser(new FileBinaryMimeTypeGuesser(), 10);
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
      $container->get('file.mime_type.guesser'),
      $container->get('transliteration')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function replaceSnippetPlaceholders(PrintArticleInterface $printArticle) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $bundle */
    $bundle = $this->entityTypeManager->getStorage($printArticle->getEntityType()
      ->getBundleEntityType())
      ->load($printArticle->bundle());

    $idms = new IDMS($bundle->getIdms());

    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $tagMapping */
    foreach ($bundle->getTags() as $tagMapping) {

      /** @var \Drupal\Core\Field\FieldItemList $field */
      $field = $printArticle->{$tagMapping->id()};

      if ($fieldItem = $field->first()) {
        $mappingType = $tagMapping->getMappingType();
        $idms = $mappingType->replacePlaceholder($idms, $fieldItem->getValue());

        if ($this->pluginDefinition['buildMode'] === IdmsBuilder::BUILDMODE_MULTIFILE && $mappingType instanceof AdditionalFilesInterface) {
          $idms = $mappingType->replacePlaceholderWithAdditionalFiles($idms, $fieldItem->getValue());
        }
        else {
          $idms = $mappingType->replacePlaceholder($idms, $fieldItem->getValue());
        }
      }
    }
    return $idms;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse(PrintArticleInterface $printArticle) {

    $content = $this->getContent($printArticle);
    $filename = $this->getFilename($printArticle);

    $response = new StreamedResponse(
      function () use ($content) {
        echo $content;
      });

    $tempfile = tempnam("tmp", "");
    file_put_contents($tempfile, $content);
    $mimeType = $this->mimeTypeGuesser->guess($tempfile);
    unlink($tempfile);

    $response->headers->set('Content-Type', $mimeType);
    $response->headers->set('Cache-Control', '');
    $response->headers->set('Content-Length', strlen($content));
    $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
    $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $this->transliteration->transliterate($filename));
    $response->headers->set('Content-Disposition', $contentDisposition);

    return $response;
  }

}
