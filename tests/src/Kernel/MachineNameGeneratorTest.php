<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Test machine name generator.
 *
 * @group thunder_print
 */
class MachineNameGeneratorTest extends KernelTestBase {

  /**
   * @var \Drupal\thunder_print\MachineNameGeneratorInterface
   */
  protected $generator;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'thunder_print',
  ];

  /**
   * List of machine names to be seen as existant when testing.
   *
   * @var array
   */
  protected static $existingMachineNames = [
    'machine_name_exists',
  ];

  /**
   * @inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->generator = $this->container->get('thunder_print.machine_name');
  }

  /**
   * Test the machine name generation.
   *
   * @param string $input
   * @param string $expected
   *
   * @dataProvider nameProvider
   */
  public function testGenerateMachineName($input, $expected) {
    $this->assertSame($this->generator->generateMachineName($input), $expected);
  }

  /**
   * Test the machine name generation for uniques.
   *
   * @param string $input
   * @param string $expected
   *
   * @dataProvider nameProvider
   */
  public function testUniqueGenerateMachineName($input, $expected, $expected_unique = NULL) {
    $this->generator->setExistsCallback('\Drupal\Tests\thunder_print\Kernel\MachineNameGeneratorTest::machineNameExists');
    $this->assertSame($this->generator->generateUniqueMachineName($input), isset($expected_unique) ? $expected_unique : $expected);
  }

  /**
   * Data provider for machine names.
   *
   * @return array
   *   Array of test data
   *   - Human readable name
   *   - machine name / expected result.
   */
  public function nameProvider() {
    return [
      [
        'XMLTag/Title',
        'xmltag_title',
      ],
      [
        'Machine Name Exists',
        'machine_name_exists',
        'machine_name_exists_1',
      ]
    ];
  }

  /**
   * Machine name exists callback for the tests.
   *
   * @param string $input
   *
   * @return bool
   */
  public static function machineNameExists($input) {
    return in_array($input, static::$existingMachineNames);
  }

}
