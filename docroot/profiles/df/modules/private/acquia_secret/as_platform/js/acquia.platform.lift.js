/**
 * @file acquia.platform.lift.js
 */
(function ($, Drupal) {

  "use strict";

  /**
   * Opens the Acquia Lift client when the associated Toolbar icon is clicked.
   */
  Drupal.behaviors.ASPlatformLiftIcon = {
    attach: function (context) {
      // Hide the "Use Lift" icon if Lift isn't ready or the sidebar is
      // already loaded.
      var $icon = $('.toolbar-icon-lift-client', context);
      $icon.hide();
      setInterval(function () {
        if ($('.lift-tools:visible').length) {
          $icon.hide();
        }
        else if (window.AcquiaLiftPublicApi) {
          $icon.show();
        }
      }, 1000);

      // On click of the icon, activate Lift.
      $icon.on('click', function (e) {
        e.preventDefault();
        window.AcquiaLiftPublicApi.activate();
      });
    }
  };

}(jQuery, Drupal));
