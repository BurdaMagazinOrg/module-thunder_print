<?php

namespace Drupal\thunder_print;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Language\LanguageManagerInterface;

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
   * Langauge manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Callback to check for existing machine name.
   *
   * @var callable
   */
  protected $existsCallback;

  /**
   * Constructor.
   */
  public function __construct(TransliterationInterface $transliteration, LanguageManagerInterface $languageManager) {
    $this->transliteration = $transliteration;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function generateMachineName($input) {
    $language = $this->languageManager->getCurrentLanguage();
    $machine_name = $this->transliteration->transliterate($input, $language->getId(), '_');
    $machine_name = Unicode::strtolower($machine_name);
    // Only allow a-z, 0-9 or '_'.
    $machine_name = preg_replace('/[^a-z0-9_]+/', '_', $machine_name);
    // Replace multiple occurencies of '_' with a single '_'.
    $machine_name = preg_replace('/_+/', '_', $machine_name);
    return $machine_name;
  }

  /**
   * {@inheritdoc}
   */
  public function generateUniqueMachineName($input, callable $callback) {
    $output = $this->generateMachineName($input);
    $appendcount = 0;

    do {
      $unique = ($appendcount) ? $output . '_' . $appendcount : $output;
      $return = call_user_func($callback, $unique);
      $appendcount++;
    } while (!empty($return));

    return $unique;
  }
}
