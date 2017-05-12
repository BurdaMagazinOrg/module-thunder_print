<?php

namespace Drupal\thunder_print\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderManager;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Print article edit forms.
 *
 * @ingroup thunder_print
 */
class PrintArticleForm extends ContentEntityForm {

  protected $httpClient;

  protected $idmsBuilderManager;

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
   * @param \Drupal\thunder_print\Plugin\IdmsBuilderManager $idmsBuilderManager
   *   The idms builder manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, ClientInterface $httpClient, IdmsBuilderManager $idmsBuilderManager) {

    parent::__construct($entity_manager, $entity_type_bundle_info, $time);

    $this->httpClient = $httpClient;
    $this->idmsBuilderManager = $idmsBuilderManager;
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
      $container->get('plugin.manager.thunder_print_idms_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Node author information for administrators.
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
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if (!$this->entity->isNew()) {
      $actions['quick_preview'] = [
        '#type' => 'submit',
        '#value' => $this->t('Quick preview'),
        '#submit' => ['::submitForm', '::quickPreview', '::save'],
      ];
    }

    return $actions;
  }

  /**
   * Grab a quick preview from InDesign server.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function quickPreview(array &$form, FormStateInterface $form_state) {

    $config = $this->config('thunder_print.settings');

    if (!($url = $config->get('general_settings.api_url'))) {
      drupal_set_message($this->t('No url.'), 'warning');
      return;
    }

    $builder = $this->idmsBuilderManager->createInstance('remote', ['print_article' => $this->entity]);

    $response = $this->httpClient->request('POST', $url . '/quickpreview', [
      'multipart' => [
        [
          'name' => 'snippetzip',
          'contents' => $builder->getContent(),
          'filename' => $builder->getFilename(),
        ],
      ],
    ]);

    $zip = new \ZipArchive();
    $zipFilename = tempnam("tmp", "zip");

    file_put_contents($zipFilename, $response->getBody());

    $zip->open($zipFilename);

    $dir = 'public://print-article/';
    file_prepare_directory($dir, FILE_CREATE_DIRECTORY);
    $thumbnail = file_save_data($zip->getFromName('preview.jpg'), 'public://print-article/' . $this->entity->label() . '-preview.jpg', FILE_EXISTS_REPLACE);

    $this->entity->set('image', $thumbnail);

    $zip->close();
    unlink($zipFilename);
  }

}
