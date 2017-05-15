<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media_entity\Entity\Media;
use Drupal\thunder_print\Entity\PrintArticle;

/**
 * Tests the idms builder.
 *
 * @group thunder_print
 */
class IDMSBuilderTest extends KernelTestBase {

  protected $adminUser;

  protected $printArticle;

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
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setup();

    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('media');
    $this->installEntitySchema('print_article');

    $this->installConfig(['thunder_print_test', 'system']);

    $path = "public://druplicon.png";
    file_unmanaged_copy(\Drupal::root() . '/core/misc/druplicon.png', $path);

    $image = File::create(['uri' => $path]);
    $image->save();

    $media = Media::create([
      'name' => 'druplicon',
      'bundle' => 'image',
      'field_image' => $image->id(),
      'field_copyright' => 'This is Druplicons image',
    ]);
    $media->save();

    $this->printArticle = PrintArticle::create([
      'name' => 'Zeitung1 article',
      'type' => 'zeitung1',
      'xmltag_image' => $media->id(),
      'xmltag_title' => 'Such a nice article',
    ]);
    $this->printArticle->save();
  }

  /**
   * Test the embedded builder.
   */
  public function testEmbeddedBuilder() {

    /** @var \Drupal\thunder_print\Plugin\IdmsBuilderManager $builder */
    $builderManager = \Drupal::service('plugin.manager.thunder_print_idms_builder');
    /** @var \Drupal\thunder_print\Plugin\IdmsBuilder\EmbeddedBuilder $builder */
    $builder = $builderManager->createInstance('embedded');

    $xml = simplexml_load_string($builder->getContent($this->printArticle));
    $this->assertNotNull($xml);
    $this->assertSame('Zeitung1 article.idms', $builder->getFilename($this->printArticle));
  }

  /**
   * Test the zip builder.
   */
  public function testZipArchiveBuilder() {

    /** @var \Drupal\thunder_print\Plugin\IdmsBuilderManager $builder */
    $builderManager = \Drupal::service('plugin.manager.thunder_print_idms_builder');
    /** @var \Drupal\thunder_print\Plugin\IdmsBuilder\EmbeddedBuilder $builder */
    $builder = $builderManager->createInstance('zip_archived');

    file_put_contents(file_directory_temp() . '/foo.zip', $builder->getContent($this->printArticle));

    $zip = new \ZipArchive();
    $zip->open(file_directory_temp() . '/foo.zip');
    $image = $zip->getFromName('druplicon.png');
    // Check is binary.
    $this->assertTrue(preg_match('~[^\x20-\x7E\t\r\n]~', $image) > 0);

    $idms = $zip->getFromName('Zeitung1 article.idms');

    $xml = simplexml_load_string($idms);
    $this->assertNotNull($xml);

    $this->assertSame('Zeitung1 article.zip', $builder->getFilename($this->printArticle));
  }

}
