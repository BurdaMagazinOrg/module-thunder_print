<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Idms builder plugins.
 */
interface IdmsBuilderInterface extends PluginInspectionInterface {

  /**
   * Returns a unified response.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   A generic stream response.
   */
  public function getResponse();

}
