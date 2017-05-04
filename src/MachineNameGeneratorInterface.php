<?php

namespace Drupal\thunder_print;

/**
 * Interface MachineNameGeneratorInterface.
 *
 * @package Drupal\thunder_print
 */
interface MachineNameGeneratorInterface {

  /**
   * Generate a machine name.
   *
   * @param string $input
   *   Input string to use for machine name generation.
   *
   * @return string
   *   Generated machine name.
   */
  public function generateMachineName($input);

  /**
   * Generates a unique machine name.
   *
   * @param string $input
   *   Input string to use for machine name generation.
   * @param callable $callback
   *   Callback for checking the existance of a machine name.
   *
   * @return string
   *   Generated unique machine name.
   *
   * @throws \Exception
   */
  public function generateUniqueMachineName($input, callable $callback);
}
