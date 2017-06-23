<?php

namespace Drupal\thunder_print;

/**
 * Represenation of an Indesign server preview content.
 */
class IndesignServerPreview {

  /**
   * Raw response for the preview.
   *
   * @var string
   */
  protected $raw;

  /**
   * Filename of the zip file.
   *
   * @var bool|string
   */
  protected $zipFilename;

  /**
   * Constructor for preview data handling.
   *
   * @param string $raw
   *   Raw response for the preview.
   */
  public function __construct($raw) {
    $this->raw = $raw;
    $this->zipFilename = tempnam("IndesignServerPreview", "zip");

    // Create zip archive to work with.
    $this->zip = new \ZipArchive();
    file_put_contents($this->zipFilename, $this->raw);
    $this->zip->open($this->zipFilename);
  }

  /**
   * Provides raw content of the preview image.
   *
   * @return string
   *   Raw preview image.
   */
  public function getPreviewImageContent() {
    return $this->zip->getFromName('preview.jpg');
  }

  /**
   * Provides data uri for preview image.
   *
   * @return string
   *   Base64 encoded preview image.
   */
  public function getPreviewImageDataUri() {
    return 'data:image/jpeg;base64,' . base64_encode($this->getPreviewImageContent());
  }

  /**
   * Destructor for the preview archive.
   */
  public function __destruct() {
    $this->zip->close();
    if ($this->zipFilename) {
      unlink($this->zipFilename);
    }
  }

}
