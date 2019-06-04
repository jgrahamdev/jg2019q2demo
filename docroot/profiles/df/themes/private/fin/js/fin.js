/**
 * @file
 * fin sub-theme behaviors.
 *
 */
(function($, Drupal) {

  /**
   * fin demo theme custom javascript.
   */

  /**
   * Initializes foundation's JavaScript for new content added to the page.
   */
  Drupal.behaviors.foundationInit = {
    attach: function (context, settings) {
      $(context).foundation();
    }
  };

  //navbar with mobile menu look on desktop
  function SetMenuState() {
    var menuIcon = $('.main-menu-icon');
    var menuBox = $('.top-bar-right ul');
    //var menuWrap = $('.top-bar-right');

    menuBox.toggleClass('main-menu-open');
    menuIcon.toggleClass('hover');
    if (menuBox.hasClass('main-menu-open')) {
      localStorage.setItem('lastState', 'on');
    } else {
      localStorage.setItem('lastState', 'off');
    }
  };

  // Ignore our logic surrounding the menu icon based on the drupal settings.
  if (drupalSettings.fin && !drupalSettings.fin.desktop_mobile_menu_icon) {
    $('.top-bar-right ul').addClass('main-menu-open');
    $('.main-menu-icon').hide();
  } else {
    $('.main-menu-icon').click(function() {
      SetMenuState();
    });

    $(document).ready(function() {
      var checkStatus = localStorage.getItem('lastState') || 'false';
      //turn it on by default
      SetMenuState();
      //turn if off, if localstorage tells you to
      if (checkStatus == 'off') {
        SetMenuState();
      }
    });
  }

  Drupal.behaviors.absoluteNav = {
    attach: function(context, settings) {
      var hero = $('.hero .field-name-field-hero-image').closest('.hero');
      if (hero.index() == 0 && hero.parent().index() == 0) {
        $('#top-bar-sticky-container').addClass('absoluteNav');
      } else {
        $('#top-bar-sticky-container').removeClass('absoluteNav');
      }
    }
  };

  Drupal.behaviors.searchBox = {
    attach: function(context, settings) {
      $('.meta-wrapper #views-exposed-form-dfs-fin-search-block-1 .form-submit').once().after('<i class="searchbox-icon meta-icon-size icon ion-ios-search"></i>');
      $('.meta-wrapper .block-searchform #views-exposed-form-dfs-fin-search-block-1', context).addClass('searchbox');
      $('.meta-wrapper .block-searchform .form-text').attr('placeholder', 'Search...   ');
      var submitIcon = $('.searchbox-icon');
      var inputBox = $('.block-searchform .form-text');
      var searchBox = $('.searchbox');
      var isOpen = false;
      submitIcon.click(function() {
        if (isOpen == false) {
          searchBox.addClass('searchbox-open');
          submitIcon.addClass('hover');
          inputBox.focus();
          isOpen = true;
        } else {
          searchBox.removeClass('searchbox-open');
          submitIcon.removeClass('hover');
          inputBox.focusout();
          isOpen = false;
        }
      });
      submitIcon.mouseup(function() {
        return false;
      });
      searchBox.mouseup(function() {
        return false;
      });
      $(document).mouseup(function() {
        if (isOpen == true) {
          $('.searchbox-icon').css('display', 'block');
          submitIcon.click();
        }
      });
    }
  };

  // Display a custom modal for FIN.
  Drupal.behaviors.finModal = {
    attach: function(context, settings) {
      if (settings.fin && settings.fin.modal) {
        var output =
          '<div id="fin-modal" data-reveal data-animation-in="fade-in" data-animation-out="fade-out" class="fin-modal-message reveal">' +
          '  <div>' +
          '    <i class="icon ion-ios-checkmark-outline fa-4x"></i>' +
          '    <h2>' + Drupal.t('Thank You') + '</h2>' +
          '    <p>' + Drupal.checkPlain(settings.fin.modal) + '</p>' +
          '    <button class="close-button" aria-label="Close reveal" type="button" data-close><span aria-hidden="true">&times;</span></button>' +
          '  </div>' +
          '</div>';
        $('html').append(output);
        $('#fin-modal').foundation().foundation('open');
        delete settings.fin.modal;
      }
    }
  }

  Drupal.behaviors.last_menu_as_cta = {
    attach: function(context, settings) {
      if (drupalSettings.fin && drupalSettings.fin.last_menu_as_cta) {
        $('.topbar-toplevel > li:last-child a').addClass('button alert small');
      } else {
        return false;
      }
    }
  };

  /**
   * Handles empty results in the view.
   */
  Drupal.behaviors.DFSFINLocationEmptyResults = {
    attach: function (context) {
      if ($('.view-id-location_finder .leaflet-tile-pane').length) {
        $('.dfs-fin-location-dialog').remove();
        if (!$('.view-id-location_finder .leaflet-marker-icon').length) {
          var message = Drupal.t('No agents were found in your area.<br />To connect with an agent online, <a href="/contact/request_more_info">click here.</a>');
          var $elem = $('<div class="dfs-fin-location-dialog">' + message + '</div>');
          $elem.dialog({
            position: {
              my: 'center',
              at: 'center',
              of: '.view-id-location_finder .leaflet-container'
            }
          }).parent().resizable({
            containment: '.view-id-location_finder .leaflet-container'
          }).draggable({
            containment: '.view-id-location_finder .leaflet-container',
            opacity: 0.70
          });
        }
      }
    }
  };

  /**
   * Renders the view twice on load to prevent visual errors.
   */
  Drupal.behaviors.DFSFINLocationReloadFix = {
    attach: function (context) {
      $('body').once('dfs-fin-location-autosubmit').each(function() {
        $('.view-id-location_finder').find('input[type="submit"]').on('click', function () {
          $('body').addClass('locationSelected');
        });
      });
    }
  };

  //fullback for full width rows. To break out of the row width, it requires two divs. I forsee people not putting the second div in. so there is this.
  // $(document).ready(function() {
  //     fullWidthInner();

  //     function fullWidthInner() {
  //         $("#main .full-width-row:not(:has(.full-width-inner))").wrapInner('<div class="full-width-inner"></div>');
  //     }
  // });

})(jQuery, Drupal);
