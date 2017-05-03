<?php

namespace Drupal\thunder_print\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class PrintArticleController.
 *
 *  Returns responses for Print article routes.
 *
 * @package Drupal\thunder_print\Controller
 */
class PrintArticleTypeController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Toggles the status of a print article type.
   *
   * @param string $print_article_type
   *   The type of the print article.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to the collection page.
   */
  public function toggleStatus($print_article_type) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
    $print_article_type = $this->entityTypeManager()
      ->getStorage('print_article_type')
      ->load($print_article_type);

    $print_article_type
      ->setStatus(!$print_article_type->status())
      ->save();

    return $this->redirect('entity.print_article_type.collection');
  }

  /**
   * Downloads the original idms file.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $print_article_type
   *   The type of the print article.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   The download.
   */
  public function downloadIdms(Request $request, $print_article_type) {

    /** @var \Drupal\thunder_print\Entity\PrintArticleTypeInterface $print_article_type */
    $print_article_type = $this->entityTypeManager()
      ->getStorage('print_article_type')
      ->load($print_article_type);

    $response = new StreamedResponse(
      function () use ($print_article_type) {
        echo $print_article_type->getIdms();
      });

    $response->headers->set('Content-Type', 'application/xml');
    $response->headers->set('Cache-Control', '');
    $response->headers->set('Content-Length', strlen($print_article_type->getIdms()));
    $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
    $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $print_article_type->label() . '.idms');
    $response->headers->set('Content-Disposition', $contentDisposition);
    $response->prepare($request);

    return $response;
  }

}
