/**
 * @file
 * Placeholder file for custom sub-theme behaviors.
 *
 */
(function($, Drupal) {

  /**
   * Use this behavior as a template for custom Javascript.
   */
  $(document).ready(function() {
    function changeState() {
      if (window.location.hash === '#live') {
        $('div.bs-init').addClass('no-style');
        $('a.toggle-chrome').addClass('success');
         setTimeout(function(){
          window.location.reload(1);
         }, 30000);
        $('a.toggle-chrome').click(function(event) {
          event.preventDefault();
          history.pushState('', document.title, window.location.pathname);
          changeState();
          reattachEvents();
        });
      } else {
        $('div.bs-init').removeClass('no-style');
        $('a.toggle-chrome').removeClass('success');

      };
    }
    $(window).bind('hashchange', function() {
      changeState();
    });

    changeState();

    function reattachEvents() {
      $('a.toggle-chrome').unbind('click').click(function() {
        return true;
      });
    }

  });

  $(document).ready(function() {
    resizeDiv();
  });

  window.onresize = function(event) {
    resizeDiv();
  }



  function resizeDiv() {
    vpw = $(window).width();
    vph = $(window).height();
    toolbarSize = $('.toolbar-bar').height() + $('.toolbar-tray').height();
    vphToolbarOn = (vph - toolbarSize);
    $('#bigscreen-wrapper').css({ 'height': vphToolbarOn + 'px', 'width': vpw + 'px' });
  }


  $(document).ready(function() {
    $('.block-region-right section div').addClass('large-up-3 medium-up-3 row');
    console.log($('block-region-right section div'));
    $('.block-region-right section div img').each(
        function(){
          $(this).wrap('<div class="columns each-img">')
        })
  });

})(jQuery, Drupal);
