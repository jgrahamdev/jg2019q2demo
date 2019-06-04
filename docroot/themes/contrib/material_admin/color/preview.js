/**
 * @file
 * Color preview enhancements for the Material Admin theme.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.color = {
    callback: function (context, settings, $form) {

      var $colorPalette = $form.find('.js-color-palette');

      $('.color-preview header.header-wrapper').css('background-color', $colorPalette.find('input[name="palette[headerbg]"]').val());

      $('.color-preview .breadcrumb-section-wrapper').css('background-color', $colorPalette.find('input[name="palette[breadcrumbbg]"]').val());

      $('.color-preview .breadcrumb-nav ol li a, .color-preview .breadcrumb-nav ol li .material-icons').css('color', $colorPalette.find('input[name="palette[breadcrumb]"]').val());

    }
  };
})(jQuery, Drupal, drupalSettings);
