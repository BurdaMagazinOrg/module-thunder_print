<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class PrintArticleTypeForm.
 *
 * @package Drupal\thunder_print\Form
 */
class PrintArticleTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
    $print_article_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $print_article_type->label(),
      '#description' => $this->t("Label for the @print_article_type", [
        '@print_article_type' => $print_article_type->getEntityType()
          ->getLabel(),
      ]),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $print_article_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\thunder_print\Entity\PrintArticleType::load',
      ],
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t("A brief description of this @print_article_type.", [
        '@print_article_type' => $print_article_type->getEntityType()
          ->getLabel(),
      ]),
      '#default_value' => $print_article_type->get('description'),
    ];

    $form['grid'] = [
      '#type' => 'number',
      '#title' => $this->t('Grid'),
      '#description' => $this->t("Grid size for your InDesign document."),
      '#required' => TRUE,
      '#default_value' => $print_article_type->get('grid'),
    ];

    if ($thumbnail_url = $print_article_type->getThumbnailUrl()) {
      $form['thumbnail_file']['data'] = [
        '#theme' => 'image',
        '#uri' => $thumbnail_url,
      ];
    }

    if (!$this->entity->isNew()) {

      $entityCount = $print_article_type->getEntityCount();
      $form['number_articles'] = [
        '#type' => 'item',
        '#markup' => $this->formatPlural($entityCount, '%count article is currently using this @print_article_type.', '%count articles are currently using this @print_article_type.', [
          '%count' => $this->getLinkGenerator()->generate($entityCount, Url::fromRoute('entity.print_article.collection')),
          '@print_article_type' => $print_article_type->getEntityType()->getLabel(),
        ]),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $print_article_type = $this->entity;
    $status = $print_article_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Print article type.', [
          '%label' => $print_article_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Print article type.', [
          '%label' => $print_article_type->label(),
        ]));
    }
    $form_state->setRedirectUrl(Url::fromRoute('entity.entity_form_display.print_article.default', ['print_article_type' => $print_article_type->id()]));
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {

    $actions = parent::actions($form, $form_state);

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
    $print_article_type = $this->entity;

    if (!empty($actions['delete'])) {
      $actions['delete']['#access'] = ($print_article_type->getEntityCount()) ? FALSE : TRUE;
    }

    return $actions;
  }

}
