var ARTIST_AUTOCOMPLETE_URL = 'artist.php?action=autocomplete'
var TAGS_AUTOCOMPLETE_URL = 'torrents.php?action=autocomplete_tags'
var TORRENT_AUTOCOMPLETE_URL = 'torrents.php?action=autocomplete'
var SELECTOR = '[data-gazelle-autocomplete="true"]'
$(document).ready(function () {
  var url = new gazURL()

  $('#artistsearch' + SELECTOR).autocomplete({
    deferRequestBy: 300,
    onSelect: function (suggestion) {
      window.location = 'artist.php?id=' + suggestion['data']
    },
    serviceUrl: ARTIST_AUTOCOMPLETE_URL,
  })
  $('#torrentssearch' + SELECTOR).autocomplete({
    deferRequestBy: 300,
    serviceUrl: TORRENT_AUTOCOMPLETE_URL,
    onSelect: function (suggestion) {
      window.location = 'torrents.php?id=' + suggestion['data']
    },
  })

  if (
    url.path == 'upload' ||
    url.path == 'artist' ||
    (url.path == 'requests' && url.query['action'] == 'new') ||
    url.path == 'collages'
  ) {
    $('#artist' + SELECTOR).autocomplete({
      deferRequestBy: 300,
      serviceUrl: ARTIST_AUTOCOMPLETE_URL,
    })
  }
  if (url.path == 'artist') {
    $('#artistsimilar' + SELECTOR).autocomplete({
      deferRequestBy: 300,
      serviceUrl: ARTIST_AUTOCOMPLETE_URL,
      onSelect: function (suggestion) {
        $('#similar_artistid').val(suggestion['data'])
      },
    })
  }
  if (url.path == 'torrents') {
    $('#artist' + SELECTOR).autocomplete({
      deferRequestBy: 300,
      serviceUrl: ARTIST_AUTOCOMPLETE_URL,
      onSelect: function (suggestion) {
        $('#artist_imdb').val(suggestion['imdb'])
        $('#artist_name').val(suggestion['name'])
        $('#artist_sub_name').val(suggestion['sub_name'])
      },
    })
    $('#tagsearch' + SELECTOR).autocomplete({
      deferRequestBy: 300,
      serviceUrl: TAGS_AUTOCOMPLETE_URL,
      onSelect: function (suggestion) {
        $('#tagname').val(suggestion['name'])
        $('#tagsubname').val(suggestion['subname'])
      }
    })
  }
})
