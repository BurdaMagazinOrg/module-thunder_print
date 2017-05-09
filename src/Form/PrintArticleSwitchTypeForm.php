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
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $new_print_type = $form_state->getValue('new_print_type');

    $print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($form_state->getValue('print_article'));

    if ($print_article && $new_print_type) {
      $cloned_node = $print_article->createDuplicate();
      $cloned_node->type = $new_print_type;
      $cloned_node->save();

      $form_state->setRedirectUrl($cloned_node->toUrl('edit-form'));
    }

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

      $currentTags = array_keys($print_article_type->getTags());
      sort($currentTags);

      $all_print_article_types = $this->entityTypeManager->getStorage('print_article_type')
        ->loadMultiple();

      $options = [];
      /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $entity */
      foreach ($all_print_article_types as $entity) {
        $tags = array_keys($entity->getTags());
        sort($tags);
        if ($entity->id() != $print_article_type->id() && $currentTags == $tags) {
          $options[$entity->id()] = $entity->label();
        }
      }

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
      $form['error']['#markup'] = 'No valid print article.';
    }

    return $form;
  }

}
