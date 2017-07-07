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
   */
  public function __construct(\SimpleXMLElement $element, \SimpleXMLElement $fullXml) {
    $this->element = $element;
    $this->name = (string) $element;
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

  #public

}
