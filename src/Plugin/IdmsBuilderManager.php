<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Idms builder plugin manager.
 */
class IdmsBuilderManager extends DefaultPluginManager {

  /**
   * Constructs a new IdmsBuilderManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/IdmsBuilder', $namespaces, $module_handler, 'Drupal\thunder_print\Plugin\IdmsBuilderInterface', 'Drupal\thunder_print\Annotation\IdmsBuilder');

    $this->alterInfo('thunder_print_idms_builder_info');
    $this->setCacheBackend($cache_backend, 'thunder_print_idms_builder_plugins');
  }

}
