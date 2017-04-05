<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Print article type entities.
 */
interface PrintArticleTypeInterface extends ConfigEntityInterface {

  /**
   * Brief description of this print article type.
   *
   * @return string
   *   The description.
   */
  public function getDescription();

  /**
   * Grid size to render an idms.
   *
   * @return int
   *   The grid size.
   */
  public function getGrid();

  /**
   * The complete idms xml.
   *
   * @return string
   *   Xml of the idms.
   */
  public function getIdms();

}
