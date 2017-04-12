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
   */
  public function generateMachineName($input);

  /**
   * Generates a unique machine name.
   *
   * @param $input
   *   Input string to use for machine name generation.
   *
   * @return string
   *
   * @throws \Exception
   */
  public function generateUniqueMachineName($input);

  /**
   * Sets the callback to check for existing machine name.
   *
   * @param callable $callback
   *
   * @return static
   */
  public function setExistsCallback(callable $callback);

  /**
   * Remove the exists callback.
   *
   * @return static
   */
  public function unsetExistsCallback();

  /**
   * Checks if callback for exists checking is set.
   */
  public function hasExistsCallback();

}
