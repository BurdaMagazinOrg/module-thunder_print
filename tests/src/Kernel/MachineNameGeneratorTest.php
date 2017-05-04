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
   * Holds the machine name generator service.
   *
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
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->generator = $this->container->get('thunder_print.machine_name');
  }

  /**
   * Test the machine name generation.
   *
   * @param string $input
   *   Original value to convert.
   * @param string $expected
   *   Expected machine_name value.
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
   *   Original value to convert.
   * @param string $expected
   *   Provides expected machine_name value when $expected_unique is not given.
   * @param string $expected_unique
   *   Optionally provides expected result for unique machine name.
   *
   * @dataProvider nameProvider
   */
  public function testUniqueGenerateMachineName($input, $expected, $expected_unique = NULL) {
    $this->assertSame($this->generator->generateUniqueMachineName($input, '\Drupal\Tests\thunder_print\Kernel\MachineNameGeneratorTest::machineNameExists'), isset($expected_unique) ? $expected_unique : $expected);
  }

  /**
   * Data provider for machine names.
   *
   * @return array
   *   Array of test data
   *   - Human readable name
   *   - machine name / expected result.
   *   - optionally a machine name / expected result for unique generator
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
      ],
    ];
  }

  /**
   * Machine name exists callback for the tests.
   *
   * @param string $input
   *   Provides input string to be checked for existance.
   *
   * @return bool
   *   TRUE if machine name exists in static preset list.
   */
  public static function machineNameExists($input) {
    return in_array($input, static::$existingMachineNames);
  }

}
