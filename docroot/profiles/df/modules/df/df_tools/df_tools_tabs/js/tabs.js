/**
 * @file tabs.js
 */
(function ($, Drupal) {

  "use strict";

  function handleOverflow () {
    $('.df-tools-tabs-wrapper').each(function () {
      var baseTop;
      var $wrapper = $(this);
      if ($wrapper.find('.field-wrapper').data('ui-accordion')) {
        $wrapper.find('.field-wrapper').accordion('destroy');
        $wrapper.removeClass('is-overflown');
      }
      $wrapper.tabs();
      $wrapper.find('.ui-tabs-nav li').each(function () {
        var top = $(this).position()['top'];
        baseTop = baseTop || top;
        if (top !== baseTop) {
          $wrapper.tabs('destroy');
          $wrapper.find('.field-wrapper').accordion({
            header: '.df-tools-tabs-accordion-title',
            collapsible: true
          });
          $wrapper.addClass('is-overflown');
          return false;
        }
      });
    });
  }

  /**
   * Enables jQuery tabs for our field.
   */
  Drupal.behaviors.DFToolsTabs = {
    attach: function (context) {
      handleOverflow();
    }
  };

  $(document).ready(function () {
    $(window).resize(function () {
      handleOverflow();
    });
  });

})(jQuery, Drupal);
