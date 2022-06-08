var ArtistCount = 1

globalapp.uploadCategories = function uploadCategories() {
  var dynamic_form = $('#dynamic_form')
  ajax.get(
    'ajax.php?action=upload_section&categoryid=' + $('#categories').raw().value,
    function (response) {
      dynamic_form.raw().innerHTML = response
      initMultiButtons()
      // Evaluate the code that generates previews.
      eval($('#dynamic_form script.preview_code').html())
      setTimeout(function () {
        dynamic_form.data('loaded', true)
      }, 500)

      ArtistCount = 1
    }
  )
}

globalapp.uploadAddTag = function uploadAddTag() {
  if ($('#tags').raw().value == '') {
    $('#tags').raw().value =
      $('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value
  } else if (
    $('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex]
      .value === ''
  ) {
  } else {
    $('#tags').raw().value =
      $('#tags').raw().value +
      ', ' +
      $('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value
  }
}

globalapp.uploadAddArtistField = function AddArtistField(movie = false) {
  var ArtistIDField = document.createElement('input')
  ArtistIDField.type = 'hidden'
  ArtistIDField.id = 'artist_id_' + ArtistCount
  ArtistIDField.name = 'artist_ids[]'
  ArtistIDField.size = 45

  var ArtistField = document.createElement('input')
  ArtistField.classList.add('Input')
  ArtistField.type = 'text'
  ArtistField.id = 'artist_' + ArtistCount
  ArtistField.name = 'artists[]'
  ArtistField.size = 45

  var ArtistChineseField = document.createElement('input')
  ArtistChineseField.classList.add('Input', 'is-small')
  ArtistChineseField.type = 'text'
  ArtistChineseField.id = 'artist_chinese_' + ArtistCount
  ArtistChineseField.name = 'artists_chinese[]'
  ArtistChineseField.size = 25

  var ImportanceField = document.createElement('select')
  ImportanceField.classList.add('Input')
  ImportanceField.id = 'importance_' + ArtistCount
  ImportanceField.name = 'importance[]'

  ImportanceField.options[0] = new Option(translation.get('director'), '1')
  ImportanceField.options[1] = new Option(translation.get('writer'), '2')
  ImportanceField.options[2] = new Option(translation.get('producer'), '3')
  ImportanceField.options[3] = new Option(translation.get('composer'), '4')
  ImportanceField.options[4] = new Option(
    translation.get('cinematographer'),
    '5'
  )
  ImportanceField.options[5] = new Option(translation.get('actor'), '6')

  var x = $('#artistfields').raw()
  const div = document.createElement('div')
  div.classList.add('Form-inputs', 'is-artist')
  div.appendChild(ArtistIDField)
  div.appendChild(ArtistField)
  div.appendChild(ArtistChineseField)
  div.appendChild(ImportanceField)
  $('#artistfields .show-more').before(div)

  if (
    $('#artist_0').data('gazelle-autocomplete') ||
    $('#artist').data('gazelle-autocomplete')
  ) {
    $(ArtistField).live('focus', function () {
      $(ArtistField).autocomplete({
        serviceUrl: 'artist.php?action=autocomplete',
      })
    })
  }

  ArtistCount++
}

globalapp.uploadRemoveArtistField = function RemoveArtistField() {
  if (ArtistCount === 1) {
    return
  }
  $('#artistfields .Form-inputs.is-artist').last().remove()
  ArtistCount--
}

globalapp.uploadRemoveAllArtistFields = function removeAllArtistFields() {
  $('#artistfields .Form-inputs.is-artist').slice(1).remove()
  ArtistCount = 1
}

globalapp.uploadAlterOriginal = function AlterOriginal() {
  if (
    !$('input[name=buy]').raw().checked &&
    !$('input[name=diy]').raw().checked
  ) {
    //$('input[name=allow]').raw().disabled = true
    $('input[name=jinzhuan]').raw().disabled = true
    //$('input[name=allow]').raw().checked = false
    $('input[name=jinzhuan]').raw().checked = false
  } else {
    //$('input[name=allow]').raw().disabled = false
    $('input[name=jinzhuan]').raw().disabled = false
  }
}

globalapp.uploadArtistsShowMore = function artistsShowMore({ hide } = {}) {
  if (hide) {
    $('.u-formUploadArtistList .Form-inputs').slice(5).hide()
    $('.u-formUploadArtistList .show-more').show()
  } else {
    $('.u-formUploadArtistList .Form-inputs').slice(5).show()
    $('.u-formUploadArtistList .show-more').hide()
  }
}

globalapp.uploadNoImdbId = function noImdbId() {
  const form = $('.FormUpload')
  form.toggleClass('u-formUploadNoImdbId')
  $('.u-formUploadNoImdbNote').toggleClass('hidden')
}
