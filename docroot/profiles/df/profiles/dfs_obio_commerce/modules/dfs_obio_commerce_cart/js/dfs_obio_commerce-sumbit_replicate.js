/**
 * @file
 * DFS Obio submit button replicate.
 */

(function($, Drupal) {
  'use strict';

  // Based on the data-replicate attriute, this behavior mimics the
  // functionality of any kind of element by triggering click event on the
  // (target) element that has this attribute IF the source element exists.
  //
  // Source element is the html tag which selector was specified in the content
  // of the "data-replicate" attribute.s
  //
  // If the source not exist, it restores the visibility of the target element.
  //
  // This feature is used on the add-to-cart form.
  //
  // Check dfs_obio_commerce module's form alter and the product template inside
  // the Obio theme.
  Drupal.behaviors.dfsObioCommerceSubmitReplicate = {
    attach: function (context) {
      $(context).find('[data-replicate]').once('dfsObioCommerceSubmitReplicate').each(function () {
        var $trigger_target = $(this);
        var trigger_source_selector = $(this).attr('data-replicate');
        var $trigger_source = $(trigger_source_selector);

        if ($trigger_source.length) {
          $trigger_source.bind('click', function (event) {
            $trigger_target.trigger('click');

            event.preventDefault();
          }).removeClass('visually-hidden');
        }
        else {
          $trigger_target.removeClass('visually-hidden');
        }
      });
    }
  };

})(jQuery, Drupal);
