<?php

namespace Drupal\thunder_print;

class IDMSTag {

  /**
   * @var \SimpleXMLElement
   */
  protected $tag;

  /**
   * @var \Drupal\thunder_print\IDMS
   */
  protected $idms;

  public function __construct(\SimpleXMLElement $tag, IDMS $idms) {
    $this->tag = $tag;
    $this->idms = $idms;
  }

  /**
   * @return string
   */
  public function getSelf() {
    return (string) $this->tag['Self'];
  }

  /**
   * Performs a plain value replacement for this tag.
   *
   * @param mixed $value
   */
  public function replacePlain($value) {
    $xpath = "//Story//XMLElement[@MarkupTag='{{$this->tag}}']//Content";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->idms->getXml()->xpath($xpath);
    if ($xmlElement) {
      $xmlElement[0][0] = trim(strip_tags($value));
    }
  }

}
