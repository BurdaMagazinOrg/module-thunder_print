<?php

namespace Drupal\Tests\thunder_print\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\thunder_print\IDMS;
use Drupal\thunder_print\Validator\Constraints\IdmsUniqueTags;
use Symfony\Component\Validator\Validation;

/**
 * Test operations on an idms file.
 *
 * @group thunder_print
 */
class IDMSReplacementTest extends UnitTestCase {

  public function testPlainTextReplacement() {

    $filename = dirname(__FILE__) . '/../../fixtures/ManuelleFormate.idms';
    $idms = new IDMS(file_get_contents($filename));

    $lead = "<Content>Zwei Firmen, ein Gebäude und eine IT-Infrastruktur: Bei der Akzidenzdruckerei Triner AG und dem «Boten der Urschweiz», der Tageszeitung des Kantons Schwyz, wurde Anfang Jahr ein neues Backup-Konzept namens Veeam Backup und Replication integriert. </Content>";

    $this->assertSame($lead, $idms->getContent('XMLTag/Lead'));



  }

}
