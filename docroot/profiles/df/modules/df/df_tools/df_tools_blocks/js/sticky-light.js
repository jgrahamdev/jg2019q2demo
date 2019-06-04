/**
 * @file
 * Sticky Light plugin.
 *
 * Replaces Foundation's Sticky.
 *
 * Options:
 *  An object with keys:
 *  - defaultClass: default CSS class to add on init.
 *  - stuckedClass: class to toggle on stick.
 *  - offset: offset from top (static string).
 *  - offsetMethod: top for manipulating 'top' property, 'margin-top' for
 *    margin-top etc.
 *  - placeholderClass: CSS class to use for placeholder.
 *  - stickTo: jQuery object or CSS selector to stick to.
 *  - offsetCallback: callback for offset.
 *  - pauseCallback: pause callback, return true if sticky should pause.
 *
 * Usage: $(element).stick(options = {offset: 10px, stickto: 'body'});
 */

(function ($) {
  'use strict';

  function StickyLight(element, options) {
    this.options = $.extend({}, {
      offset: 0,
      offsetMethod: 'top',
      offsetCallback: function() {return this.offset },
      stickTo: 'body',
      defaultClass: 'sticky-light',
      stuckedClass: 'is-stucked',
      placeholderClass: 'sticky-light__placeholder'
    }, options);
    this.elementObj = element.data('sticked', false).addClass(this.options.defaultClass);
    this.stickToObj = this.options.stickTo instanceof jQuery && this.options.stickTo.length ?
      this.options.stickTo : null;
    if (!this.stickToObj && (typeof this.options.stickTo === 'string') && $(this.options.stickTo).length) {
      this.stickToObj = $(this.options.stickTo);
    }
    if (!this.stickToObj) {
      return;
    }
    this.paused = typeof this.options.pauseCallback === 'function' ? this.options.pauseCallback : function() {return false};
    this.offset = 0;
    this.offsetCallback = this.options.offsetCallback;
    this.scrollHeight = 0;
    this.placeholderObj = this.elementObj.closest('.' + this.options.placeholderClass);
    this.scrollObj = $(window)
      .on('scroll.stickylight', $.proxy(this.process, this));
    this.reset();
    this.process();
  }

  StickyLight.prototype.getScrollHeight = function () {
    return this.scrollObj[0].document.body.clientHeight;
  };

  StickyLight.prototype.recalculateDimensions = function () {
    this.offset = this.offsetCallback();
    this.scrollHeight = this.getScrollHeight();
    this.stickPoint = this.stickToObj.offset().top;
    this.placeholderObj.attr('style', 'height: ' + this.elementObj.outerHeight() + 'px;')
  }

  StickyLight.prototype.reset = function () {
    var originalOffset = this.offset;
    this.recalculateDimensions();

    // Massage placeholder.
    if (!(this.placeholderObj) || this.placeholderObj.length === 0) {
      this.elementObj.wrap($('<div/>', {
        class: this.options.placeholderClass
      }));
      this.placeholderObj = this.elementObj.closest('.' + this.options.placeholderClass);
    }

    this.placeholderObj.attr('style', 'height: ' + this.elementObj.outerHeight() + 'px;');

    if (this.paused()) {
      this.unstick(true);
    }
    else {
      if ((this.stickPoint - this.offset) <= this.scrollObj[0].pageYOffset) {
        this.stick(originalOffset !== this.offset);
      }
      else {
        this.unstick();
      }
    }
  }

  StickyLight.prototype.process = function () {
    if (this.paused()) {
      this.unstick(true);
    }
    else {
      if (this.scrollHeight !== this.getScrollHeight()) {
        // Dimensions changed, recalculate.
        this.recalculateDimensions();
      }

      if ((this.stickPoint - this.offset) <= this.scrollObj[0].pageYOffset) {
        this.stick();
      }
      else {
        this.unstick();
      }
    }
  };

  StickyLight.prototype.stick = function (forced = false) {
    this.elementObj.filter(function() {
        return $(this).data('sticked') === forced;
      })
      .addClass(this.options.stuckedClass)
      .attr('style', this.options.offsetMethod + ': ' + this.offset + 'px')
      .data('sticked', true)
      .trigger('stickylight.stuck');
  };

  StickyLight.prototype.unstick = function (forced = false) {
    this.elementObj.filter(function() {
        return $(this).data('sticked') === !forced;
      })
      .removeClass(this.options.stuckedClass)
      .attr('style', '')
      .data('sticked', false)
      .trigger('stickylight.unstuck');
  };

  //
  // jQuery plugin export.
  //
  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data = $this.data('stickylight');
      var options = typeof option === 'object' ? option : {};
      $this.data('stickylight', (data = new StickyLight($this, options)));
    });
  }

  $.fn.stick = Plugin;
  $.fn.stick.Constructor = StickyLight;

}(jQuery));
