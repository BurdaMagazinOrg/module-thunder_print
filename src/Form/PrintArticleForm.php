<?php

namespace Drupal\thunder_print\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\thunder_print\Ajax\QuickPreviewCommand;
use Drupal\thunder_print\IndesignServer;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Form controller for Print article edit forms.
 *
 * @ingroup thunder_print
 */
class PrintArticleForm extends ContentEntityForm {

  use PrintArticleFormTrait;

  const EMPTY_IMAGE_DATA_URI = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';

  protected $httpClient;

  protected $indesignServer;

  protected $queueFactory;

  /**
   * Constructs a ContentEntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The request service.
   * @param \Drupal\thunder_print\IndesignServer $indesignServer
   *   The indesign server service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   Queue service.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, ClientInterface $httpClient, IndesignServer $indesignServer, QueueFactory $queueFactory) {

    parent::__construct($entity_manager, $entity_type_bundle_info, $time);

    $this->httpClient = $httpClient;
    $this->indesignServer = $indesignServer;
    $this->queueFactory = $queueFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('http_client'),
      $container->get('thunder_print.indesign_server'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Create sidebar group.
    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    // Use the same form like node edit.
    $form['#theme'] = ['node_edit_form'];
    $form['#attached']['library'][] = 'thunder_print/thunder_print.ajax';
    $form['#attached']['library'][] = 'thunder_print/thunder_print.ckeditor';
    $form['#attached']['library'][] = 'thunder_print/thunder_print.lightbox';
    $form['#attached']['library'][] = 'seven/node-form';

    // Print article author information for administrators.
    $form['author'] = [
      '#type' => 'details',
      '#title' => $this->t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['node-form-author'],
      ],
      '#attached' => [
        'library' => ['node/drupal.node'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
    ];

    $image_url = ($this->entity->get('image')->entity) ? $this->entity->get('image')->entity->uri->value : static::EMPTY_IMAGE_DATA_URI;
    // Fieldset for print article preview.
    $form['quick_preview'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Quick preview'),
      '#group' => 'advanced',
      '#weight' => 90,
    ];

    $form['quick_preview']['preview'] = [
      '#theme' => 'image',
      '#uri' => $image_url,
      '#group' => 'thunder_print_preview',
      '#attributes' => [
        'id' => 'thunder-print-preview-image',
        'style' => 'max-width: 100%',
      ],
      '#prefix' => '<a href="#" data-featherlight="#thunder-print-preview-image">',
      '#suffix' => '</a>',
    ];

    $form['quick_preview']['quick_preview'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update quick preview'),
      '#submit' => ['::submitForm'],
      '#ajax' => [
        'callback' => '::ajaxQuickPreviewCallback',
      ],
    ];

    if (isset($form['user_id'])) {
      $form['user_id']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    $form['footer'] = [
      '#type' => 'container',
      '#weight' => 99,
    ];
    $form['status']['#group'] = 'footer';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function addRevisionableFormFields(array &$form) {
    parent::addRevisionableFormFields($form);

    if (isset($form['revision_log_message'])) {
      $form['revision_log_message'] += [
        '#group' => 'revision_information',
        '#states' => [
          'visible' => [
            ':input[name="revision"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId($this->currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    $jobId = $this->queuePreviewImageCreation($entity);

    $form_state->set('thunder_print_job_id', $jobId);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Print article.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Print article.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.print_article.canonical', ['print_article' => $entity->id()]);
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

    drupal_get_messages();

    try {
      $jobId = $this->indesignServer->createIdmsJob($this->entity);

      $response = new AjaxResponse();
      $response->addCommand(new QuickPreviewCommand($jobId, '#thunder-print-preview-image'));

      return $response;
    }
    catch (\Exception $e) {
      drupal_set_message($this->t('Error while generating preview.'), 'error');
    }
  }

  /**
   * Checks if a specific idms job is ready.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $job_id
   *   Job id of the current running job.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The download.
   */
  public function fetchQuickPreview(Request $request, $job_id) {
    $response = new Response('', 204);
    $preview = $this->indesignServer->getPreviewById($job_id);
    if ($preview) {
      $response->setStatusCode(200);
      $response->setContent($preview->getPreviewImageDataUri());
    }
    return $response;
  }

}
