<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\thunder_print\ArticleConverter;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PrintArticleConvertForm.
 *
 * @package Drupal\thunder_print\Form
 *
 * @ingroup thunder_print
 */
class PrintArticleConvertForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $articleConverter;

  /**
   * PrintArticleSwitchTypeForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\thunder_print\ArticleConverter $articleConverter
   *   The articel converter service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ArticleConverter $articleConverter) {
    $this->entityTypeManager = $entityTypeManager;
    $this->articleConverter = $articleConverter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('thunder_print.article_converter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'PrintArticle_convertArticle';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $print_article = NULL) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $print_article */
    if ($print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($print_article)) {

      $form['description']['#markup'] = $this->t('Select a target entity and bundle to convert the print article in. The current article will stay after that.');

      $entity_types = [];

      $targets = $this->getTargets($print_article);
      foreach ($targets as $entity_type => $bundles) {
        $definition = $this->entityTypeManager->getDefinition($entity_type);
        $entity_types[$entity_type] = "{$definition->getLabel()} ({$definition->id()})";
      }

      $default_value = NULL;
      if (count($entity_types) == 1) {
        $default_value = key($entity_types);
        $form_state->setValue('entity_type', $default_value);
      }
      elseif (empty($entity_types)) {
        drupal_set_message($this->t("Attention! There are no convert targets defined."), 'warning');
      }

      $form['entity_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Entity type'),
        '#options' => $entity_types,
        '#default_value' => $default_value,
        '#required' => TRUE,
        '#ajax' => [
          'callback' => '::selectEntityType',
          'wrapper' => 'bundle-wrapper',
        ],
      ];

      $options = [];
      if ($entity_type = $form_state->getValue('entity_type')) {
        $definition = $this->entityTypeManager->getDefinition($entity_type);

        $bundles = $this->entityTypeManager->getStorage($definition->getBundleEntityType())
          ->loadMultiple();

        $options = [];
        foreach ($targets[$entity_type] as $bundle) {
          $options[$bundle] = "{$bundles[$bundle]->label()} ($bundle)";
        }
      }

      $default_value = NULL;
      if (count($options) == 1) {
        $default_value = key($options);

        if (count($entity_types) == 1) {
          $form_state->setValue('print_article', $print_article->id());
          $form_state->setValue('bundle', $default_value);

          $this->submitForm($form, $form_state);

          return new TrustedRedirectResponse($form_state->getRedirect()->toString());
        }
      }

      $form['bundle'] = [
        '#type' => 'select',
        '#title' => $this->t('Bundle'),
        '#options' => $options,
        '#default_value' => $default_value,
        '#required' => TRUE,
        '#prefix' => '<div id="bundle-wrapper">',
        '#suffix' => '</div>',
      ];

      $form['print_article'] = [
        '#type' => 'value',
        '#value' => $print_article->id(),
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Convert'),
      ];
    }
    else {
      $form['error']['#markup'] = $this->t('No valid print article.');
    }

    return $form;
  }

  /**
   * Get possible targets.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   Print article.
   *
   * @return array
   *   All the targets.
   */
  protected function getTargets(PrintArticleInterface $printArticle) {
    $targets = [];
    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
    $print_article_type = $printArticle->type->entity;
    foreach ($print_article_type->getMappings() as $mapping) {
      foreach ($mapping->getConvertTargets() as $convertTarget) {
        list($target_entity_type, $target_bundle) = explode('.', $convertTarget);
        if (empty($targets[$target_entity_type]) || !in_array($target_bundle, $targets[$target_entity_type])) {
          $targets[$target_entity_type][] = $target_bundle;
        }
      }
    }
    return $targets;
  }

  /**
   * Ajax callback.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form.
   */
  public function selectEntityType(array &$form, FormStateInterface $form_state) {
    return $form['bundle'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $print_article */
    if ($print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($form_state->getValue('print_article'))) {
      $entity_type = $form_state->getValue('entity_type');
      $bundle = $form_state->getValue('bundle');
      $definition = $this->entityTypeManager->getDefinition($entity_type);

      /** @var \Drupal\Core\Config\Entity\ConfigEntityBundleBase $bundle */
      $bundle = $this->entityTypeManager->getStorage($definition->getBundleEntityType())->load($bundle);

      $entity = $this->articleConverter->printToOnline($print_article, $bundle);

      $request = $this->getRequest();
      if ($request->query->has('destination')) {
        $request->query->remove('destination');
      }

      $form_state->setRedirectUrl($entity->toUrl('edit-form'));
    }

  }

}
