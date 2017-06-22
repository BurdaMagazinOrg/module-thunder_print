/**
 * @file
 * Extends the Drupal AJAX functionality to integrate with thunder for print.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Command to trigger an interval timer.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {object} response
   *   Object holding the server response.
   * @param {number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.initQueueWatcher = function (ajax, response, status) {

    var watcher = setInterval(function (jobId, print_article_id) {

      $.ajax('/print-article/jobFinished/' + print_article_id + '/' + jobId, {
        type: 'GET',
        statusCode: {
          200: function (data) {
            clearInterval(watcher);
            $('#preview-image')[0].src = data + '?' + new Date().getTime();
          }
        }
      });

    }, 2000, response.job_id, response.print_article_id);

  };

})(jQuery, Drupal);
