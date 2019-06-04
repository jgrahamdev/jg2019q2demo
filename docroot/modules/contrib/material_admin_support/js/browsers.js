/**
 * @file
 * Adds a helper class to visually select entities.
 *
 * Should be functional for any view with a checkbox field.
 *
 */

(function ($, Drupal) {

  Drupal.behaviors.materialAdminSupportBrowsers = {
    attach: function (context, settings) {
      $('.views-row').once('bind-click-event').each(function () {
        var input = $(this).find('input[type="checkbox"]');
        if (input.prop('checked')) {
          $(this).addClass('checked');
        }

        $(this).click(function (e) {
          e.preventDefault();
          input.prop('checked', !input.prop('checked'));
          if (input.prop('checked')) {
            $(this).addClass('checked');
            $(this).find('input[type="checkbox"]').change();
          }
          else {
            $(this).removeClass('checked');
            $(this).find('input[type="checkbox"]').change();

          }
        });
      });

      var $view = $('.media-library-view');
      if ($view.length && $view.find('.browser--item').length) {
        var $checkbox = $('<input type="checkbox" class="form-checkbox" />').on('click', function (ref) {
          var currentTarget = ref.currentTarget;

          var $checkboxes = $(currentTarget).closest('.media-library-view').find('.browser--item input[type="checkbox"]');
          $checkboxes.prop('checked', $(currentTarget).prop('checked')).trigger('change');

        });
        var $label = $('<label class="media-library-select-all btn"></label>').text(Drupal.t('Select all media'));
        $label.prepend($checkbox);
        $view.find('.browser--item').first().before($label);
        $('.media-library-select-all').wrap('<div class="media-library-select-all-wrapper"></div>');

        $('.media-library-select-all').on('click', function(e) {
          var input = $(this).find('input[type="checkbox"]');
          if (input.prop('checked')) {
            $('.views-row').addClass('checked');
          }
          else {
            $('.views-row').removeClass('checked');
          }
        });
      }
    }
  }

})(jQuery, Drupal);
