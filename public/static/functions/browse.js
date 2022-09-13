function show_peers(TorrentID, Page, View) {
  if (Page > 0) {
    ajax.get('torrents.php?action=peerlist&page=' + Page + '&torrentid=' + TorrentID, function (response) {
      $('#' + View + '_peers_' + TorrentID)
        .gshow()
        .raw().innerHTML = response
    })
  } else {
    if ($('#' + View + '_peers_' + TorrentID).raw().innerHTML === '') {
      $('#' + View + '_peers_' + TorrentID)
        .gshow()
        .raw().innerHTML = '<h4>Loading...</h4>'
      ajax.get('torrents.php?action=peerlist&torrentid=' + TorrentID, function (response) {
        $('#' + View + '_peers_' + TorrentID)
          .gshow()
          .raw().innerHTML = response
      })
    } else {
      $('#' + View + '_peers_' + TorrentID).gtoggle()
    }
  }
  $('#' + View + '_viewlog_' + TorrentID).ghide()
  $('#' + View + '_snatches_' + TorrentID).ghide()
  $('#' + View + '_downloads_' + TorrentID).ghide()
  $('#' + View + '_files_' + TorrentID).ghide()
  $('#' + View + '_reported_' + TorrentID).ghide()
  $('#' + View + '_giver_' + TorrentID).ghide()
}

function show_giver(TorrentID, Page, View) {
  if (Page > 0) {
    ajax.get('torrents.php?action=sendbonuslist&page=' + Page + '&torrentid=' + TorrentID, function (response) {
      $('#' + View + '_giver_' + TorrentID)
        .gshow()
        .raw().innerHTML = response
    })
  } else {
    if ($('#' + View + '_giver_' + TorrentID).raw().innerHTML === '') {
      $('#' + View + '_giver_' + TorrentID)
        .gshow()
        .raw().innerHTML = '<h4>Loading...</h4>'
      ajax.get('torrents.php?action=sendbonuslist&torrentid=' + TorrentID, function (response) {
        $('#' + View + '_giver_' + TorrentID)
          .gshow()
          .raw().innerHTML = response
      })
    } else {
      $('#' + View + '_giver_' + TorrentID).gtoggle()
    }
  }
  $('#' + View + '_viewlog_' + TorrentID).ghide()
  $('#' + View + '_peers_' + TorrentID).ghide()
  $('#' + View + '_downloads_' + TorrentID).ghide()
  $('#' + View + '_files_' + TorrentID).ghide()
  $('#' + View + '_reported_' + TorrentID).ghide()
  $('#' + View + '_snatches_' + TorrentID).ghide()
}

function show_snatches(TorrentID, Page, View) {
  if (Page > 0) {
    ajax.get('torrents.php?action=snatchlist&page=' + Page + '&torrentid=' + TorrentID, function (response) {
      $('#' + View + '_snatches_' + TorrentID)
        .gshow()
        .raw().innerHTML = response
    })
  } else {
    if ($('#' + View + '_snatches_' + TorrentID).raw().innerHTML === '') {
      $('#' + View + '_snatches_' + TorrentID)
        .gshow()
        .raw().innerHTML = '<h4>Loading...</h4>'
      ajax.get('torrents.php?action=snatchlist&torrentid=' + TorrentID, function (response) {
        $('#' + View + '_snatches_' + TorrentID)
          .gshow()
          .raw().innerHTML = response
      })
    } else {
      $('#' + View + '_snatches_' + TorrentID).gtoggle()
    }
  }
  $('#' + View + '_viewlog_' + TorrentID).ghide()
  $('#' + View + '_peers_' + TorrentID).ghide()
  $('#' + View + '_downloads_' + TorrentID).ghide()
  $('#' + View + '_files_' + TorrentID).ghide()
  $('#' + View + '_reported_' + TorrentID).ghide()
  $('#' + View + '_giver_' + TorrentID).ghide()
}

function show_downloads(TorrentID, Page, View) {
  if (Page > 0) {
    ajax.get('torrents.php?action=downloadlist&page=' + Page + '&torrentid=' + TorrentID, function (response) {
      $('#' + View + '_downloads_' + TorrentID)
        .gshow()
        .raw().innerHTML = response
    })
  } else {
    if ($('#' + View + '_downloads_' + TorrentID).raw().innerHTML === '') {
      $('#' + View + '_downloads_' + TorrentID)
        .gshow()
        .raw().innerHTML = '<h4>Loading...</h4>'
      ajax.get('torrents.php?action=downloadlist&torrentid=' + TorrentID, function (response) {
        $('#' + View + '_downloads_' + TorrentID).raw().innerHTML = response
      })
    } else {
      $('#' + View + '_downloads_' + TorrentID).gtoggle()
    }
  }
  $('#' + View + '_viewlog_' + TorrentID).ghide()
  $('#' + View + '_peers_' + TorrentID).ghide()
  $('#' + View + '_snatches_' + TorrentID).ghide()
  $('#' + View + '_files_' + TorrentID).ghide()
  $('#' + View + '_reported_' + TorrentID).ghide()
  $('#' + View + '_giver_' + TorrentID).ghide()
}

function show_files(TorrentID, View) {
  if ($('#' + View + '_files_' + TorrentID).raw().innerHTML === '') {
    $('#' + View + '_files_' + TorrentID)
      .gshow()
      .raw().innerHTML = '<h4>Loading...</h4>'
    ajax.get('torrents.php?action=filelist&torrentid=' + TorrentID, function (response) {
      $('#' + View + '_files_' + TorrentID).raw().innerHTML = response
    })
  } else {
    $('#' + View + '_files_' + TorrentID).gtoggle()
  }
  $('#' + View + '_viewlog_' + TorrentID).ghide()
  $('#' + View + '_peers_' + TorrentID).ghide()
  $('#' + View + '_snatches_' + TorrentID).ghide()
  $('#' + View + '_downloads_' + TorrentID).ghide()
  $('#' + View + '_reported_' + TorrentID).ghide()
  $('#' + View + '_giver_' + TorrentID).ghide()
}

function show_reported(TorrentID, View) {
  if ($('#' + View + '_reported_' + TorrentID).raw().innerHTML === '') {
    $('#' + View + '_reported_' + TorrentID)
      .gshow()
      .raw().innerHTML = '<h4>Loading...</h4>'
    ajax.get('torrents.php?action=reportlist&torrentid=' + TorrentID, function (response) {
      $('#' + View + '_reported_' + TorrentID).raw().innerHTML = response
    })
  } else {
    $('#' + View + '_reported_' + TorrentID).gtoggle()
  }
  $('#' + View + '_files_' + TorrentID).ghide()
  $('#' + View + '_viewlog_' + TorrentID).ghide()
  $('#' + View + '_peers_' + TorrentID).ghide()
  $('#' + View + '_snatches_' + TorrentID).ghide()
  $('#' + View + '_downloads_' + TorrentID).ghide()
  $('#' + View + '_giver_' + TorrentID).ghide()
}

globalapp.browseAddTag = function browseAddTag(tag) {
  if ($('#tags').raw().value == '') {
    $('#tags').raw().value = tag
  } else {
    $('#tags').raw().value = $('#tags').raw().value + ', ' + tag
  }
}

var ArtistFieldCount = 1

globalapp.browseAddArtistField = function browseAddArtistField() {
  var x = $('#AddArtists').raw()
  x.appendChild(document.createElement('br'))
  var ArtistField = document.createElement('input')
  ArtistField.type = 'text'
  ArtistField.name = 'aliasname[]'
  ArtistField.size = '17'
  x.appendChild(ArtistField)
  x.appendChild(document.createTextNode(' '))
  var Importance = document.createElement('select')
  Importance.name = 'importance[]'
  Importance.innerHTML =
    '<option class="Select-option" value="1">' +
    t('client.common.director') +
    '</option><option class="Select-option" value="2">' +
    t('client.common.writer') +
    '</option><option class="Select-option" value="3">' +
    t('client.common.producer') +
    '</option><option class="Select-option" value="4">' +
    t('client.common.composer') +
    '</option><option class="Select-option" value="5">' +
    t('client.common.cinematographer') +
    '</option><option class="Select-option" value="6">' +
    t('client.common.actor') +
    '</option>'
  x.appendChild(Importance)
  if ($('#artist').data('gazelle-autocomplete')) {
    $(ArtistField).live('focus', function () {
      $(ArtistField).autocomplete({
        serviceUrl: 'artist.php?action=autocomplete',
      })
    })
  }
  ArtistFieldCount++
}

var coverFieldCount = 0
var hasCoverAddButton = false

function addCoverField() {
  if (coverFieldCount >= 100) {
    return
  }
  var x = $('#add_cover').raw()
  x.appendChild(document.createElement('br'))
  var field = document.createElement('input')
  field.type = 'text'
  field.name = 'image[]'
  field.placeholder = 'URL'
  x.appendChild(field)
  x.appendChild(document.createTextNode(' '))
  var summary = document.createElement('input')
  summary.type = 'text'
  summary.name = 'summary[]'
  summary.placeholder = 'Summary'
  x.appendChild(summary)
  coverFieldCount++

  if (!hasCoverAddButton) {
    x = $('#add_covers_form').raw()
    field = document.createElement('input')
    field.type = 'submit'
    field.value = 'Add'
    x.appendChild(field)
    hasCoverAddButton = true
  }
}

function ToggleEditionRows() {
  $('#edition_title').gtoggle()
  $('#edition_label').gtoggle()
  $('#edition_catalogue').gtoggle()
}
