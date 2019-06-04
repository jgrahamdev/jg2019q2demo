/**
 * @file
 * magellan.js
 */

(function ($, Drupal, window) {
  "use strict";

  /**
   * Dynamically fills the "Magellan" block with valid links.
   */
  Drupal.behaviors.DFToolsBlocksMagellan = {
    attach: function (context) {
      $(context).find('.df-tools-magellan-block').once('magellan').each(function () {
        var _self = Drupal.behaviors.DFToolsBlocksMagellan;
        var $magellanSticky = $(this).find('[data-sticky-light]');
        if (typeof jQuery.fn.stick === 'function') {
          $magellanSticky.once('magellanStickyLight').stick({
            stickTo: $(this).closest('.df-tools-magellan-block'),
            pauseCallback: function () {
              if ($magellanSticky && $magellanSticky.length && $magellanSticky.closest('.panels-ipe-active').length) {
                return true;
              }
              return false;
            },
            offsetCallback: function() {
              var heightToolbar = $('body').hasClass('toolbar-fixed') && $('.toolbar-bar:visible').length ?
                $('.toolbar-bar:visible').outerHeight() : 0;
              var heightToolbarTray = $('body').hasClass('toolbar-fixed') && $('body').hasClass('toolbar-horizontal') && $('.toolbar-tray.is-active:visible').length ? $('.toolbar-tray.is-active:visible').outerHeight() : 0;
              return (heightToolbar + heightToolbarTray);
            }
          }).on('offset:reset', function () {
            $(this).data('stickylight').reset();
          });
        }

        var $scrollParent = $('body');
        var $magellan = $(this).find('[data-magellan-light]').uniqueId();
        var $blocks_after = $(this).closest('.block-plugin-id-magellan').nextAll();
        var $menu = $magellan.find('.menu');

        $menu.empty();
        $blocks_after.each(function () {
          $(this).find('.block-title').uniqueId().each(function () {
            var anchorId = $(this).attr('id') + '-mgl';
            var anchorText = $(this).text();
            var $anchorPoint = _self.createAnchorPoint(anchorId);
            var $anchor = _self.createAnchorElement('#' + anchorId, anchorText);
            $anchorPoint.insertBefore($(this));
            $menu.append($('<li></li>').append($anchor));
          });
        });

        // Prepare parent element and fire handler.
        $scrollParent.once('magellanLight').css('position', 'relative').magellanize({
          magellans: '#' + $magellan.attr('id') + ' a[href^="#"]',
          offsetCallback: function () {
            var heightToolbar = $('body').hasClass('toolbar-fixed') && $('.toolbar-bar:visible').length ?
              $('.toolbar-bar:visible').outerHeight() : 0;
            var heightToolbarTray = $('body').hasClass('toolbar-fixed') && $('body').hasClass('toolbar-horizontal') && $('.toolbar-tray.is-active:visible').length ? $('.toolbar-tray.is-active:visible').outerHeight() : 0;
            var stickyHeight = $('[data-sticky-light]:visible').length ?
              $('[data-sticky-light]:visible').outerHeight() : 0;

            return heightToolbar + heightToolbarTray + stickyHeight;
          }
        });

        // Binding dimension helper functions.
        $blocks_after.find('.js-magellan-light-anchor').each(function () {
          $(this).trigger('offset:reset');
        });
        $(document).once('magellanLightViewPort').on('drupalViewportOffsetChange drupalToolbarOrientationChange drupalToolbarTrayChange drupalToolbarTrabChange', function () {
          $('.js-magellan-light-anchor').trigger('offset:reset');
          if ($magellanSticky) {
            $magellanSticky.trigger('offset:reset');
          }
        });
      });
    },
    createAnchorPoint: function(anchorId) {
      return $('<div/>', {
        class: 'clearfix js-magellan-light-anchor',
        id: anchorId,
        on: {
          'offset:reset': function (event) {
            var heightToolbar = $('body').hasClass('toolbar-fixed') && $('.toolbar-bar:visible').length ?
              $('.toolbar-bar:visible').outerHeight() : 0;
            var heightToolbarTray = $('body').hasClass('toolbar-fixed') && $('body').hasClass('toolbar-horizontal') && $('.toolbar-tray.is-active:visible').length ? $('.toolbar-tray.is-active:visible').outerHeight() : 0;
            var stickyHeight = $('[data-sticky-light]:visible').length ?
              $('[data-sticky-light]:visible').outerHeight() : 0;

            var offSetTop = heightToolbar + heightToolbarTray + stickyHeight;
            $(this).attr('style', 'position: relative; top: -' + offSetTop + 'px;');
          }
        }
      });
    },
    createAnchorElement: function (target, text) {
      return $('<a/>', {
        href: target,
        text: text,
        on: {
          click: function (event) {
            event.preventDefault();
            var $target = $(event.target.hash);
            if ($target.length) {
              var targetPos = $target.offset().top;
              var docScrollPos = $('html, body').scrollTop();
              var rawSpeed = Math.abs(docScrollPos - targetPos) / 3;
              var minSpeed = rawSpeed < 300 ? 300: rawSpeed;
              var calculatedSpeed = rawSpeed > 1000 ? 1000 : minSpeed;

              $('html, body').stop().animate({
                scrollTop: targetPos
              }, calculatedSpeed, function() {
                var $target = $(event.target.hash);
                $target.focus();
                if ($target.not(':focus')) {
                  $target
                  .attr('tabindex', '-1')
                  .focus();
                };
              });
            }
          }
        }
      });
    }
  };

}(jQuery, Drupal, window));
