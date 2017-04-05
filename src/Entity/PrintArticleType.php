<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Print article type entity.
 *
 * @ConfigEntityType(
 *   id = "print_article_type",
 *   label = @Translation("Snippet Template"),
 *   handlers = {
 *     "list_builder" = "Drupal\thunder_print\PrintArticleTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\thunder_print\Form\PrintArticleTypeAddForm",
 *       "edit" = "Drupal\thunder_print\Form\PrintArticleTypeForm",
 *       "delete" = "Drupal\thunder_print\Form\PrintArticleTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\thunder_print\PrintArticleTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "print_article_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "print_article",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "grid",
 *     "idms",
 *     "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/print_article_type/{print_article_type}",
 *     "add-form" = "/admin/structure/print_article_type/add",
 *     "edit-form" = "/admin/structure/print_article_type/{print_article_type}/edit",
 *     "delete-form" = "/admin/structure/print_article_type/{print_article_type}/delete",
 *     "collection" = "/admin/structure/print_article_type"
 *   }
 * )
 */
class PrintArticleType extends ConfigEntityBundleBase implements PrintArticleTypeInterface {

  /**
   * Machine name of the print article type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the print article type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this print article type.
   *
   * @var string
   */
  protected $description;

  /**
   * Grid size to render an idms.
   *
   * @var int
   */
  protected $grid;

  /**
   * The complete idms xml.
   *
   * @var string
   */
  protected $idms;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getGrid() {
    return $this->grid;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdms() {
    return $this->idms;
  }

}
