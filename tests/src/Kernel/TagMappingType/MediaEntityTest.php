<?php

namespace Drupal\Tests\thunder_print\Kernel\TagMappingType;

use Drupal\file\Entity\File;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media_entity\Entity\Media;
use Drupal\thunder_print\Entity\PrintArticle;
use Drupal\thunder_print\Entity\PrintArticleType;
use Drupal\thunder_print\Entity\TagMapping;
use Drupal\thunder_print\IDMS;

/**
 * Tests the media entity integration.
 *
 * @group thunder_print
 */
class MediaEntityTest extends KernelTestBase {

  protected $adminUser;

  protected $printArticle;

  protected $media;

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

    $this->media = Media::create([
      'name' => 'druplicon',
      'bundle' => 'image',
      'field_image' => $image->id(),
      'field_copyright' => 'This is Druplicons image',
    ]);
    $this->media->save();

    $this->printArticle = PrintArticle::create([
      'name' => 'Zeitung1 article',
      'type' => 'zeitung1',
      'xmltag_image' => $this->media->id(),
      'xmltag_title' => 'Such a nice article',
    ]);
    $this->printArticle->save();
  }

  /**
   * Test the embedded builder.
   */
  public function testReplacementWithEmbeddedContent() {

    $tagMapping = TagMapping::load('xmltag_image');
    $mappingType = $tagMapping->getMappingType();

    $printArticleType = PrintArticleType::load('zeitung1');

    $idms = new IDMS($printArticleType->getIdms());
    $xml = $mappingType->replacePlaceholder($idms, ['target_id' => $this->media->id()])->getXml();

    // Test image replacement.
    $xpath = "(//XmlStory//XMLElement[@MarkupTag='XMLTag/Image'])[last()]";
    $xmlElement = $xml->xpath($xpath)[0];

    $xmlContentId = (string) $xmlElement['XMLContent'];
    $xpath = "//Image[@Self='$xmlContentId']";

    $data = (string) $xml->xpath($xpath)[0]->Properties->Contents;
    // Check is binary.
    $this->assertTrue(preg_match('~[^\x20-\x7E\t\r\n]~', base64_decode($data)) > 0);

    $xmlImageLink = (string) $xml->xpath($xpath)[0]->Link['LinkResourceURI'];

    $this->assertContains('file:/druplicon.png', $xmlImageLink);

    // Test copyright replacement.
    $xpath = "//Story//XMLElement[@MarkupTag='XMLTag/Caption']//Content";
    $xmlElement = $xml->xpath($xpath)[0];

    $this->assertSame('This is Druplicons image', (string) $xmlElement);
  }

  /**
   * Test the embedded builder.
   */
  public function testReplacement() {

    $tagMapping = TagMapping::load('xmltag_image');
    $mappingType = $tagMapping->getMappingType();

    $printArticleType = PrintArticleType::load('zeitung1');

    $idms = new IDMS($printArticleType->getIdms());
    $xml = $mappingType->replacePlaceholderUseRelativeLinks($idms, ['target_id' => $this->media->id()])->getXml();

    // Test image replacement.
    $xpath = "(//XmlStory//XMLElement[@MarkupTag='XMLTag/Image'])[last()]";
    $xmlElement = $xml->xpath($xpath)[0];

    $xmlContentId = (string) $xmlElement['XMLContent'];
    $xpath = "//Image[@Self='$xmlContentId']";

    $data = (string) $xml->xpath($xpath)[0]->Properties->Contents;
    // Check is binary.
    $this->assertFalse(preg_match('~[^\x20-\x7E\t\r\n]~', base64_decode($data)) > 0);

    $xmlImageLink = (string) $xml->xpath($xpath)[0]->Link['LinkResourceURI'];

    $this->assertContains('file:/druplicon.png', $xmlImageLink);

    // Test copyright replacement.
    $xpath = "//Story//XMLElement[@MarkupTag='XMLTag/Caption']//Content";
    $xmlElement = $xml->xpath($xpath)[0];

    $this->assertSame('This is Druplicons image', (string) $xmlElement);
  }

}
