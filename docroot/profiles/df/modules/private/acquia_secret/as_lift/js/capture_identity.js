/**
 * @file capture_identity.js
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   * Captures the identity of the current user.
   */
  Drupal.behaviors.ASLiftCaptureIdentity = {
    attach: function (context) {
      // Check that the setting exists.
      if (drupalSettings.as_lift_user && drupalSettings.as_lift_user.email) {
        if ($.cookie('tc_ptid') && !$.cookie('as_lift_captured')) {
          if (typeof _tcaq !== 'undefined' ) {
            _tcaq.push(['captureIdentity', drupalSettings.as_lift_user.email, 'email']);
            $.cookie('as_lift_captured', true, { expires: 1 });
            console.log($.cookie('tc_ptid'));
          }
        }
      }
    }
  };

}(jQuery, Drupal, drupalSettings));
