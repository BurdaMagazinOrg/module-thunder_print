<?php

namespace Drupal\thunder_print;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Component\Utility\Unicode;

/**
 * Provides machine name generator.
 *
 * @package Drupal\thunder_print
 */
class MachineNameGenerator implements MachineNameGeneratorInterface {

  /**
   * Transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * Callback to check for existing machine name.
   *
   * @var callable
   */
  protected $existsCallback;

  /**
   * Constructor.
   */
  public function __construct(TransliterationInterface $transliteration) {
    $this->transliteration = $transliteration;
  }

  /**
   * {@inheritdoc}
   */
  public function generateMachineName($input) {
    $language = \Drupal::languageManager()->getCurrentLanguage();

    $transliterated = $this->transliteration->transliterate($input, $language->getId(), '_');
    $transliterated = Unicode::strtolower($transliterated);
    return $transliterated;
  }

  /**
   * {@inheritdoc}
   */
  public function generateUniqueMachineName($input) {
    if (!$this->hasExistsCallback()) {
      throw new \Exception('No existsCallback set for generating a unique machine name.');
    }

    $output = $this->generateMachineName($input);
    $appendcount = 0;

    do {

      $return = call_user_func($this->existsCallback, ($appendcount) ? $output . '_'  . $appendcount: $output);
      $appendcount++;

    } while (!empty($return));

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function setExistsCallback(callable $callback) {
    $this->existsCallback = $callback;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function unsetExistsCallback() {
    unset($this->existsCallback);
  }

  /**
   * {@inheritdoc}
   */
  public function hasExistsCallback() {
    return isset($this->existsCallback);
  }
}
