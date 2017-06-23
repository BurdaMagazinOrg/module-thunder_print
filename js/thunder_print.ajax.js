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
  Drupal.AjaxCommands.prototype.thunderPrintQuickPreview = function (ajax, response, status) {

    var watcher = setInterval(function (jobId) {

      $.ajax('/print-article/quick-preview/' + jobId, {
        type: 'GET',
        statusCode: {
          200: function (data) {
            clearInterval(watcher);
            $('#preview-image')[0].src = data;
          }
        }
      });

    }, 2000, response.job_id);

  };

})(jQuery, Drupal);
