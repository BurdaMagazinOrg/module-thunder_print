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
   * IDMSStyle constructor.
   *
   * @param \SimpleXMLElement $element
   *   Xml object.
   */
  public function __construct(\SimpleXMLElement $element) {
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
   * Get representative class for the style.
   *
   * @return string
   *   Class name.
   */
  public function getClass() {
    return Html::getClass($this->name);
  }

}
