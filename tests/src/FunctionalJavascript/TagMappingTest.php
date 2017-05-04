<?php

namespace Drupal\Tests\thunder_print\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\thunder_print\Entity\TagMapping;

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
    $this->assertSession()->elementExists('css', 'input[name="mapping[value]"]');

    $value_tag = 'XMLTag/TestTag';

    $page->fillField('mapping[value]', $value_tag);
    $page->checkField('options[title]');
    $page->pressButton('Save');

    // Checks if page is redirected after save and mapping with the given name
    // exists.
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals('admin/structure/thunder_print/tag_mapping');
    $this->assertSession()->pageTextContains('XMLTag/TestTag');

    // Make mapping is saved and title option is set.
    $mapping = TagMapping::loadMappingForTag($value_tag);
    $this->assertNotNull($mapping, sprintf('Mapping with tag %s exists.', $value_tag));
    $this->assertSame($mapping->getOption('title'), TRUE, 'Title option is set for created mapping.');
    $this->assertSame($mapping->getTag('value'), $value_tag);
  }
}
