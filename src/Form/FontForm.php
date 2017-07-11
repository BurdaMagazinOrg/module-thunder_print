<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Font edit forms.
 *
 * @ingroup thunder_print
 */
class FontForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\thunder_print\Entity\Font */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    $form['file']['#access'] = $entity->isNew();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Font.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Font.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.thunder_print_font.collection');
  }

}
