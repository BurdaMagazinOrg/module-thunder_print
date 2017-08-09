<?php

namespace Drupal\thunder_print;

use Drupal\Component\Utility\Html;

/**
 * Class IDMSStyle.
 */
abstract class IDMSStyle {

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
  abstract public function getFontFamily();

}
