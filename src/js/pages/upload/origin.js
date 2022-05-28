var ArtistCount = 1

function Categories() {
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
window.Categories = Categories

function Remaster() {
  if ($('#remaster').raw().checked) {
    $('#remaster_true').gshow()
  } else {
    $('#remaster_true').ghide()
  }
}
window.Remaster = Remaster

function add_tag() {
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
window.add_tag = add_tag

var FormatCount = 0

function AddFormat() {
  if (FormatCount >= 10) {
    return
  }
  FormatCount++
  $('#extras').raw().value = FormatCount

  var NewRow = document.createElement('tr')
  NewRow.id = 'new_torrent_row' + FormatCount
  NewRow.setAttribute(
    'style',
    'border-top-width: 5px; border-left-width: 5px; border-right-width: 5px;'
  )

  var NewCell1 = document.createElement('td')
  NewCell1.setAttribute('class', 'label')
  NewCell1.innerHTML = 'Extra Torrent File'

  var NewCell2 = document.createElement('td')
  var TorrentField = document.createElement('input')
  TorrentField.type = 'file'
  TorrentField.id = 'extra_torrent_file' + FormatCount
  TorrentField.name = 'extra_torrent_files[]'
  TorrentField.size = 50
  NewCell2.appendChild(TorrentField)

  NewRow.appendChild(NewCell1)
  NewRow.appendChild(NewCell2)

  NewRow = document.createElement('tr')
  NewRow.id = 'new_format_row' + FormatCount
  NewRow.setAttribute(
    'style',
    'border-left-width: 5px; border-right-width: 5px;'
  )
  NewCell1 = document.createElement('td')
  NewCell1.setAttribute('class', 'label')
  NewCell1.innerHTML = 'Extra Format / Bitrate'

  NewCell2 = document.createElement('td')
  tmp =
    '<select class="Input" id="releasetype" name="extra_formats[]"><option class="Select-option" value="">---</option>'
  var formats = ['Saab', 'Volvo', 'BMW']
  for (var i in formats) {
    tmp +=
      '<option class="Select-option" value="' +
      formats[i] +
      '">' +
      formats[i] +
      '</option>\n'
  }
  tmp += '</select>'
  var bitrates = ['1', '2', '3']
  tmp +=
    '<select class="Input" id="releasetype" name="extra_bitrates[]"><option class="Select-option" value="">---</option>'
  for (var i in bitrates) {
    tmp +=
      '<option class="Select-option" value="' +
      bitrates[i] +
      '">' +
      bitrates[i] +
      '</option>\n'
  }
  tmp += '</select>'

  NewCell2.innerHTML = tmp
  NewRow.appendChild(NewCell1)
  NewRow.appendChild(NewCell2)

  NewRow = document.createElement('tr')
  NewRow.id = 'new_description_row' + FormatCount
  NewRow.setAttribute(
    'style',
    'border-bottom-width: 5px; border-left-width: 5px; border-right-width: 5px;'
  )
  NewCell1 = document.createElement('td')
  NewCell1.setAttribute('class', 'label')
  NewCell1.innerHTML = 'Extra Release Description'

  NewCell2 = document.createElement('td')
  NewCell2.innerHTML =
    '<textarea class="Input" name="extra_release_desc[]" id="release_desc" cols="60" rows="4"></textarea>'

  NewRow.appendChild(NewCell1)
  NewRow.appendChild(NewCell2)
}
window.AddFormat = AddFormat

function RemoveFormat() {
  if (FormatCount == 0) {
    return
  }
  $('#extras').raw().value = FormatCount

  var x = $('#new_torrent_row' + FormatCount).raw()
  x.parentNode.removeChild(x)

  x = $('#new_format_row' + FormatCount).raw()
  x.parentNode.removeChild(x)

  x = $('#new_description_row' + FormatCount).raw()
  x.parentNode.removeChild(x)

  FormatCount--
}
window.RemoveFormat = RemoveFormat

function AddArtistField(movie = false) {
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

window.AddArtistField = AddArtistField

function RemoveArtistField() {
  if (ArtistCount === 1) {
    return
  }
  $('#artistfields .Form-inputs.is-artist').last().remove()
  ArtistCount--
}
window.RemoveArtistField = RemoveArtistField

function removeAllArtistFields() {
  $('#artistfields .Form-inputs.is-artist').slice(1).remove()
  ArtistCount = 1
}
window.removeAllArtistFields = removeAllArtistFields

var remaster_year,
  remaster_title,
  remaster_record_label,
  remaster_catalogue_number
function ToggleUnknown() {
  if ($('#unknown').raw().checked) {
    remaster_year = $('#remaster_year').raw().value
    remaster_title = $('#remaster_title').raw().value
    remaster_record_label = $('#remaster_record_label').raw().value
    remaster_catalogue_number = $('#remaster_catalogue_number').raw().value
    $('#remaster_year').raw().value = ''
    $('#remaster_title').raw().value = ''
    $('#remaster_record_label').raw().value = ''
    $('#remaster_catalogue_number').raw().value = ''

    if ($('#groupremasters').raw()) {
      $('#groupremasters').raw().selectedIndex = 0
      $('#groupremasters').raw().disabled = true
    }

    $('#remaster_year').raw().disabled = true
    $('#remaster_title').raw().disabled = true
    $('#remaster_record_label').raw().disabled = true
    $('#remaster_catalogue_number').raw().disabled = true
  } else {
    $('#remaster_year').raw().disabled = false
    $('#remaster_title').raw().disabled = false
    $('#remaster_record_label').raw().disabled = false
    $('#remaster_catalogue_number').raw().disabled = false

    if ($('#groupremasters').raw()) {
      $('#groupremasters').raw().disabled = false
    }
    $('#remaster_year').raw().value = remaster_year
    $('#remaster_title').raw().value = remaster_title
    $('#remaster_record_label').raw().value = remaster_record_label
    $('#remaster_catalogue_number').raw().value = remaster_catalogue_number
  }
}
window.ToggleUnknown = ToggleUnknown

function GroupRemaster() {
  var remasters = json.decode($('#json_remasters').raw().value)
  var index =
    $('#groupremasters').raw().options[$('#groupremasters').raw().selectedIndex]
      .value
  if (index != '') {
    $('#remaster_year').raw().value = remasters[index][1]
    $('#remaster_title').raw().value = remasters[index][2]
    $('#remaster_record_label').raw().value = remasters[index][3]
    $('#remaster_catalogue_number').raw().value = remasters[index][4]
  }
}
window.GroupRemaster = GroupRemaster

/**
 * Accepts a mapping which is an object where each prop is the id of
 * an html element and the value corresponds to a key in the data object
 * which we want to put as the value of the html element.
 *
 * @param mapping
 * @param data
 */
function FillInFields(mapping, data) {
  for (var prop in mapping) {
    if (!mapping.hasOwnProperty(prop)) {
      continue
    }
    if (data[mapping[prop]] && data[mapping[prop]] !== '') {
      $('#' + prop)
        .val(data[mapping[prop]])
        .trigger('change')
    }
  }
}
window.FillInFields = FillInFields

function AddArtist(array, importance, cnt) {
  for (var i = 0; i < array.length; i++) {
    var artist_id = cnt > 0 ? 'artist_' + cnt : 'artist'
    var importance_id = cnt > 0 ? 'importance_' + cnt : 'importance'
    if (array[i]['name']) {
      $('#' + artist_id).val(array[i]['name'])
      $('#' + importance_id).val(importance)
      AddArtistField()
      cnt++
    }
  }
  return cnt
}
window.AddArtist = AddArtist

function WaitForCategory(callback) {
  setTimeout(function () {
    var dynamic_form = $('#dynamic_form')
    if (dynamic_form.data('loaded') === true) {
      dynamic_form.data('loaded', false)
      callback()
    } else {
      setTimeout(WaitForCategory(callback), 400)
    }
  }, 100)
}
window.WaitForCategory = WaitForCategory

function ParseUploadJson() {
  var reader = new FileReader()

  reader.addEventListener(
    'load',
    function () {
      try {
        var data = JSON.parse(reader.result.toString())
        var group = data['response']['group']
        var torrent = data['response']['torrent']

        var categories_mapping = {
          Movie: 1,
        }

        var categories = $('#categories')
        if (!group['categoryName']) {
          group['categoryName'] = 'Movie'
        }
        categories
          .val(categories_mapping[group['categoryName']])
          .triggerHandler('change')
        // delay for the form to change before filling it
        WaitForCategory(function () {
          ParseForm(group, torrent)
        })
      } catch (e) {
        alert('Could not read file. Please try again.')
        console.log(e)
      }
    },
    false
  )

  var file = $('#torrent-json-file')[0].files[0]
  if (file) {
    reader.readAsText(file)
  }
}
window.ParseUploadJson = ParseUploadJson

function AlterOriginal() {
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
window.AlterOriginal = AlterOriginal

function artistsShowMore({ hide } = {}) {
  if (hide) {
    $('.u-formUploadArtistList .Form-inputs').slice(5).hide()
    $('.u-formUploadArtistList .show-more').show()
  } else {
    $('.u-formUploadArtistList .Form-inputs').slice(5).show()
    $('.u-formUploadArtistList .show-more').hide()
  }
}
window.artistsShowMore = artistsShowMore

function noImdbId() {
  const form = $('.FormUpload')
  form.toggleClass('u-formUploadNoImdbId')
  $('.u-formUploadNoImdbNote').toggleClass('hidden')
}
window.noImdbId = noImdbId
