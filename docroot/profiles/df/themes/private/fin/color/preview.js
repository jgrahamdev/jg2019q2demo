/**
 * @file
 * Preview for the FIN theme.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.color = {
    logoChanged: false,
    callback: function (context, settings, $form) {
      // Change the logo to be the real one.
      if (!this.logoChanged) {
        $('.color-preview .color-preview-logo img').attr('src', drupalSettings.color.logo);
        this.logoChanged = true;
      }
      // Remove the logo if the setting is toggled off.
      if (drupalSettings.color.logo === null) {
        $('div').remove('.color-preview-logo');
      }

      var $colorPreview = $form.find('.color-preview');
      var $colorPalette = $form.find('.js-color-palette');

      // Solid background.
      $colorPreview.css('backgroundColor', $colorPalette.find('input[name="palette[bg]"]').val());

      // Meta nav 
      $colorPreview.find('.color-preview-metanav').css('background-color', $colorPalette.find('input[name="palette[metanav]"]').val());

      //nav links
      $colorPreview.find('.color-preview-main-menu-links li a').css('color', $colorPalette.find('input[name="palette[headline]"]').val());

      // Text preview.
      $colorPreview.find('.color-preview-main h2, .color-preview .preview-content').css('color', $colorPalette.find('input[name="palette[headline]"]').val());
      $colorPreview.find('.color-preview-content a').css('color', $colorPalette.find('input[name="palette[primary]"]').val());
      $colorPreview.find('.color-preview-content a:hover').css('color', $colorPalette.find('input[name="palette[primaryhover]"]').val());

      //dropdown
      $colorPreview.find('.color-preview-content .dropdown').css('background-color', $colorPalette.find('input[name="palette[dropdownbg]"]').val());
      $colorPreview.find('.color-preview-content .dropdown li a').css('color', $colorPalette.find('input[name="palette[text]"]').val());

      // Sidebar block.
      var $colorPreviewBlock = $colorPreview.find('.color-preview-sidebar .color-preview-block');
      var $colorPreviewButton = $colorPreview.find('.color-preview-sidebar .color-preview-block .button2');
      var $colorPreviewButtonHollow = $colorPreview.find('.color-preview-sidebar .color-preview-block .button.hollow');
      $colorPreviewBlock.css('background-color', $colorPalette.find('input[name="palette[sidebar]"]').val());
      $colorPreviewBlock.css('border-color', $colorPalette.find('input[name="palette[sidebarborders]"]').val());
      $colorPreviewButtonHollow.css('border-color', $colorPalette.find('input[name="palette[primary]"]').val());
      $colorPreviewButtonHollow.css('color', $colorPalette.find('input[name="palette[primary]"]').val());
      $colorPreviewButton.css('background-color', $colorPalette.find('input[name="palette[primary]"]').val());
      $colorPreviewButton.css('color', $colorPalette.find('input[name="palette[text]"]').val());

      // Footer wrapper background.
      $colorPreview.find('.color-preview-footer-wrapper').css('background-color', $colorPalette.find('input[name="palette[footer]"]').val());
      $colorPreview.find('.color-preview-footer-wrapper h2, .color-preview-footer-wrapper a, .color-preview-footer-wrapper ').css('color', $colorPalette.find('input[name="palette[text]"]').val());

    }
  };
})(jQuery, Drupal, drupalSettings);
