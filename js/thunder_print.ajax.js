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

    var prevNowPlaying = setInterval(function (jobId) {

      $.get('/print-article/jobFinished/' + jobId, function (data) {

        if (!parseInt(data)) {
          clearInterval(prevNowPlaying);
          var image = $('#preview-image')[0];
          image.src = image.src + '?' + new Date().getTime();
        }
      });
    }, 2000, response.job_id);

  };


})(jQuery, Drupal);
