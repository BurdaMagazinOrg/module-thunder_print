<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class PrintArticleExportTest extends KernelTestBase {

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
    'image',
    'file',
    'thunder_print_test',
    'media_entity',
  ];

  /**
   * Test placeholder replacement for media entity.
   */
  public function testMediaReplacement() {
    $this->installConfig(['thunder_print_test']);
  }

}
