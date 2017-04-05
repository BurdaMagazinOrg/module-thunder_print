<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class PrintArticleTypeAddForm.
 *
 * @package Drupal\thunder_print\Form
 */
class PrintArticleTypeAddForm extends PrintArticleTypeForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $print_article_type = $this->entity;

    $form['idms'] = [
      '#type' => 'file',
      '#title' => $print_article_type->getEntityType()->getLabel(),
      '#description' => $this->t("IDMS file exported from InDesign."),
    // '#required' => TRUE,.
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $print_article_type = $this->entity;

    $all_files = \Drupal::request()->files->get('files', []);
    // Make sure there's an upload to process.
    if (!empty($all_files['idms'])) {

      $file_upload = $all_files['idms'];
      $print_article_type->set('idms', file_get_contents($file_upload->getPathname()));

      $status = $print_article_type->save();

      switch ($status) {
        case SAVED_NEW:
          drupal_set_message($this->t('Created the %label @print_article_type.', [
            '%label' => $print_article_type->label(),
            '@print_article_type' => $print_article_type->getEntityType()
              ->getLabel(),
          ]));
          break;

        default:
          drupal_set_message($this->t('Saved the %label @print_article_type.', [
            '%label' => $print_article_type->label(),
            '@print_article_type' => $print_article_type->getEntityType()
              ->getLabel(),
          ]));
      }
    }
    else {
      drupal_set_message($this->t('It was not possible to upload idms file for the %label @print_article_type.', [
        '%label' => $print_article_type->label(),
        '@print_article_type' => $print_article_type->getEntityType()
          ->getLabel(),
      ]), 'error');
    }

    $form_state->setRedirectUrl($print_article_type->toUrl('collection'));
  }

}
