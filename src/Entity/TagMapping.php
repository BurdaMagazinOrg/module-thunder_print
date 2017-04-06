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

}
