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

}
