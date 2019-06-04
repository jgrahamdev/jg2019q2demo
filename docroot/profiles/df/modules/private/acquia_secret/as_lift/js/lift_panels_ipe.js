/**
 * @file lift_panels_ipe.js
 */
(function ($, Drupal, Backbone) {

  "use strict";

  /**
   * Re-loads the Lift sidebar after Panels IPE saves.
   */
  Backbone.on('PanelsIPEInitialized', function() {
    Drupal.panels_ipe.app.get('layout').on('sync', function () {
      // @todo Figure out a way to implement this.
      console.log('Refreshing Lift after IPE save...');
    }, 'acquia_lift');
  });

}(jQuery, Drupal, Backbone));
