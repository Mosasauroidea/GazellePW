'use strict'

/* Prototypes */
if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g, '')
  }
}

var listener = {
  set: function (el, type, callback) {
    if (document.addEventListener) {
      el.addEventListener(type, callback, false)
    } else {
      // IE hack courtesy of http://blog.stchur.com/2006/10/12/fixing-ies-attachevent-failures
      var f = function () {
        callback.call(el)
      }
      el.attachEvent('on' + type, f)
    }
  },
}

/* Site wide functions */

// http://www.thefutureoftheweb.com/blog/adddomloadevent
// retrieved 2010-08-12
var addDOMLoadEvent = (function () {
  var e = [],
    t,
    s,
    n,
    i,
    o,
    d = document,
    w = window,
    r = 'readyState',
    c = 'onreadystatechange',
    x = function () {
      n = 1
      clearInterval(t)
      while ((i = e.shift())) {
        i()
      }
      if (s) {
        s[c] = ''
      }
    }
  return function (f) {
    if (n) {
      return f()
    }
    if (!e[0]) {
      d.addEventListener && d.addEventListener('DOMContentLoaded', x, false)
      /*@cc_on@*/ /*@if(@_win32)d.write("<script id=__ie_onload defer src=//0><\/scr"+"ipt>");s=d.getElementById("__ie_onload");s[c]=function(){s[r]=="complete"&&x()};/*@end@*/
      if (/WebKit/i.test(navigator.userAgent))
        t = setInterval(function () {
          ;/loaded|complete/.test(d[r]) && x()
        }, 10)
      o = w.onload
      w.onload = function () {
        x()
        o && o()
      }
    }
    e.push(f)
  }
})()

//PHP ports
function isset(variable) {
  return typeof variable === 'undefined' ? false : true
}

function is_array(input) {
  return typeof input === 'object' && input instanceof Array
}

function function_exists(function_name) {
  return typeof this.window[function_name] === 'function'
}

function html_entity_decode(str) {
  var el = document.createElement('div')
  el.innerHTML = str
  for (var i = 0, ret = ''; i < el.childNodes.length; i++) {
    ret += el.childNodes[i].nodeValue
  }
  return ret
}

function get_size(size) {
  var steps = 0
  while (size >= 1024) {
    steps++
    size = size / 1024
  }
  var ext
  switch (steps) {
    case 1:
      ext = ' B'
      break
    case 1:
      ext = ' KiB'
      break
    case 2:
      ext = ' MiB'
      break
    case 3:
      ext = ' GiB'
      break
    case 4:
      ext = ' TiB'
      break
    case 5:
      ext = ' PiB'
      break
    case 6:
      ext = ' EiB'
      break
    case 7:
      ext = ' ZiB'
      break
    case 8:
      ext = ' EiB'
      break
    default:
      '0.00 MiB'
  }
  return size.toFixed(2) + ext
}

function get_ratio_color(ratio) {
  if (ratio < 0.1) {
    return 'u-colorRatio00'
  }
  if (ratio < 0.2) {
    return 'u-colorRatio01'
  }
  if (ratio < 0.3) {
    return 'u-colorRatio02'
  }
  if (ratio < 0.4) {
    return 'u-colorRatio03'
  }
  if (ratio < 0.5) {
    return 'u-colorRatio04'
  }
  if (ratio < 0.6) {
    return 'u-colorRatio05'
  }
  if (ratio < 0.7) {
    return 'u-colorRatio06'
  }
  if (ratio < 0.8) {
    return 'u-colorRatio07'
  }
  if (ratio < 0.9) {
    return 'u-colorRatio08'
  }
  if (ratio < 1) {
    return 'u-colorRatio09'
  }
  if (ratio < 2) {
    return 'u-colorRatio10'
  }
  if (ratio < 5) {
    return 'u-colorRatio20'
  }
  return 'u-colorRatio50'
}

function ratio(dividend, divisor, color) {
  if (!color) {
    color = true
  }
  if (divisor == 0 && dividend == 0) {
    return '--'
  } else if (divisor == 0) {
    return '<span class="u-colorRatio99">∞</span>'
  } else if (dividend == 0 && divisor > 0) {
    return '<span class="u-colorRatio00">-∞</span>'
  }
  var rat = (dividend / divisor - 0.005).toFixed(2) //Subtract .005 to floor to 2 decimals
  if (color) {
    var col = get_ratio_color(rat)
    if (col) {
      rat = '<span class="' + col + '">' + rat + '</span>'
    }
  }
  return rat
}

//returns key if true, and false if false. better than the PHP funciton
function in_array(needle, haystack, strict) {
  if (strict === undefined) {
    strict = false
  }
  for (var key in haystack) {
    if ((haystack[key] == needle && strict === false) || haystack[key] === needle) {
      return true
    }
  }
  return false
}

function array_search(needle, haystack, strict) {
  if (strict === undefined) {
    strict = false
  }
  for (var key in haystack) {
    if ((strict === false && haystack[key] == needle) || haystack[key] === needle) {
      return key
    }
  }
  return false
}

var util = function (selector, context) {
  return new util.fn.init(selector, context)
}

function gazURL() {
  var path = window.location.pathname.split('/')
  var path = path[path.length - 1].split('.')[0]
  var splitted = window.location.search.substr(1).split('&')
  var query = {}
  var length = 0
  for (var i = 0; i < splitted.length; i++) {
    var q = splitted[i].split('=')
    if (q != '') {
      query[q[0]] = q[1]
      length++
    }
  }
  query['length'] = length
  var response = new Array()
  response['path'] = path
  response['query'] = query
  return response
}

function isNumberKey(e) {
  var charCode = e.which ? e.which : e.keyCode
  if (charCode == 46) {
    return true
  }
  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
    return false
  }
  return true
}

function sleep(milliseconds) {
  var start = new Date().getTime()
  for (var i = 0; i < 1e7; i++) {
    if (new Date().getTime() - start > milliseconds) {
      break
    }
  }
}

function slidetips(Id, interval, to, step) {
  var obj = document.getElementById(Id)
  if (obj == null) return
  if (to == undefined) {
    if (obj._slideStart == true) return
    if (obj._expand == true) {
      to = 0
      obj.style.overflow = 'hidden'
    } else {
      obj.style.height = ''
      obj.style.overflow = ''
      obj.style.display = 'block'
      to = obj.offsetHeight
      obj.style.overflow = 'hidden'
      obj.style.height = '1px'
    }
    obj._slideStart = true
  }
  var H,
    step = step ? Math.abs(step) : obj.offsetHeight > 500 || to > 500 ? 50 : 30 // 30 -> dynamic
  interval = interval == undefined ? 10 : interval // 1 -> 10
  step = (to > 0 ? 1 : -1) * step
  obj.style.height = (H = (H = isNaN((H = parseInt(obj.style.height))) ? 0 : H) + step < 0 ? 0 : H + step) + 'px'
  if (H <= 0) {
    obj.style.display = 'none'
    obj.style.overflow = 'hidden'
    obj._expand = false
    obj._slideStart = false
  } else if (to > 0 && H >= to) {
    // H = to; // fix wrong height v1
    obj.style.display = 'block'
    obj.style.overflow = 'visible'
    // obj.style.height = H + "px";
    obj.style.height = null // remove fixed height
    obj._expand = true
    obj._slideStart = false
  } else {
    setTimeout("slidetips('" + Id + "' , " + interval + ', ' + to + ', ' + step + ');', interval)
  }
}

$.fn.extend({
  results: function () {
    return this.size()
  },
  gshow: function () {
    return this.remove_class('hidden')
  },
  ghide: function (force) {
    return this.add_class('hidden', force)
  },
  gtoggle: function (force) {
    //Should we interate and invert all entries, or just go by the first?
    if (!in_array('hidden', this[0].className.split(' '))) {
      this.add_class('hidden', force)
    } else {
      this.remove_class('hidden')
    }
    return this
  },
  new_toggle: function () {
    if (this.attr('id')) {
      this.height(this.outerHeight())
      slidetips(this.attr('id'))
    } else {
      if (this.has_class('hidden')) {
        this.css('display', 'none').remove_class('hidden')
      }

      this.slideToggle(this.height())
      // this.animate({height:'toggle'}, this.height);
    }

    return this
  },
  listen: function (event, callback) {
    for (var i = 0, il = this.size(); i < il; i++) {
      var object = this[i]
      if (document.addEventListener) {
        object.addEventListener(event, callback, false)
      } else {
        object.attachEvent('on' + event, callback)
      }
    }
    return this
  },
  add_class: function (class_name, force) {
    for (var i = 0, il = this.size(); i < il; i++) {
      var object = this[i]
      if (object.className === '') {
        object.className = class_name
      } else if (force || !in_array(class_name, object.className.split(' '))) {
        object.className = object.className + ' ' + class_name
      }
    }
    return this
  },
  remove_class: function (class_name) {
    for (var i = 0, il = this.size(); i < il; i++) {
      var object = this[i]
      var classes = object.className.split(' ')
      var result = array_search(class_name, classes)
      if (result !== false) {
        classes.splice(result, 1)
        object.className = classes.join(' ')
      }
    }
    return this
  },
  has_class: function (class_name) {
    for (var i = 0, il = this.size(); i < il; i++) {
      var object = this[i]
      var classes = object.className.split(' ')
      if (array_search(class_name, classes)) {
        return true
      }
    }
    return false
  },
  toggle_class: function (class_name) {
    for (var i = 0, il = this.size(); i < il; i++) {
      var object = this[i]
      var classes = object.className.split(' ')
      var result = array_search(class_name, classes)
      if (result !== false) {
        classes.splice(result, 1)
        object.className = classes.join(' ')
      } else {
        if (object.className === '') {
          object.className = class_name
        } else {
          object.className = object.className + ' ' + class_name
        }
      }
    }
    return this
  },
  disable: function () {
    $(this).prop('disabled', true)
    return this
  },
  enable: function () {
    $(this).prop('disabled', false)
    return this
  },
  raw: function (number) {
    if (typeof number == 'undefined') {
      number = 0
    }
    return $(this).get(number)
  },
  nextElementSibling: function () {
    var here = this[0]
    if (here.nextElementSibling) {
      return $(here.nextElementSibling)
    }
    do {
      here = here.nextSibling
    } while (here.nodeType != 1)
    return $(here)
  },
  previousElementSibling: function () {
    var here = this[0]
    if (here.previousElementSibling) {
      return $(here.previousElementSibling)
    }
    do {
      here = here.nextSibling
    } while (here.nodeType != 1)
    return $(here)
  },

  // Disable unset form elements to allow search URLs cleanups
  disableUnset: function () {
    $('input, select', this)
      .filter(function () {
        return $(this).val() === ''
      })
      .disable()
    return this
  },

  // Prevent double submission of forms
  preventDoubleSubmission: function () {
    $(this).submit(function (e) {
      var $form = $(this)
      if ($form.data('submitted') === true) {
        // Previously submitted - don't submit again
        e.preventDefault()
      } else {
        // Mark it so that the next submit can be ignored
        $form.data('submitted', true)
      }
    })
    // Keep chainability
    return this
  },
})
