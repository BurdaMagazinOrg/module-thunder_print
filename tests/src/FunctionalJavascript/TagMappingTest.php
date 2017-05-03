<?php

namespace Drupal\Tests\thunder_print\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Tests interface for tag mapping creation.
 *
 * @group thunder_print
 */
class TagMappingTest extends JavascriptTestBase {

  /**
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_print',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',

    ]);
    $this->drupalLogin($this->adminUser);
  }


  /**
   * Test Creation of a mapping.
   */
  public function testTagMappingAddForm() {

    $this->drupalGet('admin/structure/thunder_print/tag_mapping/add');

    $page = $this->getSession()->getPage();
    // Select "Text plain" type.
    $page->selectFieldOption('mapping_type', 'text_plain');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Check for mapping value existance.
    $this->assertSession()->elementExists('css', 'input[name="mapping[0][tag]"]');

    $value_tag = 'XMLTag/TestTag';

    $page->fillField('mapping[0][tag]', $value_tag);

    $page->pressButton('Save');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals('admin/structure/thunder_print/tag_mapping');
    $this->assertSession()->pageTextContains('XMLTag/TestTag');
  }
}
