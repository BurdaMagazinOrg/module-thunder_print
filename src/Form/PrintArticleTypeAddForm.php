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
      $form_state->setValue('idms', $xml);
      /** @var \Drupal\thunder_print\Entity\TagMappingInterface $new_entity */
      $new_entity = $this->buildEntity($form, $form_state);

      /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $errors */
      $violations = $new_entity->validate();
      if ($violations->count()) {

        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
          $form_state->setErrorByName($violation->getPropertyPath(), $violation->getMessage());
        }
      }
    }
    else {
      $form_state->setErrorByName('idms', $this->t('It was not possible to upload idms file.'));
    }
  }

}
