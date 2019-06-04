/**
 * @file
 * Data picker instances
 */
(function($, Drupal) {
  "use strict";
  $('#edit-field-booking-date-0-value-date').pickadate({
    formatSubmit: 'yyyy-mm-dd',
    format: 'dddd, dd mmm, yyyy',
    hiddenName: true,
    autoclose: true
  })
  $('#edit-field-booking-date-0-value-time').pickatime({
    autoclose: true,
    twelvehour: false,
    closeOnSelect: true,
    interval: 120,
    formatSubmit: 'H:i',
    formatLabel: 'h:i a',
    format: 'h:i a',
    min: [8,0],
  	max: [18,0]
  });
})(jQuery, Drupal);
