<?php

namespace Drupal\thunder_print\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Tag Mapping entities.
 */
interface TagMappingInterface extends ConfigEntityInterface {

  /**
   * Provides the raw mapping type id.
   *
   * @return string
   *   Machine name of the associated mapping type plugin.
   */
  public function getMappingTypeId();

  /**
   * Provides the mapping relation.
   *
   * @return array
   *   Mapping relation as key/value association.
   *   - key: Mapping property (e.g. `value`)
   *   - value: Tag name (e.g. from IDMS)
   */
  public function getMapping();

  /**
   * Provides the tag for a specific property.
   *
   * @param string $property
   *   Mapping property to get tag for.
   *
   * @return string
   *   The tag assigned to the given property.
   */
  public function getTag($property);

  /**
   * Provides the main tag of this mapping.
   *
   * This main tag is used to generate an ID.
   *
   * @return string
   *   Tag name of the main tag.
   */
  public function getMainTag();

  /**
   * Provides a list of tags used by this mapping.
   *
   * @return array
   *   A list of tags associated to this mapping.
   */
  public function getTags();

  /**
   * Provides the associated mapping type plugin instance.
   *
   * @return \Drupal\thunder_print\Plugin\TagMappingTypeInterface
   *   Mapping type instance of this mapping.
   */
  public function getMappingType();

  /**
   * Retrieve the options for this mapping instance.
   *
   * @return array
   *   List of key value pairs of options.
   */
  public function getOptions();

  /**
   * Retrieve a single option value for this mapping instance.
   *
   * @param string $key
   *   Option key to get value for.
   *
   * @return mixed
   *   Arbitrary value assigned to the option.
   */
  public function getOption($key);

  /**
   * Validates the currently set values.
   *
   * @return \Symfony\Component\Validator\ConstraintViolationListInterface
   *   A list of constraint violations. If the list is empty, validation
   *   succeeded.
   */
  public function validate();

  /**
   * Create fields for the current bundle.
   *
   * @param string $entity_type_id
   *   Entity type to create the fields.
   * @param string $bundle
   *   Entity bundle to create the fields.
   */
  public function createField($entity_type_id, $bundle);

}
