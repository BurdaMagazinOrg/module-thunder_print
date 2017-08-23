<?php

namespace Drupal\thunder_print\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\IndesignServer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PrintArticleSwitchTypeForm.
 *
 * @package Drupal\thunder_print\Form
 *
 * @ingroup thunder_print
 */
class PrintArticleSwitchTypeForm extends FormBase {

  use PrintArticleFormTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $indesignServer;

  protected $queueFactory;

  /**
   * PrintArticleSwitchTypeForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\thunder_print\IndesignServer $indesignServer
   *   The indesign server service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   Queue service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, IndesignServer $indesignServer, QueueFactory $queueFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->indesignServer = $indesignServer;
    $this->queueFactory = $queueFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('thunder_print.indesign_server'),
      $container->get('queue')
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

    drupal_set_message($this->t("Attention! It's not possible to switch back after switching to an other Snippet-Template."), 'warning');

    if ($print_article = $this->entityTypeManager->getStorage('print_article')->load($print_article)) {

      $options = [];
      /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $entity */
      foreach ($print_article->type->entity->getSwitchableBundles() as $entity) {
        $options[$entity->id()] = $entity->label();
      }

      if ($options) {
        $form['new_print_type'] = [
          '#type' => 'select',
          '#title' => $this->t('New snippet template'),
          '#options' => $options,
          '#required' => TRUE,
          '#ajax' => [
            'callback' => '::ajaxQuickPreviewCallback',
          ],
        ];

        $form['#attached']['library'][] = 'thunder_print/thunder_print.ajax';

        $form['quick_preview'] = [
          '#type' => 'fieldset',
          '#title' => $this->t('Quick preview'),
          '#group' => 'advanced',
        ];

        $form['quick_preview']['preview'] = [
          '#theme' => 'image',
          '#uri' => static::$emptyImagaDataUri,
          '#group' => 'thunder_print_preview',
          '#attributes' => [
            'id' => 'thunder-print-preview-image',
            'style' => 'max-width: 100%',
          ],
          '#prefix' => '<a href="#" data-featherlight="#thunder-print-preview-image">',
          '#suffix' => '</a>',
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
      $print_article = $this->switchTypeOfPrintArticle($print_article, $new_print_type);

      $print_article->save();

      $this->queuePreviewImageCreation($print_article);

      $form_state->setRedirectUrl($print_article->toUrl('edit-form'));
    }
  }

  /**
   * Switches the bundle type of a print article.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   Print article.
   * @param string $newType
   *   Type to switch.
   *
   * @return \Drupal\thunder_print\Entity\PrintArticleInterface
   *   Switched article.
   */
  protected function switchTypeOfPrintArticle(PrintArticleInterface $printArticle, $newType) {
    $printArticle->type = $newType;

    $printArticle->setNewRevision();

    // If a new revision is created, save the current user as revision author.
    $printArticle->setRevisionCreationTime(REQUEST_TIME);
    $printArticle->setRevisionUserId($this->currentUser()->id());

    return $printArticle;
  }

  /**
   * Grab a quick preview from InDesign server.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Response with command.
   */
  public function ajaxQuickPreviewCallback(array &$form, FormStateInterface $form_state) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle */
    $printArticle = $this->entityTypeManager->getStorage('print_article')
      ->load($form_state->getValue('print_article'));

    $new_print_type = $form_state->getValue('new_print_type');

    $this->entity = $this->switchTypeOfPrintArticle($printArticle, $new_print_type);

    return $this->genericAjaxQuickPreviewCallback($printArticle);
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param int $print_article
   *   Print article id.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Access result.
   */
  public function access(AccountInterface $account, $print_article = NULL) {
    /** @var \Drupal\thunder_print\Entity\PrintArticleInterface $print_article */
    $print_article = $this->entityTypeManager->getStorage('print_article')
      ->load($print_article);
    return AccessResult::allowedIf($print_article && $print_article->type->entity->getSwitchableBundles());
  }

}
