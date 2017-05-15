<?php

namespace Drupal\thunder_print\Plugin\IdmsBuilder;

use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Plugin\AdditionalFilesInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderBase;

/**
 * Provides Tag Mapping for media entity reference.
 *
 * @IdmsBuilder(
 *   id = "zip_archived",
 *   label = @Translation("Remote builder"),
 *   buildMode = "multifile"
 * )
 */
class ZipArchivedBuilder extends IdmsBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function getContent(PrintArticleInterface $printArticle) {

    $replacedIdms = $this->replaceSnippetPlaceholders($printArticle);
    $files = $this->getAdditionalFiles($printArticle);

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
  public function getFilename(PrintArticleInterface $printArticle) {
    return $printArticle->label() . '.zip';
  }

  /**
   * Discovers additional files that are part of the idms.
   *
   * @return \Drupal\file\FileInterface[]
   *   Array of file items.
   */
  protected function getAdditionalFiles(PrintArticleInterface $printArticle) {

    $files = [];

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $bundle */
    $bundle = $this->entityTypeManager->getStorage($printArticle->getEntityType()
      ->getBundleEntityType())
      ->load($printArticle->bundle());

    $idms = new IDMS($bundle->getIdms());

    /** @var \Drupal\thunder_print\Entity\TagMappingInterface $tagMapping */
    foreach ($bundle->getTags() as $tagMapping) {

      /** @var \Drupal\Core\Field\FieldItemList $field */
      $field = $printArticle->{$tagMapping->id()};

      $mappingType = $tagMapping->getMappingType();
      if (($fieldItem = $field->first()) && $mappingType instanceof AdditionalFilesInterface) {

        /** @var \Drupal\file\FileInterface $file */
        foreach ($mappingType->getAdditionalFiles($idms, $fieldItem->getValue()) as $file) {
          $filename = pathinfo($file->getFileUri())['basename'];
          $files[$filename] = $file->getFileUri();
        }
      }
    }

    return $files;
  }

}
