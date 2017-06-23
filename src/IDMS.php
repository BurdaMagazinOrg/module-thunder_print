<?php

namespace Drupal\thunder_print;

use Drupal\thunder_print\Validator\Constraints\IdmsNoChangeTracking;
use Symfony\Component\Validator\Validation;

/**
 * Works on an idms file.
 */
class IDMS {

  /**
   * XML object.
   *
   * @var \SimpleXMLElement
   */
  protected $xml;

  /**
   * IDMS constructor.
   *
   * @param string $xml
   *   A string containing the xml of an idms file.
   */
  public function __construct($xml) {

    $this->xml = new \SimpleXMLElement($xml);

    // IDMS is using many different namespaces, so we have to register them.
    $this->xml->registerXPathNamespace('x', 'adobe:ns:meta/');
    $this->xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    $this->xml->registerXPathNamespace('xmp', 'http://ns.adobe.com/xap/1.0/');
    $this->xml->registerXPathNamespace('xmpGImg', 'http://ns.adobe.com/xap/1.0/g/img/');

  }

  /**
   * Get all the tags that are contained in the idms file.
   *
   * @return \Drupal\thunder_print\IDMSTag[]
   *   List of tag objects.
   */
  public function getTags() {

    $tags = [];
    foreach ($this->xml->XMLTag as $tag) {
      $tags[(string) $tag['Self']] = new IDMSTag($tag, $this);
    }

    return $tags;
  }

  /**
   * Retrieve all names of tags contained in the idms.
   *
   * @return array
   *   List of tag names.
   */
  public function getTagNames() {
    $tags = $this->getTags();
    $names = [];
    foreach ($tags as $tag) {
      $names[] = $tag->getSelf();
    }
    return $names;
  }

  /**
   * Validates idms file.
   */
  public function validate() {
    $validator = Validation::createValidator();

    return $validator->validate($this, [
      new IdmsNoChangeTracking(),
    ]);
  }

  /**
   * The idms xml.
   *
   * @return \SimpleXMLElement
   *   The xml object.
   */
  public function getXml() {
    return $this->xml;
  }

  /**
   * Extracts extension and image data from idms file.
   *
   * @return array
   *   Data and extension form thumbnail.
   */
  public function extractThumbnail() {

    $xpath_format = "//x:xmpmeta/rdf:RDF/rdf:Description/xmp:Thumbnails/rdf:Alt/rdf:li/xmpGImg:format";
    $xpath_image = "//x:xmpmeta/rdf:RDF/rdf:Description/xmp:Thumbnails/rdf:Alt/rdf:li/xmpGImg:image";

    $extension = (string) $this->xml->xpath($xpath_format)[0];

    $data = base64_decode((string) $this->xml->xpath($xpath_image)[0]);

    return [
      $data,
      $extension,
    ];
  }

  /**
   * Get styles by xpath.
   *
   * @param string $xpath
   *   Xpath to styles.
   *
   * @return array
   *   Array of styles.
   */
  protected function getStyles($xpath) {
    $xmlElements = $this->xml->xpath($xpath);

    $styles = [];
    foreach ($xmlElements as $element) {
      $styles[] = (string) $element;
    }

    return array_unique($styles);
  }

}
