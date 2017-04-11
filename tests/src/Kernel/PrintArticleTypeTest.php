<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class PrintArticleTypeTest extends KernelTestBase {

  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_print',
    'field',
    'text',
  ];

  /**
   * Test the automatic generation of fields on bundle creation.
   */
  public function testBundleFieldCreation() {

    $bundle_name = 'test';

    $values = [
      'id' => $bundle_name,
      'label' => 'Test',
      'grid' => 12,
      'idms' => file_get_contents(dirname(__FILE__) . '/../../fixtures/Zeitung1.idms'),
    ];

    $fields = [
      'tag1',
    ];

    // Check that fields does not exists.
    foreach ($fields as $field) {
      $field = $this->container->get('entity_type.manager')
        ->getStorage('field_config')
        ->load("print_article.$bundle_name.$field");

      $this->assertNull($field);
    }

    $bundle = $this->container->get('entity_type.manager')
      ->getStorage('print_article_type')
      ->create($values);

    $bundle->save();

    // Check that fields exists now.
    foreach ($fields as $field) {
      $field = $this->container->get('entity_type.manager')
        ->getStorage('field_config')
        ->load("print_article.$bundle_name.$field");

      $this->assertNotNull($field);
    }

  }

}
