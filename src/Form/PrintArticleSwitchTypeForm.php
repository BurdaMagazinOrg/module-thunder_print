<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PrintArticleSwitchTypeForm.
 *
 * @package Drupal\thunder_print\Form
 *
 * @ingroup thunder_print
 */
class PrintArticleSwitchTypeForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * PrintArticleSwitchTypeForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'PrintArticle_switchType';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $print_article = NULL) {

    $print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($print_article);

    if ($print_article) {

      /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
      $print_article_type = $this->entityTypeManager->getStorage('print_article_type')
        ->load($print_article->bundle());

      $options = [];
      /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $entity */
      foreach ($print_article_type->getSwitchableBundles() as $entity) {
        $options[$entity->id()] = $entity->label();
      }

      if ($options) {
        $form['new_print_type'] = [
          '#type' => 'select',
          '#title' => $this->t('New snippet template'),
          '#options' => $options,
          '#required' => TRUE,
        ];

        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Switch template'),
        ];
        $form['print_article'] = [
          '#type' => 'hidden',
          '#value' => $print_article->id(),
        ];
      }
      else {
        $form['error']['#markup'] = $this->t('No matching snippet templates.');
      }

    }
    else {
      $form['error']['#markup'] = $this->t('No valid print article.');
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $new_print_type = $form_state->getValue('new_print_type');

    $print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($form_state->getValue('print_article'));

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_type */
    $print_type = $this->entityTypeManager->getStorage('print_article_type')
      ->load($print_article->bundle());

    $possibleBundles = array_keys($print_type->getSwitchableBundles());

    if (!in_array($new_print_type, $possibleBundles) || !$print_article) {
      $form_state->setErrorByName('new_print_type', $this->t('Not a valid print article type.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $new_print_type = $form_state->getValue('new_print_type');

    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $print_article */
    $print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($form_state->getValue('print_article'));

    if ($print_article && $new_print_type) {
      $print_article->type = $new_print_type;

      $print_article->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $print_article->setRevisionCreationTime(REQUEST_TIME);
      $print_article->setRevisionUserId($this->currentUser()->id());

      $print_article->save();

      $form_state->setRedirectUrl($print_article->toUrl('edit-form'));
    }
  }

}
