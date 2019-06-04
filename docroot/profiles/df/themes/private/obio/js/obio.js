/**
 * @file
 * Obio sub-theme behaviors.
 */
(function($, Drupal, window) {

  "use strict";

  /**
   * Initializes foundation's JavaScript for new content added to the page.
   */
  Drupal.behaviors.foundationInit = {
    attach: function (context, settings) {
      $(context).foundation();
    }
  };

  /**
   * Adds/removes a class when the top-bar menu is overflowing.
   */
  Drupal.behaviors.obioMenuOverflow = {
    attach: function (context, settings) {
      var onresize = function () {
        var $menu = $('.header-left ');
        var expectedMenuHeight = 120;
        $menu.closest('.header-wrap').removeClass('overflow');
        if ($menu.height() > expectedMenuHeight) {
          $menu.closest('.header-wrap').addClass('overflow');
        }
        else {
          $menu.closest('.header-wrap').removeClass('overflow');
        }
      };
      onresize();
      $(window).once('obio-menu-resize').resize(onresize);
    }
  };

  // Display a custom modal for Obio.
  Drupal.behaviors.obioModal = {
    attach: function(context, settings) {
      if (settings.obio && settings.obio.modal) {
        var output =
          '<div id="obio-modal" data-reveal data-animation-in="fade-in" data-animation-out="fade-out" class="obio-modal-message reveal">' +
          '  <div>' +
          '    <i class="icon ion-ios-checkmark-outline fa-4x"></i>' +
          '    <h2>' + Drupal.t('Thank You') + '</h2>' +
          '    <p>' + Drupal.t(settings.obio.modal) + '</p>' +
          '    <button class="close-button" aria-label="Close reveal" type="button" data-close><span aria-hidden="true">&times;</span></button>' +
          '  </div>' +
          '</div>';
        $('html').append(output);
        $('#obio-modal').foundation().foundation('open');
        delete settings.obio.modal;
      }
    }
  };

  function joyrideDisplace () {
    setTimeout(function () {
      var $joyride = $('.joyride-tip-guide:visible');
      if ($joyride.length) {
        var position = $joyride.position();
        var left = position['left'];
        var width = $joyride.width();
        var combined_left = left + width;
        if (combined_left > document.body.clientWidth) {
          $joyride.css('left', left - (combined_left - document.body.clientWidth));
        }
        if (position['top'] < 79 && $('body.toolbar-tray-open').length) {
          $joyride.css('top', 79);
        }
        else if (position['top'] == 79 && !$('body.toolbar-tray-open').length && $('body.toolbar-fixed').length) {
          $joyride.css('top', 39);
        }
      }
      $joyride.find('.button').on('click', function () {
        joyrideDisplace();
      });
    }, 1);
  }

  $(document).on('drupalTourStarted', joyrideDisplace);
  $(window).on('resize', joyrideDisplace);
  $('#toolbar-bar').on('click', joyrideDisplace);


  // Adds accordion classes for selected items.
  Drupal.behaviors.accordionClass = {
    attach: function (context, settings) {

      if ($('.accordion .is-active')[0]) { 
        $('.is-active').each(function() {
          var section = $(this).parent().parent().parent();
          var sib = section.siblings('.accordion-title');
          section.parent().addClass("is-active");
          sib.attr('aria-expanded', 'true');
          sib.attr('aria-selected', 'true');
          var item = $(this).parent().parent().parent('.accordion-content');
          item.css('display', 'block');
        });
      } else {
        $( ".accordion .block--facet-block--tags a.accordion-title" ).click();
      }
    }
  };

  // Move Facet Reset All.
  Drupal.behaviors.facetMoveReset = {
    attach: function(context, settings) {
      //Set Width for reset-ul
      function setFacetResetWidth () {
        var resetUl = $('.block-facets-summary ul li.facet-summary-item--clear').parent();
        resetUl.addClass('reset-ul');
        var resetBlockWidth = $('.block-facets-summary').innerWidth();
        var resetLabelWidth = $('.block-facets-summary .reset-link-label').outerWidth(true);
        var resetUlWidth = (resetBlockWidth - resetLabelWidth - 40) + 'px';
        $('.reset-ul').css("width", resetUlWidth);

        //Set to position to relative so it doesn't cover other items on smaller screens
        var viewportwidth = $(window).width();
        if (viewportwidth < 1023) {
          $('.facet-summary-item--clear').css("position", "relative");
        } else {
          $('.facet-summary-item--clear').css("position", "absolute");
        }
      };
      setFacetResetWidth();

      //Reset Width for reset-ul on resize
      $(window).resize(setFacetResetWidth);

      //Move reset to last li      
      $('.facet-summary-item--clear').insertAfter(".reset-ul li:last-child"); 
    }
  };

 // Make Inspiration Article Search auto submit.
  Drupal.behaviors.obioInspirationSubmit = {
    attach: function(context, settings) {
      if ($('#dfs-obio-inspiration-article-search-form .js-form-type-entity-autocomplete')[0]) {
        $('#ui-id-1.ui-autocomplete').click(function() {
          $('#dfs-obio-inspiration-article-search-form').submit();
        });
      }
    }
  }; 

})(jQuery, Drupal, window);
