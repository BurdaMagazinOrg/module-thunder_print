<?php

namespace Drupal\thunder_print;

/**
 * Class IDMSTag.
 */
class IDMSTag {

  /**
   * A tag of the xml.
   *
   * @var \SimpleXMLElement
   */
  protected $tag;

  /**
   * The current idms representation.
   *
   * @var \Drupal\thunder_print\IDMS
   */
  protected $idms;

  /**
   * IDMSTag constructor.
   *
   * @param \SimpleXMLElement $tag
   *   Xml tag.
   * @param \Drupal\thunder_print\IDMS $idms
   *   Current idms representation.
   */
  public function __construct(\SimpleXMLElement $tag, IDMS $idms) {
    $this->tag = $tag;
    $this->idms = $idms;
  }

  /**
   * Returns the self property for the tag.
   *
   * @return string
   *   Self property.
   */
  public function getSelf() {
    return (string) $this->tag['Self'];
  }

  /**
   * Performs a plain value replacement for this tag.
   *
   * @param mixed $value
   *   The plain value to replace.
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
