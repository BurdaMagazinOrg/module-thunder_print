<?php

namespace Drupal\thunder_print\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\thunder_print\Entity\PrintArticleInterface;

/**
 * Class PrintArticleController.
 *
 *  Returns responses for Print article routes.
 *
 * @package Drupal\thunder_print\Controller
 */
class PrintArticleController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Print article  revision.
   *
   * @param int $print_article_revision
   *   The Print article  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($print_article_revision) {
    $print_article = $this->entityManager()->getStorage('print_article')->loadRevision($print_article_revision);
    $view_builder = $this->entityManager()->getViewBuilder('print_article');

    return $view_builder->view($print_article);
  }

  /**
   * Page title callback for a Print article  revision.
   *
   * @param int $print_article_revision
   *   The Print article  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($print_article_revision) {
    $print_article = $this->entityManager()->getStorage('print_article')->loadRevision($print_article_revision);
    return $this->t('Revision of %title from %date', array('%title' => $print_article->label(), '%date' => format_date($print_article->getRevisionCreationTime())));
  }

  /**
   * Generates an overview table of older revisions of a Print article .
   *
   * @param \Drupal\thunder_print\Entity\PrintArticleInterface $print_article
   *   A Print article  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(PrintArticleInterface $print_article) {
    $account = $this->currentUser();
    $langcode = $print_article->language()->getId();
    $langname = $print_article->language()->getName();
    $languages = $print_article->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $print_article_storage = $this->entityManager()->getStorage('print_article');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $print_article->label()]) : $this->t('Revisions for %title', ['%title' => $print_article->label()]);
    $header = array($this->t('Revision'), $this->t('Operations'));

    $revert_permission = (($account->hasPermission("revert all print article revisions") || $account->hasPermission('administer print article entities')));
    $delete_permission = (($account->hasPermission("delete all print article revisions") || $account->hasPermission('administer print article entities')));

    $rows = array();

    $vids = $print_article_storage->revisionIds($print_article);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\thunder_print\PrintArticleInterface $revision */
      $revision = $print_article_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->revision_timestamp->value, 'short');
        if ($vid != $print_article->getRevisionId()) {
          $link = $this->l($date, new Url('entity.print_article.revision', ['print_article' => $print_article->id(), 'print_article_revision' => $vid]));
        }
        else {
          $link = $print_article->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->revision_log_message->value, '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.print_article.translation_revert', ['print_article' => $print_article->id(), 'print_article_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.print_article.revision_revert', ['print_article' => $print_article->id(), 'print_article_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.print_article.revision_delete', ['print_article' => $print_article->id(), 'print_article_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['print_article_revisions_table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $build;
  }

}
