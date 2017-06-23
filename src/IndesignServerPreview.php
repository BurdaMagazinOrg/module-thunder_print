<?php

namespace Drupal\thunder_print;

/**
 * Represenation of an Indesign server preview content.
 */
class IndesignServerPreview {

  /**
   * @var string
   */
  protected $raw;

  /**
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
   */
  public function getPreviewImageContent() {
    return $this->zip->getFromName('preview.jpg');
  }

  /**
   * Provides data uri for preview image.
   *
   * @return string
   */
  public function getPreviewImageDataURI() {
    return 'data:image/jpeg;base64,' . base64_encode($this->getPreviewImageContent());
  }

  /**
   * Destructor for the preview archive.
   */
  function __destruct() {
    $this->zip->close();
    if ($this->zipFilename) {
      unlink($this->zipFilename);
    }
  }
}
