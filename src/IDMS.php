<?php

namespace Drupal\thunder_print;

use Drupal\thunder_print\Validator\Constraints\IdmsUniqueTags;
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
      new IdmsUniqueTags(),
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

}
