/**
 * @file
 * Cart submit button behavior.
 */

(function($, Drupal) {
  'use strict';

  Drupal.behaviors.dfsObioCommerceCartSubmitButton = {
    attach: function (context) {
      var $cartSubmitButtons = $(context).find('.js-obio-checkout-button').once('user-info-from-browser');
      var $cartFormFirst = $('.cart-form').find('form').first();
      if ($cartSubmitButtons.length && $cartFormFirst.find('[data-drupal-selector="edit-checkout"]').length) {
        $cartSubmitButtons.bind('click', function (event) {
          event.preventDefault();
          $cartFormFirst.find('[data-drupal-selector="edit-checkout"]').trigger('click');
        });
      }
      else {
        $cartSubmitButtons.remove();
      }
    }
  };

})(jQuery, Drupal);
