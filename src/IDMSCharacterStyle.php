<?php

namespace Drupal\thunder_print;

use Drupal\Component\Utility\Html;

/**
 * Class IDMSStyle.
 */
class IDMSCharacterStyle extends IDMSStyle {

  /**
   * Returns the font family and style in the same format it's written to css.
   *
   * @return string
   *   Font family.
   */
  public function getFontFamily() {

    $xpath = "//RootCharacterStyleGroup//CharacterStyle[@Self='{$this->getName()}']";
    /** @var \SimpleXMLElement $xmlElement */
    $xmlElement = $this->fullXml->xpath($xpath)[0];

    return Html::getClass('style-' . (string) $xmlElement['FontStyle']);
  }

}
