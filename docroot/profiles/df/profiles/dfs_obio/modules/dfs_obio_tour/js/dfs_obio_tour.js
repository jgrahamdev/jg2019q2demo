/**
 * @file
 * Javascript behaviors for dfs_obio_tour.
 */

(function($, drupalSettings) {

  "use strict";

  if (drupalSettings.dfs_obio_tour && typeof inline_manual_player === 'undefined') {
    var settings = drupalSettings.dfs_obio_tour;
    window.inlineManualOptions = {
      language: 'en',
      variables: { 'drupal_uid': settings.uid }
    };
    if (settings.lift_enabled) {
      var interval = setInterval(function () {
        if (window.AcquiaLift && window.AcquiaLift.currentSegments) {
          var segments = [];
          window.AcquiaLift.currentSegments.forEach(function (segment) {
            segments.push(segment.id);
          });

          window.inlineManualTracking = {
            uid: window.AcquiaLift.liftWebIdentity.tc_ptid,
            email: settings.email,
            username: settings.username,
            group: segments.join(',')
          };

          createInlineManualPlayer(window.inlineManualPlayerData);

          clearInterval(interval);
        }
      }, 100);
    }
    else {
      $('body').on('DOMNodeInserted', function () {
        $('.inmplayer-list-item-tour:contains("Personalization")').hide();
      });
      createInlineManualPlayer(window.inlineManualPlayerData);
    }
  }

})(jQuery, drupalSettings);
