<?php

namespace Drupal\thunder_print;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderManager;
use GuzzleHttp\ClientInterface;

/**
 * Class IndesignServer.
 */
class IndesignServer {

  protected $idmsBuilderManager;

  protected $httpClient;

  protected $url;

  /**
   * IndesignServer constructor.
   *
   * @param \Drupal\thunder_print\Plugin\IdmsBuilderManager $manager
   *   IDMS Builder manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory service.
   * @param \GuzzleHttp\ClientInterface $client
   *   HTTP client service.
   *
   * @throws \Exception
   *   Exception if no indesign url was specified.
   */
  public function __construct(IdmsBuilderManager $manager, ConfigFactoryInterface $configFactory, ClientInterface $client) {
    $this->idmsBuilderManager = $manager;
    $this->httpClient = $client;

    if (!($this->url = $configFactory->get('thunder_print.settings')->get('general_settings.api_url'))) {
      throw new \Exception('No url defined for indesign api.');
    }
  }

  /**
   * Schedule a job on the indesign server to render the print article.
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $printArticle
   *   The print article entity.
   *
   * @return string
   *   The job id.
   *
   * @throws \Exception
   *   Exception when error on indesign server occurs.
   */
  public function createIdmsJob(PrintArticleInterface $printArticle) {

    /** @var \Drupal\thunder_print\Plugin\IdmsBuilderInterface $builder */
    $builder = $this->idmsBuilderManager->createInstance('zip_archived');

    $response = $this->httpClient->request('POST', $this->url . '/createIdmsJob', [
      'multipart' => [
        [
          'name' => 'snippetzip',
          'contents' => $builder->getContent($printArticle),
          'filename' => $builder->getFilename($printArticle),
        ],
      ],
    ]);

    if ($response->getStatusCode() != 200) {
      throw new \Exception('No valid response.');
    }

    $xml = new \SimpleXMLElement($response->getBody()->getContents());

    if ((string) $xml->scriptResult !== 'Success!') {
      throw new \Exception((string) $xml->errorMessage, (string) $xml->errorCode);
    }
    return (string) $xml->returnValue;
  }

  /**
   * Retrieve preview image for the given job from the indesign server.
   *
   * @param int $id
   *   The job id provided by createIdmsJob().
   *
   * @return string
   *   Raw data of the preview image.
   */
  public function getPreviewById($id) {

    $response = $this->httpClient->request('GET', $this->url . '/getPreviewById/' . $id, []);

    if ($response->getStatusCode() !== 200) {
      return "";
    }

    return $response->getBody()->getContents();
  }

}
