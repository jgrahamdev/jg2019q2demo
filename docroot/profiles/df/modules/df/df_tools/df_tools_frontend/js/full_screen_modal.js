(function ($, Drupal) {

  $(document).ready(function(){
    $(window).on({
      'dialog:aftercreate': function (event, dialog, $modal, settings) {
        var $child = $modal.find('iframe');
        var fWidth = window.innerWidth-20;
        var fHeight = window.innerHeight-20;
        if ($child.length > 0) {
           var modalSrc = $child.attr('src');
           var splits = modalSrc.split('/');
          if (splits[1] == 'entity-browser') {
            // Make the modal full width.
            $modal.dialog({
              resizable: false,
              modal: true,
              closeOnEscape: true,
              draggable:false,
              height: fHeight,
              width: fWidth,
              position: {
              my: 'center center',
              at: 'center center',
              of: window,
              collision: 'none'
              }
            });
              $modal.parent().addClass('ui-dialog-full-width').css('z-index','10001');
              $child.css('height', $modal.innerHeight());
          }
        }
      }
    });
  });
})(jQuery, Drupal);
