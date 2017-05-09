<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
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
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * PrintArticleSwitchTypeForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
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

    $new_print_type = $this->entityTypeManager->getStorage('print_article_type')
      ->load($new_print_type);

    if ($print_article && $new_print_type) {
      $print_article->type = $new_print_type->id();
      $print_article->save();

      $form_state->setRedirectUrl($print_article->toUrl('edit-form'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $print_article = NULL) {

    $print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($print_article);

    if ($print_article) {

      $fieldDefinitions = $this->entityFieldManager->getFieldDefinitions('print_article', $print_article->bundle());
      $fieldDefinitions = array_keys($fieldDefinitions);
      sort($fieldDefinitions);

      $all_print_article_types = $this->entityTypeManager->getStorage('print_article_type')
        ->loadMultiple();

      $options = [];
      /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $entity */
      foreach ($all_print_article_types as $entity) {
        $bundleFieldDefinitions = $this->entityFieldManager->getFieldDefinitions('print_article', $entity->id());
        $bundleFieldDefinitions = array_keys($bundleFieldDefinitions);
        sort($bundleFieldDefinitions);

        if ($entity->id() != $print_article->bundle() && empty(array_diff($fieldDefinitions, $bundleFieldDefinitions))) {
          $options[$entity->id()] = $entity->label();
        }
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

}
