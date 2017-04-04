<?php

namespace Drupal\Tests\thunder_print\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class MappingTest extends JavascriptTestBase {

  /**
   * Test Creation of a mapping.
   */
  public function testMappingCreation() {

    $this->drupalGet('<front>');

    $this->assertSession()->pageTextContains('Drupal');
  }

}
