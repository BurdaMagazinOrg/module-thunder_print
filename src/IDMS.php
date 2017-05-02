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
   * @return array
   *   Array of tags.
   */
  public function getTags() {

    $tags = [];
    foreach ($this->xml->XMLTag as $tag) {
      $tags[] = (string) $tag['Self'];
    }

    return $tags;
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

  public function getContent($tagName) {

    $xpath = "(//Story//XMLElement[@MarkupTag='$tagName'])[last()]/Content";
    $elements = $this->xml->xpath($xpath)[0]->asXML();

  #  var_dump(trim($elements));

    return $elements;
  }

}
