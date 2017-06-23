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

    var $throbber = $('<div class="ajax-progress ajax-progress-throbber"></div>');
    $throbber.append('<div class="throbber">&nbsp;</div>');
    $throbber.append($('<div class="message"></div>').text(Drupal.t('Waiting for preview image update.')));

    var $previewImg = $(response.selector);
    $previewImg.after($throbber);

    var $watcher = setInterval(function (jobId, selector) {

      $.ajax('/print-article/quick-preview/' + jobId, {
        type: 'GET',
        statusCode: {
          200: function (data) {
            clearInterval($watcher);
            $previewImg[0].src = data;
            $throbber.remove();
          }
        }
      });

    }, 2000, response.job_id);

  };

})(jQuery, Drupal);
