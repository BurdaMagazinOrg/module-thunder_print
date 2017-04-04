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
class IDMSTest extends UnitTestCase {

  /**
   * Test the extracting of tags.
   *
   * @dataProvider tagProvider
   */
  public function testGetTags($filename, $tags) {

    $idms = new IDMS(file_get_contents($filename));

    $this->assertArrayEquals($tags, $idms->getTags());

  }

  /**
   * DataProvider for testGetTags.
   *
   * @return array
   *   Filename and expected tags.
   */
  public function tagProvider() {
    return [
      [
        dirname(__FILE__) . '/../../assets/Zeitung1.idms',
        [
          'XMLTag/Story',
          'XMLTag/Caption',
          'XMLTag/Image',
          'XMLTag/Title',
          'XMLTag/Lead',
          'XMLTag/Author',
          'XMLTag/Body',
        ],
      ],
      [
        dirname(__FILE__) . '/../../assets/Zeitung2.idms',
        [
          'XMLTag/Textabschnitt',
          'XMLTag/Author',
          'XMLTag/Body',
          'XMLTag/Title',
          'XMLTag/Lead',
        ],
      ],
    ];
  }

  /**
   * Test the extracting of tags.
   *
   * @dataProvider uniqueTagsValidationProvider
   */
  public function testUniqueTagsValidation($filename, $expectedViolations) {
    $idms = new IDMS(file_get_contents($filename));

    $validator = Validation::createValidator();

    $violations = $validator->validate($idms, [
      new IdmsUniqueTags(),
    ]);

    $this->assertSame($expectedViolations, count($violations));
  }

  /**
   * DataProvider for testValidation.
   *
   * @return array
   *   Filename and expected violations.
   */
  public function uniqueTagsValidationProvider() {
    return [
      [dirname(__FILE__) . '/../../assets/Zeitung1.idms', 0],
      [dirname(__FILE__) . '/../../assets/Zeitung2.idms', 1],
    ];
  }

}
