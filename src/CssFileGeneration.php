<?php

namespace Drupal\thunder_print;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class CssFileGeneration.
 *
 * @package Drupal\thunder_print
 */
class CssFileGeneration {

  protected $entityTypeManager;

  /**
   * CssFileGeneration constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Writes a css file with current fonts.
   *
   * @throws \Exception
   */
  public function generateCssFile() {

    $fonts = $this->entityTypeManager->getStorage('thunder_print_font')
      ->loadMultiple();

    $css = '';

    foreach ($fonts as $font) {

      $path = file_create_url($font->get('file')->entity->uri->value);
      $fontName = Html::getClass($font->get('font')->value . '-' . $font->get('font_style')->value);
      $css .= "@font-face { font-family: $fontName; src: url($path); }\n";
    }

    $destination = 'public://thunder-print-css';

    if (!file_prepare_directory($destination, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      throw new \Exception("Unable to create directory $destination.");
    }
    file_put_contents($destination . '/fonts.css', $css);
  }

}
