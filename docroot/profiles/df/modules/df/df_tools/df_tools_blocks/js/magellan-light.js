/**
 * @file
 * Magellan Light plugin.
 *
 * Replaces Foundation's Magellan.
 *
 * Options:
 *  An object with keys:
 *  - magellans: selectors to watch on.
 *  - offset: offset in magellan should be active.
 *  - offsetCallback: callback for offset.
 *
 * Usage: $('body').magellanize(options = {magellans: '.element a[href*="#"]'});
 */

(function ($) {
  'use strict';

  function MagellanLight(options) {
    this.options = $.extend({}, {
      magellans: 'a[href^="#"]',
      offset: 1,
      offsetCallback: function() {return this.offset }
    }, options);
    this.targetSelector = this.options.magellans;
    this.activeTarget = null;
    this.scrollObj = $(window)
      .on('scroll.magellanlight', $.proxy(this.process, this));
    this.recalculateDimensions();
    this.process();
  }

  MagellanLight.prototype.getScrollHeight = function () {
    return this.scrollObj[0].document.body.clientHeight;
  };

  MagellanLight.prototype.recalculateDimensions = function () {
    var _self = this;
    // Reset everything.
    this.offsets = [];
    this.targets = [];
    this.scrollHeight = this.getScrollHeight();

    $(document.body).find(this.targetSelector).each(function () {
        var href = $(this).attr('href').replace(window.location.origin + window.location.pathname, '');
        var $href = /^#./.test(href) && $(href);

        if ($href && $href.length && $href.is(':visible')) {
          _self.offsets.push($href.offset().top); // Offset.
          _self.targets.push(href); // Href.
        }
      });
  }

  MagellanLight.prototype.process = function () {
    var scrollObjTop = this.scrollObj.scrollTop() + this.options.offsetCallback();
    var scrollHeight = this.getScrollHeight();
    var maxScroll = this.options.offsetCallback() + scrollHeight - this.scrollObj.height();
    var i;

    if (this.scrollHeight !== scrollHeight) {
      // Dimensions changed, recalculate.
      this.recalculateDimensions();
    }

    if (this.activeTarget && (scrollObjTop < this.offsets[0])) {
      // Above the first target.
      this.activeTarget = null;
      return this.reset();
    }

    for (i = this.offsets.length; i--;) {
      if (this.activeTarget !== this.targets[i]
        && scrollObjTop >= this.offsets[i]
        && (this.offsets[i + 1] === undefined || scrollObjTop < this.offsets[i + 1])) {
        this.activate(this.targets[i]);
      }
    }
  };

  MagellanLight.prototype.activate = function (target) {
    this.activeTarget = target;
    this.reset();
    $(this.targetSelector + '[href="' + target + '"]')
      .addClass('active')
      .trigger('magellanlight.activated');
  };

  MagellanLight.prototype.reset = function () {
    $(this.targetSelector + '.active')
      .removeClass('active')
      .trigger('magellanlight.deactivated');
  };

  //
  // jQuery plugin export.
  //
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data = $this.data('magellanlight');
      var options = typeof option === 'object' && option.magellans ? option : {};
      $this.data('magellanlight', (data = new MagellanLight(options)));
    })
  }

  $.fn.magellanize = Plugin;
  $.fn.magellanize.Constructor = MagellanLight;

}(jQuery));
