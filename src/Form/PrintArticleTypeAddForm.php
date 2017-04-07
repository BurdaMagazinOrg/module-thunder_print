<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder_print\IDMS;

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
      // It's not possible to require the file field because of
      // https://www.drupal.org/node/59750.
      // '#required' => TRUE,.
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $all_files = \Drupal::request()->files->get('files', []);
    // Make sure there's an upload to process.
    if (!empty($all_files['idms'])) {
      $file_upload = $all_files['idms'];

      $xml = file_get_contents($file_upload->getPathname());
      $idms = new IDMS($xml);

      $errors = $idms->validate();

      if (count($errors) > 1) {
        $form_state->setErrorByName('idms', $this->t('IDMS is not valid.'));
      }

      $form_state->setValue('idms', $xml);
    }
    else {
      $form_state->setErrorByName('idms', $this->t('It was not possible to upload idms file.'));
    }
  }

}
