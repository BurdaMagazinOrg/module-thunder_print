<?php

namespace Drupal\thunder_print;

use Drupal\Component\Utility\Html;

/**
 * Class IDMSStyle.
 */
class IDMSStyle {

  /**
   * Xml element.
   *
   * @var \SimpleXMLElement
   */
  protected $element;

  /**
   * Style name.
   *
   * @var string
   */
  protected $name;

  /**
   * Xml element.
   *
   * @var \SimpleXMLElement
   */
  protected $fullXml;

  /**
   * IDMSStyle constructor.
   *
   * @param \SimpleXMLElement $element
   *   Xml object.
   * @param \SimpleXMLElement $fullXml
   *   Complete idms as xml object.
   */
  public function __construct(\SimpleXMLElement $element, \SimpleXMLElement $fullXml) {
    $this->element = $element;
    $this->name = (string) $element;
    $this->fullXml = $fullXml;
  }

  /**
   * Get style name.
   *
   * @return string
   *   Style name.
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get display name name.
   *
   * @return string
   *   Display name.
   */
  public function getDisplayName() {
    $displayName = substr($this->name, strpos($this->name, '/') + 1);
    $displayName = urldecode($displayName);
    return $displayName;
  }

  /**
   * Get representative class for the style.
   *
   * @return string
   *   Class name.
   */
  public function getClass() {
    return Html::getClass($this->name);
  }

  /**
   * Returns the font family and style in the same format it's written to css.
   *
   * @return string
   *   Font family.
   */
  public function getFontFamily() {

    $xpath = "//RootParagraphStyleGroup//ParagraphStyle[@Self='{$this->getName()}']";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->fullXml->xpath($xpath)[0];

    return Html::getClass($this->getBaseFontFamily($this->getName()) . '-' . (string) $xmlElement['FontStyle']);
  }

  /**
   * Recursive looking for font family.
   *
   * @param string $name
   *   Name of a paragraph style.
   *
   * @return string
   *   Returns the font family.
   */
  protected function getBaseFontFamily($name) {

    $xpath = "//RootParagraphStyleGroup//ParagraphStyle[@Self='{$name}']";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->fullXml->xpath($xpath)[0];

    if (strpos((string) $xmlElement->Properties->BasedOn, '$ID/[No paragraph style]') !== FALSE) {
      return (string) $xmlElement->Properties->AppliedFont;
    }
    else {
      return $this->getBaseFontFamily((string) $xmlElement->Properties->BasedOn);
    }

  }

}
