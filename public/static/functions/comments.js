var username
var postid
var url = new gazURL()

function QuoteJump(event, post) {
  var button = event.button
  var url, pattern
  if (isNaN(post.charAt(0))) {
    switch (post.charAt(0)) {
      case 'a': // artist comment
        url = 'artist'
        break
      case 't': // torrent comment
        url = 'torrents'
        break
      case 'c': // collage comment
        url = 'collages'
        break
      case 'r': // request comment
        url = 'requests'
        break
      default:
        return
    }
    pattern = new RegExp(url + '.php')
    post = post.substr(1)
    url = 'comments.php?action=jump&postid=' + post
  } else {
    // forum post
    url = 'forums.php?action=viewthread&postid=' + post
    pattern = /forums\.php/
  }
  var hash = '#post' + post
  if (button == 0) {
    if ($(hash).raw() != null && location.href.match(pattern)) {
      window.location.hash = hash
    } else {
      window.open(url, '_self')
    }
  } else if (button == 1) {
    window.open(url, '_window')
  }
}

function Quote(post, user) {
  Quote(post, user, false)
}

var original_post
function Quote(post, user, link) {
  username = user
  postid = post

  var target = ''
  var requrl = ''
  if (url.path == 'inbox') {
    requrl = 'inbox.php?action=get_post&post=' + post
  } else {
    requrl = 'comments.php?action=get&postid=' + post
  }
  if (link == true) {
    if (url.path == 'artist') {
      // artist comment
      target = 'a'
    } else if (url.path == 'torrents') {
      // torrent comment
      target = 't'
    } else if (url.path == 'collages') {
      // collage comment
      target = 'c'
    } else if (url.path == 'requests') {
      // request comment
      target = 'r'
    } else {
      // forum post
      requrl = 'forums.php?action=get_post&post=' + post
    }
    target += post
  }

  // if any text inside of a forum post body is selected, use that instead of Ajax result.
  // unfortunately, this will not preserve bbcode in the quote. This is an unfortunate necessity, as
  // doing some sort of weird grepping through the Ajax bbcode for the selected text is overkill.
  if (getSelection().toString() && inPost(getSelection().anchorNode) && inPost(getSelection().focusNode)) {
    insertQuote(getSelection().toString())
  } else {
    ajax.get(requrl, insertQuote)
  }

  // DOM element (non-jQuery) -> Bool
  function inPost(elt) {
    return $.contains($('#post' + postid)[0], elt)
  }
  // Str -> undefined
  function insertQuote(response) {
    if ($('#quickpost').raw().value !== '') {
      $('#quickpost').raw().value += '\n\n'
    }
    $('#quickpost').raw().value =
      $('#quickpost').raw().value +
      '[quote=' +
      username +
      (link == true ? '|' + target : '') +
      ']' +
      //response.replace(/(img|aud)(\]|=)/ig,'url$2').replace(/\[url\=(https?:\/\/[^\s\[\]<>"\'()]+?)\]\[url\](.+?)\[\/url\]\[\/url\]/gi, "[url]$1[/url]")
      html_entity_decode(response) +
      '[/quote]'
    resize('quickpost')
  }
}

globalapp.editForm = function Edit_Form(post) {
  postid = post
  var postuserid, pmbox

  // iff no edit is already going underway or a previous edit was finished, make the necessary dom changes.
  if (!$('#editbox' + postid).results() || $('#editbox' + postid + '.hidden').results()) {
    $('#reply_box').ghide()
    postuserid = $('#post' + postid + ' strong a')
      .attr('href')
      .split('=')[1]
    if (postuserid != userid) {
      pmbox = t('client.common.pm_user_on_edit', { postId: postid })
    } else {
      pmbox = ''
    }

    $('#bar' + postid).raw().oldbar = $('#bar' + postid).raw().innerHTML
    $('#content' + post + ' .TableForumPostBody-text').ghide()
    $('#content' + post + ' .TableForumPostBody-edit').gshow()
    $('#bar' + postid).raw().innerHTML =
      '<a href="#" type="button" value="Post" onclick="Save_Edit(' +
      postid +
      '); return false;">' +
      t('client.common.save') +
      '</a> - <a href="#" type="button" value="Cancel" onclick="Cancel_Edit(' +
      postid +
      '); return false;">' +
      t('client.common.cancel') +
      '</a>'
  }
  /* If it's the initial edit, fetch the post content to be edited.
   * If editing is already underway and edit is pressed again, reset the post
   * (keeps current functionality, move into brackets to stop from happening).
   */
  if (location.href.match(/forums\.php/)) {
    ajax.get('?action=get_post&post=' + postid, function (response) {
      $('#edit_content_' + postid).val(html_entity_decode(response))
      resize('edit_content_' + postid)
    })
  } else {
    ajax.get('comments.php?action=get&postid=' + postid, function (response) {
      $('#edit_content_' + postid).val(html_entity_decode(response))
      resize('edit_content_' + postid)
    })
  }
}

function Cancel_Edit(postid) {
  var answer = confirm(t('client.common.are_you_sure_you_want_to_cancel'))
  if (answer) {
    $('#reply_box').gshow()
    $('#bar' + postid).raw().innerHTML = $('#bar' + postid).raw().oldbar
    $('#content' + postid + ' .TableForumPostBody-text').gshow()
    $('#content' + postid + ' .TableForumPostBody-edit').ghide()
  }
}

function Save_Edit(postid) {
  $('#reply_box').gshow()
  if (location.href.match(/forums\.php/)) {
    ajax.post('forums.php?action=takeedit', 'edit_form_' + postid, function (response) {
      $('#bar' + postid).raw().innerHTML = $('#bar' + postid).raw().oldbar
      $('#pmbox' + postid).ghide()
      $('#content' + postid + ' .TableForumPostBody-text').html(response)
      $('#content' + postid + ' .TableForumPostBody-text').gshow()
      $('#content' + postid + ' .TableForumPostBody-edit').ghide()
    })
  } else {
    ajax.post('comments.php?action=take_edit', 'edit_form_' + postid, function (response) {
      $('#bar' + postid).raw().innerHTML = $('#bar' + postid).raw().oldbar
      $('#pmbox' + postid).ghide()
      $('#content' + postid + ' .TableForumPostBody-text').html(response)
      $('#content' + postid + ' .TableForumPostBody-text').gshow()
      $('#content' + postid + ' .TableForumPostBody-edit').ghide()
    })
  }
}

function Delete(post) {
  postid = post
  if (confirm(t('client.common.are_you_sure_you_wish_to_delete_this_post')) == true) {
    if (location.href.match(/forums\.php/)) {
      ajax.get('forums.php?action=delete&auth=' + authkey + '&postid=' + postid, function () {
        $('#post' + postid).ghide()
      })
    } else {
      ajax.get('comments.php?action=take_delete&auth=' + authkey + '&postid=' + postid, function () {
        $('#post' + postid).ghide()
      })
    }
  }
}

function Quick_Preview() {
  var quickreplybuttons
  $('#post_preview').raw().value = 'Make changes'
  $('#post_preview').raw().preview = true
  ajax.post('ajax.php?action=preview', 'quickpostform', function (response) {
    $('#quickreplypreview').gshow()
    $('#contentpreview').raw().innerHTML = response
    $('#quickreplytext').ghide()
  })
}

function Quick_Edit() {
  var quickreplybuttons
  $('#post_preview').raw().value = 'Preview'
  $('#post_preview').raw().preview = false
  $('#quickreplypreview').ghide()
  $('#quickreplytext').gshow()
}

function Newthread_Preview(mode) {
  $('#newthreadpreviewbutton').gtoggle()
  $('#newthreadeditbutton').gtoggle()
  if (mode) {
    // Preview
    ajax.post('ajax.php?action=preview', 'newthreadform', function (response) {
      $('#contentpreview').raw().innerHTML = response
    })
    $('#newthreadtitle').raw().innerHTML = $('#title').raw().value
    var pollanswers = $('#answer_block').raw()
    if (pollanswers && pollanswers.children.length > 4) {
      pollanswers = pollanswers.children
      $('#pollquestion').raw().innerHTML = $('#pollquestionfield').raw().value
      for (var i = 0; i < pollanswers.length; i += 2) {
        if (!pollanswers[i].value) {
          continue
        }
        var el = document.createElement('input')
        el.id = 'answer_' + (i + 1)
        el.type = 'radio'
        el.name = 'vote'
        $('#pollanswers').raw().appendChild(el)
        $('#pollanswers').raw().appendChild(document.createTextNode(' '))
        el = document.createElement('label')
        el.htmlFor = 'answer_' + (i + 1)
        el.innerHTML = pollanswers[i].value
        $('#pollanswers').raw().appendChild(el)
        $('#pollanswers').raw().appendChild(document.createElement('br'))
      }
      if ($('#pollanswers').raw().children.length > 4) {
        $('#pollpreview').gshow()
      }
    }
  } else {
    // Back to editor
    $('#pollpreview').ghide()
    $('#newthreadtitle').raw().innerHTML = 'New Topic'
    var pollanswers = $('#pollanswers').raw()
    if (pollanswers) {
      var el = document.createElement('div')
      el.id = 'pollanswers'
      pollanswers.parentNode.replaceChild(el, pollanswers)
    }
  }
  $('#newthreadtext').gtoggle()
  $('#newthreadpreview').gtoggle()
  $('#subscribediv').gtoggle()
}

function LoadEdit(type, post, depth) {
  ajax.get('forums.php?action=ajax_get_edit&postid=' + post + '&depth=' + depth + '&type=' + type, function (response) {
    $('#content' + post).raw().innerHTML = response
  })
}

function Refresh($Lite) {
  if ($Lite) {
    ajax.get('forums.php?action=ajax_refresh&auth=' + authkey + '&news_flush_lite=1', function (response) {
      alert('Success!')
    })
  } else {
    ajax.get('forums.php?action=ajax_refresh&auth=' + authkey + '&news_flush=1', function (response) {})
    alert('Success!')
  }
}

function AddPollOption(id) {
  var list = $('#poll_options').raw()
  var item = document.createElement('li')
  var form = document.createElement('form')
  form.method = 'POST'
  var auth = document.createElement('input')
  auth.type = 'hidden'
  auth.name = 'auth'
  auth.value = authkey
  form.appendChild(auth)

  var action = document.createElement('input')
  action.type = 'hidden'
  action.name = 'action'
  action.value = 'add_poll_option'
  form.appendChild(action)

  var threadid = document.createElement('input')
  threadid.type = 'hidden'
  threadid.name = 'threadid'
  threadid.value = id
  form.appendChild(threadid)

  var input = document.createElement('input')
  input.type = 'text'
  input.name = 'new_option'
  input.size = '50'
  form.appendChild(input)

  var submit = document.createElement('input')
  submit.type = 'submit'
  submit.id = 'new_submit'
  submit.value = 'Add'
  form.appendChild(submit)
  item.appendChild(form)
  list.appendChild(item)
}
function PollCount(count) {
  var anss = $('.js-Poll-answerInput')
  if (count) {
    var selectCount = 0
    for (var i = 0; i < anss.length; i++) {
      if (anss[i].checked) {
        selectCount++
      }
    }
    if (selectCount == count) {
      for (var i = 0; i < anss.length; i++) {
        anss[i].disabled = !anss[i].checked
      }
    } else {
      for (var i = 0; i < anss.length; i++) {
        anss[i].disabled = false
      }
    }
    $('#answer_0')[0].checked = false
  } else {
    for (var i = 0; i < anss.length; i++) {
      anss[i].disabled = false
      anss[i].checked = false
    }
  }
}

/**
 * HTML5-compatible storage system
 * Tries to use 'oninput' event to detect text changes and sessionStorage to save it.
 *
 * new StoreText('some_textarea_id', 'some_form_id', 'some_topic_id')
 * The form is required to remove the stored text once it is submitted.
 *
 * Topic ID is required to retrieve the right text on the right topic
 **/
function StoreText(field, form, topic) {
  this.field = document.getElementById(field)
  this.form = document.getElementById(form)
  this.key = 'auto_save_temp'
  this.keyID = 'auto_save_temp_id'
  this.topic = +topic
  this.load()
}
StoreText.prototype = {
  constructor: StoreText,
  load: function () {
    if (this.enabled() && this.valid()) {
      this.retrieve()
      this.autosave()
      this.clearForm()
    }
  },
  valid: function () {
    return this.field && this.form && !isNaN(this.topic)
  },
  enabled: function () {
    return window.sessionStorage && typeof window.sessionStorage === 'object'
  },
  retrieve: function () {
    var r = sessionStorage.getItem(this.key)
    if (this.topic === +sessionStorage.getItem(this.keyID) && r) {
      this.field.value = r
    }
  },
  remove: function () {
    sessionStorage.removeItem(this.keyID)
    sessionStorage.removeItem(this.key)
  },
  save: function () {
    sessionStorage.setItem(this.keyID, this.topic)
    sessionStorage.setItem(this.key, this.field.value)
  },
  autosave: function () {
    $(this.field).on(this.getInputEvent(), $.proxy(this.save, this))
  },
  getInputEvent: function () {
    var e
    if ('oninput' in this.field) {
      e = 'input'
    } else if (document.body.addEventListener) {
      e = 'change keyup paste cut'
    } else {
      e = 'propertychange'
    }
    return e
  },
  clearForm: function () {
    $(this.form).submit($.proxy(this.remove, this))
  },
}
