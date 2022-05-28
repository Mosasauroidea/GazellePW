/**
 * Check or uncheck checkboxes in formElem
 * If masterElem is false, toggle each box, otherwise use masterElem's status on all boxes
 * If elemSelector is false, act on all checkboxes in formElem
 */
function toggleChecks(formElem, masterElem, elemSelector) {
  elemSelector = elemSelector || 'input:checkbox'
  if (masterElem) {
    $('#' + formElem + ' ' + elemSelector).prop('checked', masterElem.checked)
  } else {
    $('#' + formElem + ' ' + elemSelector).each(function () {
      this.checked = !this.checked
    })
  }
}

/* Still some issues
function caps_check(e) {
	if (e === undefined) {
		e = window.event;
	}
	if (e.which === undefined) {
		e.which = e.keyCode;
	}
	if (e.which > 47 && e.which < 58) {
		return;
	}
	if ((e.which > 64 && e.which < 91 && !e.shiftKey) || (e.which > 96 && e.which < 123 && e.shiftKey)) {
		$('#capslock').gshow();
	}
}
*/

function hexify(str) {
  str = str.replace(/rgb\(|\)/g, '').split(',')
  str[0] = parseInt(str[0], 10).toString(16).toLowerCase()
  str[1] = parseInt(str[1], 10).toString(16).toLowerCase()
  str[2] = parseInt(str[2], 10).toString(16).toLowerCase()
  str[0] = str[0].length == 1 ? '0' + str[0] : str[0]
  str[1] = str[1].length == 1 ? '0' + str[1] : str[1]
  str[2] = str[2].length == 1 ? '0' + str[2] : str[2]
  return str.join('')
}

function resize(id) {
  var textarea = document.getElementById(id)
  if (textarea.scrollHeight > textarea.clientHeight) {
    //textarea.style.overflowY = 'hidden';
    textarea.style.height =
      Math.min(1000, textarea.scrollHeight + textarea.style.fontSize) + 'px'
  }
}

//ZIP downloader stuff
function add_selection() {
  var selected = $('#formats').raw().options[$('#formats').raw().selectedIndex]
  if (selected.disabled === false) {
    var listitem = document.createElement('li')
    listitem.id = 'list' + selected.value
    listitem.innerHTML =
      '						<input type="hidden" name="list[]" value="' +
      selected.value +
      '" /> ' +
      '						<span style="float: left;">' +
      selected.innerHTML +
      '</span>' +
      '						<a href="#" onclick="remove_selection(\'' +
      selected.value +
      '\'); return false;" style="float: right;" class="brackets">X</a>' +
      '						<br style="clear: all;" />'
    $('#list').raw().appendChild(listitem)
    $('#opt' + selected.value).raw().disabled = true
  }
}

function remove_selection(index) {
  $('#list' + index).remove()
  $('#opt' + index).raw().disabled = ''
}

// Thank you http://stackoverflow.com/questions/4578398/selecting-all-text-within-a-div-on-a-single-left-click-with-javascript
function select_all(el) {
  if (
    typeof window.getSelection != 'undefined' &&
    typeof document.createRange != 'undefined'
  ) {
    var range = document.createRange()
    range.selectNodeContents(el)
    var sel = window.getSelection()
    sel.removeAllRanges()
    sel.addRange(range)
  } else if (
    typeof document.selection != 'undefined' &&
    typeof document.body.createTextRange != 'undefined'
  ) {
    var textRange = document.body.createTextRange()
    textRange.moveToElementText(el)
    textRange.select()
  }
}

function toggle_display(selector) {
  let element = document.getElementById(selector)
  if (!element) {
    element = document.getElementsByClassName(selector)
  }
  if (element.style.display === 'none' || element.style.display === '') {
    element.style.display = 'block'
  } else {
    element.style.display = 'none'
  }
}
function change_lang(lang) {
  if (cookie.get('lang') != null) {
    cookie.del('lang')
  }
  cookie.set('lang', lang)
  location.reload(true)
}
function displayImg(url) {
  var img = document.getElementById('dynamicImg')
  img.innerHTML = '<img src="' + url + '">'
  var x = event.clientX + document.body.scrollLeft + 20
  var y = event.clientY + document.body.scrollTop - 5
  img.style.left = x + 'px'
  img.style.top = y + 'px'
  img.style.display = 'block'
}
function vanishImg() {
  var img = document.getElementById('dynamicImg')
  img.style.display = 'none'
}
function toggleShow(objID) {
  var obj = document.getElementById(objID)
  if (obj.style.display == '') {
    obj.style.display = 'none'
  } else {
    obj.style.display = ''
  }
}
