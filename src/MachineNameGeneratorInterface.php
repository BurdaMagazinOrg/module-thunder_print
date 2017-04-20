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
   *
   * @return string
   *   Generated unique machine name.
   *
   * @throws \Exception
   */
  public function generateUniqueMachineName($input);

  /**
   * Sets the callback to check for existing machine name.
   *
   * @param callable $callback
   *   Callback for checking the existance of a machine name.
   *
   * @return static
   *   The generator itself.
   */
  public function setExistsCallback(callable $callback);

  /**
   * Remove the exists callback.
   *
   * @return static
   *   The generator itself.
   */
  public function unsetExistsCallback();

  /**
   * Checks if callback for exists checking is set.
   *
   * @return bool
   *   Returns TRUE if callback exists, FALSE otherwise.
   */
  public function hasExistsCallback();

}
