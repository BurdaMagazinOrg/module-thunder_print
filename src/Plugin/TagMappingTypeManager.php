<?php

namespace Drupal\thunder_print\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Tag mapping type plugin manager.
 */
class TagMappingTypeManager extends DefaultPluginManager {

  /**
   * Constructor for TagMappingTypeManager objects.
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
    parent::__construct('Plugin/TagMappingType', $namespaces, $module_handler, 'Drupal\thunder_print\Plugin\TagMappingTypeInterface', 'Drupal\thunder_print\Annotation\TagMappingType');

    $this->alterInfo('thunder_print_thunder_print_tag_mapping_type_info');
    $this->setCacheBackend($cache_backend, 'thunder_print_thunder_print_tag_mapping_type_plugins');
  }

  /**
   * Provides an options list to be used in a select element.
   *
   * @return String[String]
   *   Key/value list of tag mappings types.
   *   - Key: ID/machine name of the plugin
   *   - Value: Human readable label of the plugin
   */
  public function getOptions() {
    $options = [];
    $definitions = $this->getDefinitions();
    foreach ($definitions as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }
    return $options;
  }

}
