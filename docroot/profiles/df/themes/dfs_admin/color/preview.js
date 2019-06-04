/**
 * @file
 * Color preview enhancements for the Demo Framework Admin theme.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.color = {
    callback: function (context, settings, $form) {

      var $colorPalette = $form.find('.js-color-palette');

      $('.color-preview header.header-wrapper').css('background-color', $colorPalette.find('input[name="palette[headerbg]"]').val());

      $('.color-preview header.header-wrapper h1.page-title').css('color', $colorPalette.find('input[name="palette[pagetitle]"]').val());

      $('.color-preview ul.tabs a').css('color', $colorPalette.find('input[name="palette[tabs]"]').val());

      $('.color-preview ul.tabs a.active').css('color', $colorPalette.find('input[name="palette[tabsactive]"]').val());

      $('.color-preview ul.tabs .indicator').css('background-color', $colorPalette.find('input[name="palette[tabsactive]"]').val());

      $('.color-preview .breadcrumb-section-wrapper').css('background-color', $colorPalette.find('input[name="palette[breadcrumbbg]"]').val());

      $('.color-preview .breadcrumb-nav ol li a, .color-preview .breadcrumb-nav ol li .material-icons').css('color', $colorPalette.find('input[name="palette[breadcrumb]"]').val());

      $('.color-preview .breadcrumb-nav ol li span').css('color', $colorPalette.find('input[name="palette[breadcrumbtitle]"]').val());

      $('.color-preview .body').css('background-color', $colorPalette.find('input[name="palette[base]"]').val());

      $('.color-preview .btn').css('background-color', $colorPalette.find('input[name="palette[button]"]').val());

      $('.color-preview .btn').css('color', $colorPalette.find('input[name="palette[buttontext]"]').val());

      $('.color-preview .btn').hover(function(){
          $(this).css('color', $colorPalette.find('input[name="palette[buttonhovertext]"]').val());
      }, function(){
          $(this).css('color', $colorPalette.find('input[name="palette[buttontext]"]').val());
      });

      $('.color-preview .btn').hover(function(){
          $(this).css('background-color', $colorPalette.find('input[name="palette[buttonhover]"]').val());
      }, function(){
          $(this).css('background-color', $colorPalette.find('input[name="palette[button]"]').val());
      });

      $('.color-preview .button-action').css('background-color', $colorPalette.find('input[name="palette[buttonaction]"]').val());

      $('.color-preview .button-action').each(function () {
        this.style.setProperty( 'color', $colorPalette.find('input[name="palette[buttonactiontext]"]').val(), 'important' );
      });

      $('.color-preview .button-action').hover(function(){
          this.style.setProperty( 'color', $colorPalette.find('input[name="palette[buttonactionhovertext]"]').val(), 'important' );
      }, function(){
          this.style.setProperty( 'color', $colorPalette.find('input[name="palette[buttonactiontext]"]').val(), 'important' );
      });

      $('.color-preview .button-action').hover(function(){
          $(this).css('background-color', $colorPalette.find('input[name="palette[buttonactionhover]"]').val());
      }, function(){
          $(this).css('background-color', $colorPalette.find('input[name="palette[buttonaction]"]').val());
      });



    }
  };
})(jQuery, Drupal, drupalSettings);
