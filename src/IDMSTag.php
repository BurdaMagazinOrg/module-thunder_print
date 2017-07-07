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
    $xpath = "//Story//XMLElement[@MarkupTag='{$this->getSelf()}']//Content";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->idms->getXml()->xpath($xpath);
    if ($xmlElement) {
      $xmlElement[0][0] = trim(strip_tags($value));
    }
  }

  /**
   * Performs a plain value replacement for this tag.
   *
   * @param mixed $value
   *   The plain value to replace.
   */
  public function replaceComplex($value) {

    $value = "<foo>$value</foo>";

    $value = str_replace('&nbsp;', ' ', $value);

    foreach ($this->getCharacterStyles() as $characterStyle) {
      $value = str_replace($characterStyle->getClass(), $characterStyle->getName(), $value);
    }
    foreach ($this->getParagraphStyles() as $paragraphsStyle) {
      $value = str_replace($paragraphsStyle->getClass(), $paragraphsStyle->getName(), $value);
    }

    $xpath = "//Story//XMLElement[@MarkupTag='{$this->getSelf()}']";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->idms->getXml()->xpath($xpath);

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($value, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOEMPTYTAG);

    $elements = $dom->getElementsByTagName('p');

    /** @var \DOMElement $element */
    foreach ($elements as $element) {
      $replacements = [];
      foreach ($element->childNodes as $childNode) {

        if ($childNode instanceof \DOMText) {
          $new = $dom->createElement("span", preg_replace('/&(?![[:alnum:]]+;)/', '&amp;', $childNode->nodeValue));
          $new->setAttribute('class', 'CharacterStyle/$ID/[No character style]');
          $replacements[] = ['new' => $new, 'old' => $childNode];
        }
      }
      foreach ($replacements as $replacement) {
        $element->replaceChild($replacement['new'], $replacement['old']);
      }
    }

    $value = $dom->saveXML($dom);

    $value = preg_replace('/<span class="(.+?)">(.+?)<\/span>/ims', "<CharacterStyleRange AppliedCharacterStyle=\"$1\"><Content>$2</Content></CharacterStyleRange>", $value);
    $value = preg_replace('/<p class="(.+?)">(.+?)<\/p>/ims', "<ParagraphStyleRange AppliedParagraphStyle=\"$1\">$2<Br/></ParagraphStyleRange>", $value);
    $value = preg_replace("/<p>(.*?)<\/p>/ims", "", $value);

    $doc = simplexml_load_string($value);

    $xmlElement[0][0] = "";

    $toDom = dom_import_simplexml($xmlElement[0][0]);
    foreach ($doc->children() as $child) {
      $fromDom = dom_import_simplexml($child);
      $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, TRUE));
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
      $style = new IDMSStyle($element, $this->idms->getXml());
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
      $style = new IDMSStyle($element, $this->idms->getXml());
      if (strpos($style->getName(), '[No character style]') === FALSE) {
        $styles[$style->getName()] = $style;
      }
    }

    return $styles;
  }

}
