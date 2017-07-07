<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use FontLib\Font as FontLib;
use FontLib\Table\Type\name;

/**
 * Defines the Font entity.
 *
 * @ingroup thunder_print
 *
 * @ContentEntityType(
 *   id = "thunder_print_font",
 *   label = @Translation("Font"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\thunder_print\FontListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\thunder_print\Form\FontForm",
 *       "add" = "Drupal\thunder_print\Form\FontForm",
 *       "edit" = "Drupal\thunder_print\Form\FontForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\thunder_print\FontAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "thunder_print_font",
 *   admin_permission = "administer font entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/thunder_print/thunder_print_font/{thunder_print_font}",
 *     "add-form" = "/admin/structure/thunder_print/thunder_print_font/add",
 *     "edit-form" = "/admin/structure/thunder_print/thunder_print_font/{thunder_print_font}/edit",
 *     "delete-form" = "/admin/structure/thunder_print/thunder_print_font/{thunder_print_font}/delete",
 *     "collection" = "/admin/structure/thunder_print/thunder_print_font",
 *   },
 *   field_ui_base_route = "entity.thunder_print_font.edit_form"
 * )
 */
class Font extends ContentEntityBase implements FontInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Font entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Font entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ]);

    $fields['file'] = BaseFieldDefinition::create('file')
      ->setLabel(t('File'))
      ->setDescription(t('Font file field'))
      ->setSettings([
        'file_directory' => 'thunder-print-fonts',
        'file_extensions' => 'woff otf',
      ])
      ->setDisplayOptions('form', [
        'type' => 'file_generic',
        'weight' => -4,
      ]);

    $fields['font'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Font'))
      ->setDescription(t('The font name.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('');

    $fields['font_style'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Font style'))
      ->setDescription(t('The font style name.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    if (!$update) {

      $font = FontLib::load($this->get('file')->entity->uri->value);
      $font->parse();

      $this->set('font', $font->getNameTableString(name::NAME_PREFERRE_FAMILY));
      $this->set('font_style', $font->getNameTableString(name::NAME_PREFERRE_SUBFAMILY));
      $this->save();

      /** @var \Drupal\thunder_print\CssFileGeneration $font */
      $font = \Drupal::service('thunder_print.css_generation');
      $font->generateCssFile();

    }
  }

}
