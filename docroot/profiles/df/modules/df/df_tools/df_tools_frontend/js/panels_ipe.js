/**
 * @file panels_ipe.js
 */
(function ($, Drupal, Backbone) {

  "use strict";

  /**
   * Reacts to IPE states to better direct demo flow.
   */
  Backbone.on('PanelsIPEInitialized', function () {
    Drupal.panels_ipe.app.get('layout').on('sync', function () {
      $('body').after('<div class="ajax-progress ajax-progress-fullscreen">&nbsp;</div>');
      $(window).unbind('beforeunload');
      location.reload();
    }, 'df_tools_frontend');

    Drupal.panels_ipe.app.get('tabCollection').on('change', function (tab) {
      if (tab.get('active')) {
        var $offcanvas = $('#drupal-offcanvas');
        if ($offcanvas.length && $offcanvas.dialog) {
          $offcanvas.dialog('close');
        }
      }
    }, 'df_tools_frontend');

    $(window).bind('beforeunload', function (e) {
      if (Drupal.panels_ipe.app.get('unsaved') && !Drupal.panels_ipe.app.get('cancelTab').get('loading')) {
        return Drupal.t('You have unchanged changes, are you sure you want to leave?');
      }
    });
  });

}(jQuery, Drupal, Backbone));
