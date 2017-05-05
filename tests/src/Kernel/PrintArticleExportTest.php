<?php

namespace Drupal\Tests\thunder_print\Kernel;

use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media_entity\Entity\Media;
use Drupal\thunder_print\Entity\PrintArticle;

/**
 * Tests the mapping creation.
 *
 * @group thunder_print
 */
class PrintArticleExportTest extends KernelTestBase {

  protected $adminUser;

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
  }

  /**
   * Test placeholder replacement for media entity.
   */
  public function testMediaReplacement() {
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

    $printArticle = PrintArticle::create([
      'name' => 'Zeitung1 article',
      'type' => 'zeitung1',
      'xmltag_image' => $media->id(),
    ]);
    $printArticle->save();

    $idms = $printArticle->replaceText();

    // Test image replacement.
    $xpath = "(//XmlStory//XMLElement[@MarkupTag='XMLTag/Image'])[last()]";
    $xmlElement = $idms->getXml()->xpath($xpath)[0];

    $xmlContentId = (string) $xmlElement['XMLContent'];
    $xpath = "//Image[@Self='$xmlContentId']/Link";
    $xmlImageLink = (string) $idms->getXml()->xpath($xpath)[0]['LinkResourceURI'];

    $this->assertContains('files/druplicon.png', $xmlImageLink);
    $this->assertContains('files/druplicon.png', (string) $xmlElement['Value']);

    // Test copyright replacement.
    $xpath = "(//Story//XMLElement[@MarkupTag='XMLTag/Caption'])[last()]/Content";
    $xmlElement = $idms->getXml()->xpath($xpath)[0];

    $this->assertSame('This is Druplicons image', (string) $xmlElement);

  }

}
