<?php

namespace Drupal\thunder_print;
use Drupal\Core\Transliteration\PhpTransliteration;

/**
 * Class MachineNameGenerator.
 *
 * @package Drupal\thunder_print
 */
class MachineNameGenerator implements MachineNameGeneratorInterface {

  /**
   * Drupal\Core\Transliteration\PhpTransliteration definition.
   *
   * @var \Drupal\Core\Transliteration\PhpTransliteration
   */
  protected $transliteration;
  /**
   * Constructor.
   */
  public function __construct(PhpTransliteration $transliteration) {
    $this->transliteration = $transliteration;
  }

}
