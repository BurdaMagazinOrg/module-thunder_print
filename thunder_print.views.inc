<?php

/**
 * @file
 * Contains thunder_print.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function thunder_print_views_data_alter(array &$data) {

  $data['config_thunder_print_print_article_type']['rendered_thumbnail'] = [
    'title' => t('Rendered thumbnail'),
    'field' => [
      'title' => t('Rendered thumbnail'),
      'id' => 'print_article_type_thumbnail',
    ],
  ];
  $data['config_thunder_print_print_article_type']['translated_status'] = [
    'title' => t('Translated status'),
    'field' => [
      'title' => t('Translated status'),
      'id' => 'print_article_type_status',
    ],
  ];
}
