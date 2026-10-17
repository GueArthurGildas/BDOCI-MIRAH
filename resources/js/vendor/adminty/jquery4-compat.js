/**
 * jQuery 4.0 Compatibility Polyfills
 *
 * Restores APIs removed in jQuery 4.0 so that legacy jQuery plugins
 * (inputmask, isotope, sparkline, postitall, etc.) continue to work.
 *
 * This file MUST be loaded immediately after jquery.min.js and before
 * any other scripts. It is auto-appended by build/patch-jquery4.js.
 */
(function($) {
  'use strict';

  if (!$) return;

  var version = $.fn.jquery.split('.');
  if (parseInt(version[0], 10) < 4) return; // Only needed for jQuery 4+

  // --- Static utility methods ---

  if (!$.isFunction) {
    $.isFunction = function(obj) {
      return typeof obj === 'function';
    };
  }

  if (!$.type) {
    var class2type = {};
    'Boolean Number String Function Array Date RegExp Object Error Symbol'
      .split(' ')
      .forEach(function(name) {
        class2type['[object ' + name + ']'] = name.toLowerCase();
      });

    $.type = function(obj) {
      if (obj == null) return obj + '';
      return typeof obj === 'object' || typeof obj === 'function'
        ? class2type[Object.prototype.toString.call(obj)] || 'object'
        : typeof obj;
    };
  }

  if (!$.isArray) {
    $.isArray = Array.isArray;
  }

  if (!$.isNumeric) {
    $.isNumeric = function(obj) {
      var type = typeof obj;
      return (type === 'number' || type === 'string') &&
        !isNaN(obj - parseFloat(obj));
    };
  }

  if (!$.isWindow) {
    $.isWindow = function(obj) {
      return obj != null && obj === obj.window;
    };
  }

  if (!$.camelCase) {
    $.camelCase = function(string) {
      return string.replace(/-([\da-z])/gi, function(all, letter) {
        return letter.toUpperCase();
      });
    };
  }

  if (!$.proxy) {
    $.proxy = function(fn, context) {
      var tmp, args;
      if (typeof context === 'string') {
        tmp = fn[context];
        context = fn;
        fn = tmp;
      }
      if (typeof fn !== 'function') return undefined;
      args = Array.prototype.slice.call(arguments, 2);
      var proxy = function() {
        return fn.apply(
          context || this,
          args.concat(Array.prototype.slice.call(arguments))
        );
      };
      return proxy;
    };
  }

  if (!$.now) {
    $.now = Date.now;
  }

  if (!$.trim) {
    $.trim = function(text) {
      return text == null ? '' : (text + '').trim();
    };
  }

  // --- Instance methods ---

  if (!$.fn.bind) {
    $.fn.bind = function() {
      return this.on.apply(this, arguments);
    };
  }

  if (!$.fn.unbind) {
    $.fn.unbind = function() {
      return this.off.apply(this, arguments);
    };
  }

  if (!$.fn.delegate) {
    $.fn.delegate = function(selector, types, data, fn) {
      return this.on(types, selector, data, fn);
    };
  }

  if (!$.fn.undelegate) {
    $.fn.undelegate = function(selector, types, fn) {
      return arguments.length === 1
        ? this.off(selector, '**')
        : this.off(types, selector || '**', fn);
    };
  }

  if (!$.fn.andSelf) {
    $.fn.andSelf = $.fn.addBack;
  }

  if (!$.fn.size) {
    $.fn.size = function() {
      return this.length;
    };
  }

  // --- Event shorthand methods ---
  // jQuery 4 removed .click(fn), .change(fn), etc. Restore them.

  var eventShorthands = (
    'blur focus focusin focusout resize scroll click dblclick ' +
    'mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave ' +
    'change select submit keydown keypress keyup contextmenu'
  ).split(' ');

  eventShorthands.forEach(function(name) {
    if (!$.fn[name]) {
      $.fn[name] = function(data, fn) {
        return arguments.length > 0
          ? this.on(name, null, data, fn)
          : this.trigger(name);
      };
    }
  });

  if (!$.fn.hover) {
    $.fn.hover = function(fnOver, fnOut) {
      return this.on('mouseenter', fnOver).on('mouseleave', fnOut || fnOver);
    };
  }

  // --- Pseudo-selectors removed in jQuery 4 ---
  // :first, :last, :eq(), :even, :odd, :gt(), :lt()

  if ($.expr && $.expr.pseudos) {
    if (!$.expr.pseudos.first) {
      $.expr.pseudos.first = function(elem, i) { return i === 0; };
    }
    if (!$.expr.pseudos.last) {
      $.expr.pseudos.last = function(elem, i, match, array) { return i === array.length - 1; };
    }
    if (!$.expr.pseudos.eq) {
      $.expr.pseudos.eq = function(elem, i, match) {
        var idx = parseInt(match[3], 10);
        return idx >= 0 ? i === idx : i === (i + idx + 1);
      };
    }
    if (!$.expr.pseudos.even) {
      $.expr.pseudos.even = function(elem, i) { return i % 2 === 0; };
    }
    if (!$.expr.pseudos.odd) {
      $.expr.pseudos.odd = function(elem, i) { return i % 2 === 1; };
    }
    if (!$.expr.pseudos.lt) {
      $.expr.pseudos.lt = function(elem, i, match) {
        return i < parseInt(match[3], 10);
      };
    }
    if (!$.expr.pseudos.gt) {
      $.expr.pseudos.gt = function(elem, i, match) {
        return i > parseInt(match[3], 10);
      };
    }
  }

})(window.jQuery);
