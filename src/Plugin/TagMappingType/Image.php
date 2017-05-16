<?php

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\file\FileInterface;
use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Plugin\AdditionalFilesInterface;
use Drupal\thunder_print\Plugin\TagMappingTypeBase;

/**
 * Provides tag mapping for an image.
 *
 * @package Drupal\thunder_print\Plugin\TagMappingType
 *
 * @TagMappingType(
 *   id = "image",
 *   label = @Translation("Image"),
 * )
 */
class Image extends TagMappingTypeBase implements AdditionalFilesInterface {

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    return [
      'target_id' => [
        'required' => TRUE,
        'name' => $this->t('Value'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMainProperty() {
    return 'target_id';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldStorageDefinition() {
    return [
      'type' => 'image',
      'settings' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfigDefinition() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function replacePlaceholder(IDMS $idms, $fieldItem) {

    return $this->iterateMapping(function (\SimpleXMLElement $xmlImage, FileInterface $file) {
      $xmlImage->Link['StoredState'] = 'Embedded';
      $xmlImage->Properties->Contents = base64_encode(file_get_contents($file->getFileUri()));
    }, $idms, $fieldItem);
  }

  /**
   * {@inheritdoc}
   */
  protected function iterateMapping(callable $callback, IDMS $idms, $fieldItem) {

    foreach ($this->configuration['mapping'] as $field => $tag) {

      $xmlImage = $this->getXmlImageObject($tag, $idms);

      $fileId = $fieldItem[$field];
      /** @var \Drupal\file\Entity\File $file */
      $file = $this->entityTypeManager
        ->getStorage('file')
        ->load($fileId);

      $this->setFileToXmlObject($xmlImage, $file, $callback);
    }

    return $idms;
  }

  /**
   * {@inheritdoc}
   */
  public function replacePlaceholderUseRelativeLinks(IDMS $idms, $fieldItem) {

    return $this->iterateMapping(function () {}, $idms, $fieldItem);
  }

  /**
   * {@inheritdoc}
   */
  public function getAdditionalFiles(IDMS $idms, $fieldItem) {

    $files = [];

    $this->iterateMapping(function (\SimpleXMLElement $xmlImage, FileInterface $file) use (&$files) {
      $files[] = $file;
    }, $idms, $fieldItem);

    return $files;
  }

  /**
   * Discover a SimpleXml object from an idms xmlTag.
   *
   * @param string $tag
   *   Tagname.
   * @param \Drupal\thunder_print\IDMS $idms
   *   The IDMS with placeholders.
   *
   * @return \SimpleXMLElement|null
   *   An xml object.
   */
  protected function getXmlImageObject($tag, IDMS $idms) {

    $xpath = "(//XmlStory//XMLElement[@MarkupTag='$tag'])[last()]";
    $xmlElement = $idms->getXml()->xpath($xpath)[0];

    if ($xmlElement) {
      $xmlContentId = (string) $xmlElement['XMLContent'];

      $xpath = "//Image[@Self='$xmlContentId']";

      if ($xmlElement = $idms->getXml()->xpath($xpath)) {
        return $xmlElement[0];
      }
    }

    return NULL;
  }

  /**
   * Sets file information to the xml object.
   *
   * @param \SimpleXMLElement $xmlElement
   *   The xml object.
   * @param \Drupal\file\FileInterface $file
   *   The file that should be placed in the xml.
   * @param callable $callback
   *   Function to alter xml object.
   *
   * @return \SimpleXMLElement
   *   The modified xml object.
   */
  protected function setFileToXmlObject(\SimpleXMLElement $xmlElement, FileInterface $file, callable $callback) {

    $filename = pathinfo($file->getFileUri())['basename'];

    $xmlElement->Link['LinkResourceURI'] = 'file:/' . $filename;
    $xmlElement->Link['StoredState'] = 'Normal';

    if (is_callable($callback)) {
      $callback($xmlElement, $file);
    }

    return $xmlElement;
  }

}
