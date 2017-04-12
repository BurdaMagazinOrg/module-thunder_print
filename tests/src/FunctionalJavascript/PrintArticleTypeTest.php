<?php

namespace Drupal\Tests\thunder_print\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class PrintArticleTypeTest extends JavascriptTestBase {

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
  public function testTypeCreation() {

    $this->drupalGet('admin/structure/print_article_type/add');

    $page = $this->getSession()->getPage();

    $label = 'TestType';
    $typeMachineName = str_replace(' ', '_', strtolower($label));

    $page->fillField('label', $label);

    $this->getSession()->wait(5000, "jQuery('.machine-name-value').text() === '{$typeMachineName}'");

    $page->fillField('description', 'This is a very nice description');
    $page->fillField('grid', '12');

    $fileFieldSelector = "input[type='file']";

    $fileField = $page->find('css', $fileFieldSelector);

    $filePath = dirname(__FILE__) . '/../../fixtures/Zeitung1.idms';

    $fileField->attachFile(realpath($filePath));

    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->pressButton('Save');

    $this->assertSession()->pageTextContains('TestType');

    /** @var \Drupal\thunder_print\Entity\PrintArticleType $testType */
    $testType = $this->container->get('entity_type.manager')
      ->getStorage('print_article_type')
      ->load($typeMachineName);

    $this->assertSame('This is a very nice description', $testType->getDescription());
    $this->assertSame(12, $testType->getGrid());

    $xml = NULL;
    try {
      $xml = @new \SimpleXMLElement($testType->getIdms());
    }
    catch (\Exception $exception) {
    }
    $this->assertNotNull($xml);

  }

  /**
   * Test that delete button disappears if an article exists.
   */
  public function testDeleteButton() {

    $values = [
      'id' => 'test',
      'label' => 'Test',
      'grid' => 12,
      'idms' => file_get_contents(dirname(__FILE__) . '/../../fixtures/Zeitung1.idms'),
    ];

    $this->container->get('entity_type.manager')->getStorage('print_article_type')->create($values)->save();

    $this->drupalGet('admin/structure/print_article_type/test/edit');
    $this->assertTrue($this->getSession()->getPage()->hasLink('edit-delete'));

    $this->container->get('entity_type.manager')->getStorage('print_article')->create([
      'type' => 'test',
      'name' => 'Foo',
    ])->save();

    $this->drupalGet('admin/structure/print_article_type/test/edit');
    $this->assertFalse($this->getSession()->getPage()->hasLink('edit-delete'));

  }

}
