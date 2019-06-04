(function ($, Drupal) {
  Drupal.behaviors.as_journey_lift = {
    attach: function (context, settings) {
      $('#dfs-obio-subscribe-form').submit(function() {
        var value = $('#edit-email').val();
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (_tcaq != undefined && value != undefined && re.test(value)) {
          _tcaq.push(['captureIdentity', value, 'email'], ['capture', 'signed_up_to_newsletter']);
        }
      })
    }
  }
})(jQuery, Drupal)
