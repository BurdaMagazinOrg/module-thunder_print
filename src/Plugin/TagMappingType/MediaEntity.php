<?php

namespace Drupal\thunder_print\Plugin\TagMappingType;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Plugin\TagMappingTypeBase;

/**
 * Provides Tag Mapping for media entity reference.
 *
 * @package Drupal\thunder_print\Plugin\TagMappingType
 * @todo Provide generic entity reference handler.
 *
 * @TagMappingType(
 *   id = "media_entity",
 *   label = @Translation("Media entity"),
 * )
 */
class MediaEntity extends TagMappingTypeBase {

  /**
   * {@inheritdoc}
   */
  public function optionsForm(array $form, FormStateInterface $form_state) {
    $form = parent::optionsForm($form, $form_state);

    $bundles = $this->entityTypeManager->getStorage('media_bundle')
      ->loadMultiple();

    $options = [];

    foreach ($bundles as $bundle) {
      $options[$bundle->id()] = $bundle->label();
    }

    $wrapper_id = Html::getId('tap-mapping-form-ajax-wrapper');

    $form['bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Bundle'),
      '#options' => $options,
      '#default_value' => $this->getOption('bundle'),
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => $wrapper_id,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    $return = [];
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityManager */
    $entityManager = \Drupal::service('entity_field.manager');
    $fields = $entityManager->getFieldDefinitions('media', $this->getOption('bundle'));
    foreach ($fields as $field) {
      if (!$field->getFieldStorageDefinition()->isBaseField()) {
        $return[$field->getName()] = [
          'required' => $field->isRequired(),
          'name' => $field->getLabel(),
        ];
      }
    }

    return $return;
  }

  /**
   * Checks if the given field is supported for mapping.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field
   *   Field definition from the entity bundle.
   *
   * @return bool
   *   Returns TRUE if mapping is supported.
   */
  protected function isFieldSupported(FieldDefinitionInterface $field) {
    // Do not use base fields for mapping.
    if ($field->getFieldStorageDefinition()->isBaseField()) {
      return FALSE;
    }
    // We only support a limitted set of field types for now.
    if (!in_array($field->getType(), ['string', 'text_long', 'image'])) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getMainProperty() {

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->entityTypeManager->getStorage('media_bundle')
      ->load($this->getOption('bundle'));

    if ($bundle) {
      $config = $bundle->getTypeConfiguration();
      if (!empty($config['source_field'])) {
        return $bundle->getTypeConfiguration()['source_field'];
      }

      /** @var \Drupal\Core\Entity\EntityFieldManager $entityManager */
      $entityManager = \Drupal::service('entity_field.manager');

      $fields = array_filter(
        $entityManager->getFieldDefinitions('media', $bundle->id()), function ($field_definition) {
        return $field_definition instanceof FieldConfigInterface;
      }
      );

      if ($fields) {
        /** @var \Drupal\Core\Field\FieldDefinitionInterface $field */
        foreach ($fields as $field) {
          if ($field->isRequired()) {
            return $field->getName();
          }
        }
        $field = reset($fields);
        return $field->getName();
      }
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldStorageDefinition() {
    return [
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'media',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfigDefinition() {
    return [
      'handler' => 'default:media',
      'handler_settings' => [
        'target_bundles' => [$this->getOption('bundle')],
        'sort' => [
          'field' => '_none',
        ],
        'auto_create' => FALSE,
        'auto_create_bundle' => '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $this->entityTypeManager->getStorage('media_bundle')
      ->load($this->getOption('bundle'));

    $dependencies->addDependency('config', $bundle->getConfigDependencyName());

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function replacePlaceholder(IDMS $idms, $fieldItem) {

    foreach ($this->configuration['mapping'] as $field => $tag) {

      $xpath = "(//XmlStory//XMLElement[@MarkupTag='$tag'])[last()]";
      $xmlElement = $idms->getXml()->xpath($xpath)[0];

      if ($xmlElement) {

        $xmlContentId = (string) $xmlElement['XMLContent'];

        $xpath = "//Image[@Self='$xmlContentId']/Link";
        $xmlImageLink = $idms->getXml()->xpath($xpath)[0];

        $media = $this->entityTypeManager
          ->getStorage('media')
          ->load($fieldItem['target_id']);

        $fieldValue = $media->get($field)->first();

        if ($fieldValue) {
          if ($xmlImageLink) {

            /** @var \Drupal\file\Entity\File $file */
            $file = $this->entityTypeManager
              ->getStorage('media')
              ->load($fieldValue->target_id);

            $realpath = \Drupal::service('file_system')
              ->realpath($file->getFileUri());

            $xmlElement['Value'] = 'file://' . $realpath;
            $xmlImageLink['LinkResourceURI'] = $xmlElement['Value'];

          }
          else {
            $xpath = "(//Story//XMLElement[@MarkupTag='$tag'])[last()]";
            $xmlElement = $idms->getXml()->xpath($xpath)[0];

            $errlevel = error_reporting(E_ALL & ~E_WARNING);

            foreach ($xmlElement->children() as $child) {
              unset($child[0]);
            }
            error_reporting($errlevel);

            $xmlElement->Content = trim(strip_tags($fieldValue->value));
          }
        }
      }
    }

    return $idms;
  }

}
