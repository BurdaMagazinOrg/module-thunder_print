/**
 * @file
 * Extends the Drupal AJAX functionality to integrate the dialog API.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.thunder_print = {

    watchJob: function (id) {


      $.get("/print-article/jobFinished/' + id", function (data) {

        if (!data) {

        }
        console.log(data);
      });
    }
  };

  /**
   * Command to close a dialog.
   *
   * If no selector is given, it defaults to trying to close the modal.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {object} response
   *   Object holding the server response.
   * @param {string} response.selector
   *   The selector of the dialog.
   * @param {bool} response.persist
   *   Whether to persist the dialog element or not.
   * @param {number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.initQueueWatcher = function (ajax, response, status) {

    var prevNowPlaying = setInterval(function (jobId) {
      console.log(jobId);

      $.get("/print-article/jobFinished/" + jobId, function (data) {

        if (!parseInt(data)) {
          clearInterval(prevNowPlaying);
          $('#preview-wrapper')[0].src = $('#preview-wrapper')[0].src + '?' +  + new Date().getTime();
        }
      });
    }, 2000, response.job_id);

  };


})(jQuery, Drupal);
