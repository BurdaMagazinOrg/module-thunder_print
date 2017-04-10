<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Tag Mapping entity.
 *
 * @ConfigEntityType(
 *   id = "thunder_print_tag_mapping",
 *   label = @Translation("Tag Mapping"),
 *   handlers = {
 *     "list_builder" = "Drupal\thunder_print\TagMappingListBuilder",
 *     "form" = {
 *       "add" = "Drupal\thunder_print\Form\TagMappingForm",
 *       "edit" = "Drupal\thunder_print\Form\TagMappingForm",
 *       "delete" = "Drupal\thunder_print\Form\TagMappingDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\thunder_print\TagMappingHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "tag_mapping",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/thunder_print/tag_mapping/{thunder_print_tag_mapping}",
 *     "add-form" = "/admin/structure/thunder_print/tag_mapping/add",
 *     "edit-form" = "/admin/structure/thunder_print/tag_mapping/{thunder_print_tag_mapping}/edit",
 *     "delete-form" = "/admin/structure/thunder_print/tag_mapping/{thunder_print_tag_mapping}/delete",
 *     "collection" = "/admin/structure/thunder_print/tag_mapping"
 *   }
 * )
 */
class TagMapping extends ConfigEntityBase implements TagMappingInterface {

  /**
   * The Tag Mapping ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Tag Mapping label.
   *
   * @var string
   */
  protected $label;

  /**
   * @var string
   */
  protected $mapping_type;

  /**
   * @var \Drupal\thunder_print\Plugin\TagMappingTypeManager $mapping_type_manager
   */
  protected $mapping_type_manager;

  /**
   * @var array
   */
  protected $mapping = [];

  /**
   * @var array
   */
  protected $options = [];

  /**
   * {@inheritdoc}
   */
  public function getMappingTypeId() {
    return $this->mapping_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapping() {
    $return = [];
    foreach ($this->mapping as $spec) {
      $return[$spec['property']] = $spec['tag'];
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getTags() {
    return array_unique(array_values($this->getMapping()));
  }

  /**
   * {@inheritdoc}
   */
  public function getTag($property) {
    $mapping = $this->getMapping();
    if (isset($mapping[$property])) {
      return $mapping[$property];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMappingType() {
    if (!empty($this->mapping_type) && $this->getMappingTypeManager()->hasDefinition($this->mapping_type)) {
      $plugin = $this->getMappingTypeManager()->createInstance($this->mapping_type,
        [
          'mapping' => $this->getMapping(),
          'options' => $this->options,
        ]
      );
      return $plugin;
    }
  }

  /**
   * Provides mapping type manager for internal usage.
   *
   * @return \Drupal\thunder_print\Plugin\TagMappingTypeManager|mixed
   */
  protected function getMappingTypeManager() {
    if (!isset($this->mapping_type_manager)) {
      $this->mapping_type_manager = \Drupal::service('plugin.manager.thunder_print_tag_mapping_type');
    }
    return $this->mapping_type_manager;
  }
}
