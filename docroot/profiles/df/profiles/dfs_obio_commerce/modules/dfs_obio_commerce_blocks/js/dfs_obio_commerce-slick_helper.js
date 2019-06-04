/**
 * @file
 * Slick helper
 *
 * Re-sets slick instance's "respondTo" property to make it respont to window
 * resize. This issue is fixed in later releases (e.g in 1.8.0), but df uses
 * 1.7.1 right now.
 */

(function($, Drupal) {
  'use strict';

  Drupal.behaviors.dfsObioCommerceSlickHelper = {
    attach: function (context) {
      var $slicks = $(context).find('.slick__slider').once('dfsObioCommerceSlickHelper');

      $slicks.each(function () {
        if ($(this).prop('slick')) {
          setTimeout(function($slickObject) {
            var respondTo = $slickObject.slick('slickGetOption', 'respondTo');
            $slickObject.slick('slickSetOption', 'respondTo', respondTo, true);
          }, 1000, $(this));
        }
      });
    }
  };

})(jQuery, Drupal);
