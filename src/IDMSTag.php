<?php

namespace Drupal\thunder_print;

use Drupal\thunder_print\IDMSStyle;

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
    $xpath = "//Story//XMLElement[@MarkupTag='{$this->tag}']//Content";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->idms->getXml()->xpath($xpath);
    if ($xmlElement) {
      $xmlElement[0][0] = trim(strip_tags($value));
    }
  }

  /**
   * Get all paragraphs styles for this tag.
   *
   * @return \Drupal\thunder_print\IDMSStyle[]
   *   Array of paragraphs styles.
   */

  public function getParagraphStyles() {
    $xpath = "//XMLElement[@MarkupTag='{$this->getSelf()}']/ParagraphStyleRange/@AppliedParagraphStyle";
    $xmlElements = $this->idms->getXml()->xpath($xpath);

    $styles = [];
    foreach ($xmlElements as $element) {
      $style = new IDMSStyle($element);
      $styles[$style->getName()] = $style;
    }

    return $styles;
  }

  /**
   * Get all character styles for a tag.
   *
   * @param string $tagName
   *   IDMS tag name.
   *
   * @return \Drupal\thunder_print\IDMSStyle[]
   *   Array of paragraphs styles.
   */
  public function getCharacterStyles() {
    $xpath = "//XMLElement[@MarkupTag='{$this->getSelf()}']//CharacterStyleRange/@AppliedCharacterStyle";
    $xmlElements = $this->idms->getXml()->xpath($xpath);

    $styles = [];
    foreach ($xmlElements as $element) {
      $style = new IDMSStyle($element);
      if (strpos($style->getName(), '[No character style]') === FALSE) {
        $styles[$style->getName()] = $style;
      }
    }

    return $styles;
  }

}
