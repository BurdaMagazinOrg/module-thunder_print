<?php

namespace Drupal\Tests\thunder_print\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\thunder_print\Kernel\TagMappingTrait;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class PrintArticleTypeTest extends JavascriptTestBase {

  use TagMappingTrait;

  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_print',
    'media_entity',
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

    $this->createTagMappings();
  }

  /**
   * Test Creation of a mapping.
   */
  public function testTypeCreation() {

    $this->drupalGet('admin/structure/thunder_print/print_article_type/add');

    $page = $this->getSession()->getPage();

    $label = 'TestType';
    $typeMachineName = strtolower($label);

    $page->fillField('label', $label);

    $this->getSession()->wait(5000, "jQuery('.machine-name-value').text() === '{$typeMachineName}'");

    $page->fillField('description', 'This is a very nice description');
    $page->fillField('grid', '12');

    $fileFieldSelector = "input[type='file']";

    $fileField = $this->assertSession()->elementExists('css', $fileFieldSelector);

    $filePath = dirname(__FILE__) . '/../../fixtures/Zeitung1.idms';
    var_dump($filePath);
    var_dump(realpath($filePath));

    $fileField->attachFile(realpath($filePath));

    $this->assertSession()->assertWaitOnAjaxRequest();

    $page->pressButton('Save');
    var_dump($page->getContent());

    $this->drupalGet('admin/structure/thunder_print/print_article_type/' . $typeMachineName . '/edit');

    $this->assertSession()->statusCodeEquals(200);

    $this->assertSession()->pageTextContains('TestType');

    /** @var \Drupal\thunder_print\Entity\PrintArticleType $testType */
    $testType = $this->container->get('entity_type.manager')
      ->getStorage('print_article_type')
      ->load($typeMachineName);

    $this->assertSame('This is a very nice description', $testType->getDescription());
    $this->assertSame(12, $testType->getGrid());

    $xml = NULL;
    try {
      $xml = @new \SimpleXMLElement($testType->getOriginalIdms());
    }
    catch (\Exception $exception) {
    }
    $this->assertNotNull($xml);

  }

  /**
   * Test that delete button disappears if an article exists.
   */
  public function testDeleteButton() {

    $this->container->get('entity_type.manager')->getStorage('print_article_type')->create([
      'id' => 'test',
      'label' => 'Test',
      'grid' => 12,
      'idms' => file_get_contents(dirname(__FILE__) . '/../../fixtures/Zeitung1.idms'),
    ])->save();

    $this->drupalGet('admin/structure/thunder_print/print_article_type/test/edit');
    $this->assertTrue($this->getSession()->getPage()->hasLink('edit-delete'));

    $this->container->get('entity_type.manager')->getStorage('print_article')->create([
      'type' => 'test',
      'name' => 'Foo',
    ])->save();

    $this->drupalGet('admin/structure/thunder_print/print_article_type/test/edit');
    $this->assertFalse($this->getSession()->getPage()->hasLink('edit-delete'));

  }

}
