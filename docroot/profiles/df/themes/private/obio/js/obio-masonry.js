/**
 * @file
 * Obio Masonry.
 */

(function($, Drupal) {
  "use strict";

  /**
   * Masonry helper for tile views.
   */
  Drupal.behaviors.obioMasonry = {
    attach: function (context) {
      $(context).find('.js-obio-masonry').once('obioMasonry').each(function () {
        var $viewContent = $(this).find('.view-content');
        $viewContent.addClass('obio-masonry-loading');
        $viewContent.prepend('<div class="grid-sizer"></div><div class="gutter-sizer"></div>').once();

        // Indicate that images are loading.
        $viewContent.append('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
        $viewContent.imagesLoaded(function () {
          $viewContent.masonry({
            columnWidth: '.grid-sizer',
            // do not use .grid-sizer in layout
            itemSelector: '.l-card-elem',
            percentPosition: true
          });
          // Add a class to reveal the loaded images, which avoids FOUC.
          $viewContent.children().addClass('obio-masonry-done');
          $viewContent.find('.ajax-progress').remove();
          $viewContent.removeClass('obio-masonry-loading');
        });
      });
    }
  };

})(jQuery, Drupal);
