function ChangeCategory(catid) {
  if (catid == 1) {
    $('#split_releasetype').gshow()
    $('#split_artist').gshow()
    $('#split_year').gshow()
  } else if (catid == 4 || catid == 6) {
    $('#split_releasetype').ghide()
    $('#split_year').gshow()
    $('#split_artist').ghide()
  } else {
    $('#split_releasetype').ghide()
    $('#split_artist').ghide()
    $('#split_year').ghide()
  }
}

function ArtistManager() {
  var GroupID = window.location.search.match(/[?&]id=(\d+)/)
  if (typeof GroupID == 'undefined') {
    return
  } else {
    GroupID = GroupID[1]
  }
  var ArtistList
  if (!(ArtistList = $('#artist_list').raw())) {
    return false
  } else if ($('#artistmanager').raw()) {
    $('#artistmanager').gtoggle()
    $('#artist_list').gtoggle()
  } else {
    MainArtistCount = 0
    var elArtistManager = document.createElement('div')
    elArtistManager.id = 'artistmanager'

    var elArtistList = ArtistList.cloneNode(true)
    elArtistList.id = 'artistmanager_list'
    var importance = 0
    for (var i = 0; i < elArtistList.children.length; i++) {
      switch (elArtistList.children[i].className) {
        case 'artist_director':
          importance = 1
          break
        case 'artists_writter':
          importance = 2
          break
        case 'artists_producer':
          importance = 3
          break
        case 'artists_composer':
          importance = 4
          break
        case 'artists_cinematographer':
          importance = 5
          break
        case 'artists_actor':
          importance = 6
          break
      }
      if (elArtistList.children[i].children[0].tagName.toUpperCase() == 'A') {
        var ArtistID = elArtistList.children[i].children[0].href.match(/[?&]id=(\d+)/)[1]
        var elBox = document.createElement('input')
        elBox.type = 'checkbox'
        elBox.id = 'artistmanager_box' + (i - importance + 1)
        elBox.name = 'artistmanager_box'
        elBox.value = importance + ';' + ArtistID
        elBox.onclick = function (e) {
          SelectArtist(e, this)
        }
        elArtistList.children[i].insertBefore(elBox, elArtistList.children[i].children[0])
        if (
          importance == 1 ||
          importance == 2 ||
          importance == 3 ||
          importance == 4 ||
          importance == 5 ||
          importance == 6
        ) {
          MainArtistCount++
        }
      }
    }
    elArtistManager.appendChild(elArtistList)

    var elArtistForm = document.createElement('form')
    elArtistForm.id = 'artistmanager_form'
    elArtistForm.method = 'post'
    var elGroupID = document.createElement('input')
    elGroupID.type = 'hidden'
    elGroupID.name = 'groupid'
    elGroupID.value = GroupID
    elArtistForm.appendChild(elGroupID)
    var elAction = document.createElement('input')
    elAction.type = 'hidden'
    elAction.name = 'manager_action'
    elAction.id = 'manager_action'
    elAction.value = 'manage'
    elArtistForm.appendChild(elAction)
    var elAction = document.createElement('input')
    elAction.type = 'hidden'
    elAction.name = 'action'
    elAction.value = 'manage_artists'
    elArtistForm.appendChild(elAction)
    var elAuth = document.createElement('input')
    elAuth.type = 'hidden'
    elAuth.name = 'auth'
    elAuth.value = authkey
    elArtistForm.appendChild(elAuth)
    var elSelection = document.createElement('input')
    elSelection.type = 'hidden'
    elSelection.id = 'artists_selection'
    elSelection.name = 'artists'
    elArtistForm.appendChild(elSelection)

    var elSubmitDiv = document.createElement('div')
    var elImportance = document.createElement('select')
    elImportance.name = 'importance'
    elImportance.className = 'Input'
    elImportance.id = 'artists_importance'
    var elOpt = document.createElement('option')
    elOpt.value = 1
    elOpt.innerHTML = t('client.common.director')
    elImportance.appendChild(elOpt)
    elOpt = document.createElement('option')
    elOpt.value = 2
    elOpt.innerHTML = t('client.common.writer')
    elImportance.appendChild(elOpt)
    elOpt = document.createElement('option')
    elOpt.value = 3
    elOpt.innerHTML = t('client.common.producer')
    elImportance.appendChild(elOpt)
    elOpt = document.createElement('option')
    elOpt.value = 4
    elOpt.innerHTML = t('client.common.composer')
    elImportance.appendChild(elOpt)
    elOpt = document.createElement('option')
    elOpt.value = 5
    elOpt.innerHTML = t('client.common.cinematographer')
    elImportance.appendChild(elOpt)
    elOpt = document.createElement('option')
    elOpt.value = 6
    elOpt.innerHTML = t('client.common.actor')
    elImportance.appendChild(elOpt)
    elSubmitDiv.appendChild(elImportance)
    elSubmitDiv.appendChild(document.createTextNode(' '))

    elSubmitDiv.className = 'FormOneLine'
    var elSubmit = document.createElement('input')
    elSubmit.type = 'button'
    elSubmit.className = 'Button'
    elSubmit.value = 'Update'
    elSubmit.onclick = ArtistManagerSubmit
    elSubmitDiv.appendChild(elSubmit)
    elSubmitDiv.appendChild(document.createTextNode(' '))

    var elDelButton = document.createElement('input')
    elDelButton.type = 'button'
    elDelButton.className = 'Button'
    elDelButton.value = 'Delete'
    elDelButton.onclick = ArtistManagerDelete
    elSubmitDiv.appendChild(elDelButton)

    elArtistForm.appendChild(elSubmitDiv)
    elArtistManager.appendChild(elArtistForm)
    ArtistList.parentNode.appendChild(elArtistManager)
    $('#artist_list').ghide()
  }
}

function SelectArtist(e, obj) {
  if (window.event) {
    e = window.event
  }
  EndBox = Number(obj.id.substr(17))
  if (!e.shiftKey || typeof StartBox == 'undefined') {
    StartBox = Number(obj.id.substr(17))
  }
  Dir = EndBox > StartBox ? 1 : -1
  var checked = obj.checked
  for (var i = StartBox; i != EndBox; i += Dir) {
    var key,
      importance = obj.value.substr(0, 1),
      id = obj.value.substr(2)
    $('#artistmanager_box' + i).raw().checked = checked
  }
  StartBox = Number(obj.id.substr(17))
}

function ArtistManagerSubmit() {
  var Selection = new Array()
  var MainSelectionCount = 0
  for (var i = 0, boxes = $('[name="artistmanager_box"]'); boxes.raw(i); i++) {
    if (boxes.raw(i).checked) {
      Selection.push(boxes.raw(i).value)
      if (boxes.raw(i).value.substr(0, 1) == '1') {
        MainSelectionCount++
      }
    }
  }
  if (
    Selection.length == 0 ||
    ($('#manager_action').raw().value == 'delete' &&
      !confirm('Are you sure you want to delete ' + Selection.length + ' artists from this group?'))
  ) {
    return
  }
  $('#artists_selection').raw().value = Selection.join(',')
  if (
    (($('#artists_importance').raw().value != 1 &&
      $('#artists_importance').raw().value != 4 &&
      $('#artists_importance').raw().value != 6) ||
      $('#manager_action').raw().value == 'delete') &&
    MainSelectionCount == MainArtistCount
  ) {
    if (!$('.error_message').raw()) {
      Snackbar.error('All groups need to have at least one main artist, composer, or DJ.')
    }
    $('.error_message').raw().scrollIntoView()
    return
  }
  $('#artistmanager_form').raw().submit()
}

function ArtistManagerDelete() {
  $('#manager_action').raw().value = 'delete'
  ArtistManagerSubmit()
  $('#manager_action').raw().value = 'manage'
}

var voteLock = false
function DownVoteGroup(groupid, authkey) {
  if (voteLock) {
    return
  }
  voteLock = true
  ajax.get(
    'ajax.php?action=votefavorite&do=vote&groupid=' + groupid + '&vote=down' + '&auth=' + authkey,
    function (response) {
      if (response == 'noaction') {
        //No increment
      } else if (response == 'success') {
        $('#downvotes').raw().innerHTML = parseInt($('#downvotes').raw().innerHTML) + 1
        $('#totalvotes').raw().innerHTML = parseInt($('#totalvotes').raw().innerHTML) + 1
      }
    }
  )
  $('#vote_message').ghide()
  $('#unvote_message').gshow()
  $('#upvoted').ghide()
  $('#downvoted').gshow()
  voteLock = false
}

function UpVoteGroup(groupid, authkey) {
  if (voteLock) {
    return
  }
  voteLock = true
  ajax.get(
    'ajax.php?action=votefavorite&do=vote&groupid=' + groupid + '&vote=up' + '&auth=' + authkey,
    function (response) {
      if (response == 'noaction') {
        //No increment
      } else if (response == 'success') {
        // Increment both the upvote count and the total votes count
        $('#upvotes').raw().innerHTML = parseInt($('#upvotes').raw().innerHTML) + 1
        $('#totalvotes').raw().innerHTML = parseInt($('#totalvotes').raw().innerHTML) + 1
      }
    }
  )
  $('#vote_message').ghide()
  $('#unvote_message').gshow()
  $('#upvoted').gshow()
  $('#downvoted').ghide()
  voteLock = false
}

function UnvoteGroup(groupid, authkey) {
  if (voteLock) {
    return
  }
  voteLock = true
  ajax.get('ajax.php?action=votefavorite&do=unvote&groupid=' + groupid + '&auth=' + authkey, function (response) {
    if (response == 'noaction') {
      //No increment
    } else if (response == 'success-down') {
      $('#totalvotes').raw().innerHTML = parseInt($('#totalvotes').raw().innerHTML) - 1
      $('#downvotes').raw().innerHTML = parseInt($('#downvotes').raw().innerHTML) - 1
    } else if (response == 'success-up') {
      $('#totalvotes').raw().innerHTML = parseInt($('#totalvotes').raw().innerHTML) - 1
      $('#upvotes').raw().innerHTML = parseInt($('#upvotes').raw().innerHTML) - 1
    }
  })
  $('#vote_message').gshow()
  $('#unvote_message').ghide()
  $('#upvoted').ghide()
  $('#downvoted').ghide()
  voteLock = false
}

function BrowseExternalSub(torrentid) {
  if ($('#external_subtitle_container_' + torrentid).raw().innerHTML === '') {
    $('#external_subtitle_container_' + torrentid)
      .gshow()
      .raw().innerHTML = '<h4>Loading...</h4>'
    ajax.get('subtitles.php?action=ajax_get&torrentid=' + torrentid, function (response) {
      $('#external_subtitle_container_' + torrentid).raw().innerHTML = response
      globalapp.tooltipInit('#external_subtitle_container_' + torrentid)
    })
  } else {
    $('#external_subtitle_container_' + torrentid).gtoggle()
  }
}

function torrent_check(event) {
  var id = event.data.id,
    checked = event.data.checked
  $.get(
    'torrents.php',
    {
      action: 'torrent_check',
      torrentid: id,
      checked: checked,
    },
    function (data) {
      var obj = eval('(' + data + ')')
      if (obj.ret == 'success') {
        if (checked == 1) {
          $('#torrent' + id + '_check1').show()
          $('#slot-torrent' + id + '_check1').show()
          $('#torrent' + id + '_check0').hide()
          $('#slot-torrent' + id + '_check0').hide()
        } else {
          $('#torrent' + id + '_check0').show()
          $('#slot-torrent' + id + '_check0').show()
          $('#torrent' + id + '_check1').hide()
          $('#slot-torrent' + id + '_check1').hide()
        }
      } else {
        alert('失败')
      }
    }
  )
}
