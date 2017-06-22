<?php

namespace Drupal\thunder_print;

class IDMSStyle {

  /**
   * @var \SimpleXMLElement
   */
  protected $element;

  /**
   * @var string
   */
  protected $name;

  /**
   * IDMSStyle constructor.
   *
   * @param \SimpleXMLElement $element
   */
  public function __construct(\SimpleXMLElement $element) {
    $this->element = $element;
    $this->name = (string) $element;
  }

  /**
   * Get style name.
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get representative class for the style.
   *
   * @return string
   */
  public function getClass() {
    return \Drupal\Component\Utility\Html::getClass($this->name);
  }

}
