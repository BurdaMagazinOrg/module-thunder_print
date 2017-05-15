<?php

namespace Drupal\thunder_print\Plugin\IdmsBuilder;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Plugin\AdditionalFilesInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderBase;

/**
 * Provides zip archive builder.
 *
 * @IdmsBuilder(
 *   id = "zip_archived",
 *   label = @Translation("Remote builder"),
 *   buildMode = "multifile"
 * )
 */
class ZipArchivedBuilder extends IdmsBuilderBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getContent(PrintArticleInterface $printArticle) {

    $zip = new \ZipArchive();
    $zipFilename = tempnam(file_directory_temp(), "zip");

    if ($zip->open($zipFilename, \ZipArchive::CREATE) !== TRUE) {
      throw new \Exception($this->t('Not possible to create zip archive'));
    }
    else {
      foreach ($this->getAdditionalFiles($printArticle) as $filename => $file) {
        $zip->addFromString($filename, file_get_contents($file));
      }
      $replacedIdms = $this->replaceSnippetPlaceholders($printArticle);
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
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   The print article.
   *
   * @return \Drupal\file\FileInterface[]
   *   Array of file items.
   */
  protected function getAdditionalFiles(PrintArticleInterface $printArticle) {

    $files = [];

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $bundle */
    $bundle = $printArticle->type->entity;

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
