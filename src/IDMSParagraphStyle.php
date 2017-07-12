<?php

namespace Drupal\thunder_print;

use Drupal\Component\Utility\Html;

/**
 * Class IDMSStyle.
 */
class IDMSParagraphStyle extends IDMSStyle {

  /**
   * Returns the font family and style in the same format it's written to css.
   *
   * @return string
   *   Font family.
   */
  public function getFontFamilyAndStyle() {

    $xpath = "//RootParagraphStyleGroup//ParagraphStyle[@Self='{$this->getName()}']";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->fullXml->xpath($xpath)[0];

    return Html::getClass($this->getBaseFontFamily($this->getName()) . '-style-' . (string) $xmlElement['FontStyle']);
  }

  /**
   * Returns the font family and style in the same format it's written to css.
   *
   * @return string
   *   Font family.
   */
  public function getFontFamily() {

    return Html::getClass($this->getBaseFontFamily($this->getName()));
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
