<?php

namespace Drupal\thunder_print\Plugin\IdmsBuilder;

use Drupal\thunder_print\Plugin\IdmsBuilderBase;
use Drupal\thunder_print\Plugin\TagMappingType\MediaEntity;

/**
 * Provides Tag Mapping for media entity reference.
 *
 * @IdmsBuilder(
 *   id = "remote",
 *   label = @Translation("Remote builder"),
 * )
 */
class RemoteBuilder extends IdmsBuilderBase {

  /**
   * {@inheritdoc}
   */
  protected function getContent() {

    $replacedIdms = $this->replaceSnippetPlaceholders();
    $files = $this->getAdditionalFiles();

    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle */
    $printArticle = $this->getPrintArticle();

    $zip = new \ZipArchive();
    $zipFilename = tempnam("tmp", "zip");

    if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
      return FALSE;
    }
    else {
      foreach ($files as $filename => $file) {
        $zip->addFromString($filename, file_get_contents($file));
      }
      $zip->addFromString($printArticle->label() . '.idms', $replacedIdms->getXml()->asXml());
      $zip->close();

    }

    $content = file_get_contents($zipFilename);
    unlink($zipFilename);
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  protected function getFilename() {
    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle */
    $printArticle = $this->getPrintArticle();
    return $printArticle->label() . '.zip';
  }

  /**
   * Discovers additional files that are part of the idms.
   *
   * @return \Drupal\file\FileInterface[]
   *   Array of file items.
   */
  protected function getAdditionalFiles() {

    $files = [];

    $printArticle = $this->getPrintArticle();

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $bundle */
    $bundle = $this->entityTypeManager->getStorage($printArticle->getEntityType()
      ->getBundleEntityType())
      ->load($printArticle->bundle());

    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $tagMapping */
    foreach ($bundle->getTags() as $tagMapping) {

      /** @var \Drupal\Core\Field\FieldItemList $field */
      $field = $printArticle->{$tagMapping->id()};

      if (($fieldItem = $field->first()) && $tagMapping->getMappingType() instanceof MediaEntity) {

        /** @var \Drupal\file\FileInterface $file */
        $file = $tagMapping->getMappingType()->getFile($fieldItem->getValue()['target_id']);

        $filename = pathinfo($file->getFileUri())['basename'];
        $files[$filename] = $file->getFileUri();
      }
    }

    return $files;
  }

}
