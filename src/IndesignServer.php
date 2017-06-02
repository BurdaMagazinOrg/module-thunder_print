<?php

namespace Drupal\thunder_print;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\thunder_print\Entity\PrintArticleInterface;
use Drupal\thunder_print\Plugin\IdmsBuilderManager;
use GuzzleHttp\ClientInterface;

class IndesignServer {

  protected $idmsBuilderManager;

  protected $httpClient;

  protected $url;

  public function __construct(IdmsBuilderManager $manager, ConfigFactoryInterface $configFactory, ClientInterface $client) {
    $this->idmsBuilderManager = $manager;
    $this->httpClient = $client;

    if (!($this->url = $configFactory->get('thunder_print.settings')->get('general_settings.api_url'))) {
      throw new \Exception('No url defined for indesign api.');
    }
  }

  public function createJob(PrintArticleInterface $printArticle) {

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

  public function fetchJob($id) {

    $response = $this->httpClient->request('GET', $this->url . '/getPreviewById/' . $id, []);


    return $response->getBody()->getContents();

  }

}
