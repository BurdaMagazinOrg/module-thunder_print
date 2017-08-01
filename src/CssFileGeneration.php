<?php

namespace Drupal\thunder_print;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\thunder_print\Entity\PrintArticleTypeInterface;

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
  public function generateFontCssFile() {

    $fonts = $this->entityTypeManager->getStorage('thunder_print_font')
      ->loadMultiple();

    $css = '';

    foreach ($fonts as $font) {

      $path = file_create_url($font->get('file')->entity->uri->value);
      $fontName = Html::getClass($font->get('font')->value . '-style-' . $font->get('font_style')->value);
      $css .= "@font-face { font-family: $fontName; src: url($path); }\n";
    }

    $destination = 'public://thunder-print-css';

    if (!file_prepare_directory($destination, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      throw new \Exception("Unable to create directory $destination.");
    }
    file_put_contents($destination . '/fonts.css', $css);
  }

  /**
   * Writes a css file for a specific print article type.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleTypeInterface $printArticleType
   *   The print article type object.
   *
   * @throws \Exception
   */
  public function generateCssFile(PrintArticleTypeInterface $printArticleType) {

    $filename = Html::getClass($printArticleType->label());
    $css = '';
    foreach ($printArticleType->getTags() as $tag) {

      $mappedTag = $tag->getMappingType()
        ->getMappedTag($printArticleType->getNewIdms(), 'value');

      if (!empty($mappedTag)) {

        foreach ($mappedTag->getParagraphStyles() as $style) {
          $css .= ".{$style->getClass()} { font-family: {$style->getFontFamilyAndStyle()} }\n";
          foreach ($mappedTag->getCharacterStyles() as $characterStyle) {
            $css .= ".{$style->getFontFamily()} .{$characterStyle->getFontFamily()} { font-family: {$style->getFontFamily()}-{$characterStyle->getFontFamily()} }\n";
          }
        }
      }
    }

    $destination = 'public://thunder-print-css';

    if (!file_prepare_directory($destination, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      throw new \Exception("Unable to create directory $destination.");
    }
    file_put_contents($destination . DIRECTORY_SEPARATOR . $filename . '.css', $css, FILE_APPEND);
  }

}
