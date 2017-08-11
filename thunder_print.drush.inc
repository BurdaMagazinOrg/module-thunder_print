<?php

/**
 * @file
 * Drush commands fpr TfP.
 */

/**
 * Implements hook_drush_command().
 */
function thunder_print_drush_command() {
  $items = [];
  $items['thunder-print-regenerate-css-files'] = [
    'description' => 'Regenerate all needed css files.',
    'aliases' => ['tp-rcf'],
  ];
  return $items;
}

/**
 * Implements drush_{module_name}_{item_id_for_command}().
 */
function drush_thunder_print_regenerate_css_files() {

  /** @var \Drupal\thunder_print\CssFileGeneration $cssFileGeneration */
  $cssFileGeneration = \Drupal::service('thunder_print.css_generation');

  $printArticleTypes = \Drupal::entityTypeManager()->getStorage('print_article_type')->loadMultiple();
  /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $printArticleType */
  foreach ($printArticleTypes as $printArticleType) {
    $cssFileGeneration->generateCssFile($printArticleType);
  }
  $cssFileGeneration->generateFontCssFile();

}
