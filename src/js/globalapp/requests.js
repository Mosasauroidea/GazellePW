const minimumVote = 1 * 1024 * 1024 * 1024

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.FormRequestNew')
  if (form) {
    form.onsubmit = function (e) {
      $('input:disabled, select:disabled').prop('disabled', false)
      $('#post').addClass('is-loading').prop('disabled', true)
      return true
    }
  }
})

globalapp.requestVote = function requestVote(amount, requestid) {
  if (typeof amount == 'undefined') {
    amount = parseInt($('#amount').raw().value)
  }
  if (amount == 0) {
    amount = minimumVote
  }

  let index
  let votecount
  let bounty
  if (!requestid) {
    requestid = $('#requestid').raw().value
    votecount = $('#votecount').raw()
    index = false
  } else {
    votecount = $('#vote_count_' + requestid).raw()
    bounty = $('#bounty_' + requestid).raw()
    index = true
  }

  if (amount > minimumVote) {
    const upload = $('#current_uploaded').raw().value
    const download = $('#current_downloaded').raw().value
    const rr = $('#current_rr').raw().value
    if (amount > 0.3 * (upload - rr * download)) {
      if (
        !confirm(
          'This vote is more than 30% of your buffer. Please confirm that you wish to place this large of a vote.'
        )
      ) {
        return false
      }
    }
  }

  ajax.get(
    'requests.php?action=takevote&id=' + requestid + '&auth=' + authkey + '&amount=' + amount,
    function (response) {
      if (response == 'bankrupt') {
        Snackbar.error('You do not have sufficient upload credit to add ' + get_size(amount) + ' to this request')
        return
      } else if (response == 'dupesuccess') {
        //No increment
      } else if (response == 'success') {
        votecount.innerHTML = parseInt(votecount.innerHTML) + 1
      }

      if ($('#total_bounty').results() > 0) {
        let totalBounty = parseInt($('#total_bounty').raw().value)
        totalBounty += amount * (1 - $('#request_tax').raw().value)
        const requestTax = $('#request_tax').raw().value
        $('#total_bounty').raw().value = totalBounty
        $('#formatted_bounty').raw().innerHTML = get_size(totalBounty)
        $('#movieinfo_bountry').raw().innerHTML = get_size(totalBounty)
        if (requestTax > 0) {
          Snackbar.notify(
            'Your vote of ' +
              get_size(amount) +
              ', adding a ' +
              get_size(amount * (1 - $('#request_tax').raw().value)) +
              ' bounty, has been added'
          )
        } else {
          Snackbar.notify('Your vote of ' + get_size(amount) + ' has been added')
        }
        $('#button').raw().disabled = true
      } else {
        Snackbar.notify('Your vote of ' + get_size(amount) + ' has been added')
      }
    }
  )
}

globalapp.requestCalculate = function requestCalculate() {
  const mul = $('#unit').raw().options[$('#unit').raw().selectedIndex].value == 'mb' ? 1024 * 1024 : 1024 * 1024 * 1024
  const amt = Math.floor($('#amount_box').raw().value * mul)
  if (amt > $('#current_uploaded').raw().value) {
    $('#new_uploaded').raw().innerHTML = "You can't afford that request!"
    $('#new_bounty').raw().innerHTML = '0.00 GB'
    if ($('#bounty_after_tax').raw()) {
      $('#bounty_after_tax').raw().innerHTML = '0.00 GB'
    }
    $('#button').raw().disabled = true
  } else if (
    isNaN($('#amount_box').raw().value) ||
    (window.location.search.indexOf('action=new') != -1 && $('#amount_box').raw().value * mul < 100 * 1024 * 1024) ||
    (window.location.search.indexOf('action=view') != -1 && $('#amount_box').raw().value * mul < minimumVote)
  ) {
    $('#new_uploaded').raw().innerHTML = get_size($('#current_uploaded').raw().value)
    $('#new_bounty').raw().innerHTML = '0.00 GB'
    if ($('#bounty_after_tax').raw()) {
      $('#bounty_after_tax').raw().innerHTML = '0.00 GB'
    }
    $('#button').raw().disabled = true
  } else {
    $('#button').raw().disabled = false
    $('#amount').raw().value = amt
    $('#new_uploaded').raw().innerHTML = get_size($('#current_uploaded').raw().value - amt)
    $('#new_ratio').raw().innerHTML = ratio(
      $('#current_uploaded').raw().value - amt,
      $('#current_downloaded').raw().value
    )
    $('#new_bounty').raw().innerHTML = get_size(mul * $('#amount_box').raw().value)
    if ($('#bounty_after_tax').raw()) {
      $('#bounty_after_tax').raw().innerHTML = get_size(mul * 0.9 * $('#amount_box').raw().value)
    }
  }
}

globalapp.requestCategories = function requestCategories() {
  var cat = $('#categories').raw().options[$('#categories').raw().selectedIndex].value
  if (cat == 'Movie') {
    $('#artist_tr').gshow()
    $('#releasetypes_tr').gshow()
    $('#year_tr').gshow()
  }
}

globalapp.requestAddTag = function requestAddTag() {
  if ($('#tags').raw().value == '') {
    $('#tags').raw().value = $('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value
  } else if ($('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value == '---') {
  } else {
    $('#tags').raw().value =
      $('#tags').raw().value + ', ' + $('#genre_tags').raw().options[$('#genre_tags').raw().selectedIndex].value
  }
}

globalapp.allToggle = function allToggle(id, disable) {
  var arr = document.getElementsByName(id + '[]')
  var master = $('#toggle_' + id).raw().checked
  for (const element of arr) {
    element.checked = master
    if (disable == 1) {
      element.disabled = master
    }
  }
}

var ArtistCount = 1

globalapp.requestMovieAutofill = function requestMovieAutofill() {
  const target = document.querySelector('.Button.autofill')
  var imdb = $('#imdb').val().match(/tt\d+/)
  if (imdb) {
    imdb = imdb[0]
  }
  var group = $('#group')
    .val()
    .match(/id=\d+/)
  if (group) {
    group = $('#group').val()
  }
  if (!group && !imdb) {
    return
  }
  globalapp.buttonSetLoading(target, true)
  $.ajax({
    url: 'requests.php',
    data: {
      action: 'autofill',
      imdb: imdb,
      group: group,
    },
    type: 'GET',
    error: (err) => {
      globalapp.buttonSetLoading(target, false)
      globalapp.setFormError('client.common.imdb_unknown_error')
    },
    success: (data) => {
      globalapp.buttonSetLoading(target, false)
      globalapp.setFormError(null)
      if (data.code) {
        globalapp.setFormError('client.error.imdb_unknown_error')
        return
      }
      data = data.response
      if (data.Title) {
        $('#name').val(data.Title)
      }
      if (data.SubTitle) {
        $('#subname').val(data.SubTitle)
      }
      if (data.Poster) {
        $('#image').val(data.Poster)
      }
      if (data.Year) {
        $('#year').val(data.Year)
      }
      if (data.Genre) {
        $('#tags').val(data.Genre.toLowerCase().replace('-', '.'))
      }
      if (data.Type == 'Movie') {
        $('#releasetype').val(1)
      }
      if (data.IMDBID) {
        $('#imdb').val(data.IMDBID)
      }
      if (data.GroupLink) {
        $('#group').val(data.GroupLink)
      } else {
        $('#group').val('')
      }
      var artists = [],
        importance = [],
        artist_ids = []
      if (data.Directors) {
        Object.keys(data.Directors).map((k) => {
          artists.push(data.Directors[k])
          artist_ids.push(k)
          importance.push(1)
        })
      }
      globalapp.requestRemoveAllArtistFields()
      for (var i = 0; i < artists.length; i++) {
        var artistid, importanceid, artistimdbid, artist_sub_name
        if (i) {
          artistid = '#artist_' + i
          importanceid = '#importance_' + i
          artistimdbid = '#artist_id_' + i
          artist_sub_name = '#artist_sub_' + i
          globalapp.requestAddArtistField(true)
        } else {
          artistid = '#artist'
          importanceid = '#importance'
          artistimdbid = '#artist_id'
          artist_sub_name = '#artist_sub'
        }
        $(artistid).val(artists[i])
        $(importanceid).val(importance[i])
        $(artistimdbid).val(artist_ids[i])
        if (data.SubName && data.SubName[[artists[i]]]) {
          $(artist_sub_name).val(data.SubName[[artists[i]]])
        }
      }
      $('.FormUpload').addClass('u-formUploadAutoFilled')
      $('.u-formRequestArtistList input, .u-formRequestArtistList select').prop('disabled', true)
      if (artists.length >= 5) {
        globalapp.requestArtistsShowMore({ hide: true })
      }
      if (data.FillSource == 'group') {
        $('#genre_tags, #imdb, #group, #image, #tags, #releasetype, #year, #name, #subname').prop('readonly', true)
        $('#add_artist, #remove_artist').ghide()
      }
    },
    dataType: 'json',
  })
}

globalapp.setFormError = function setFormError(key, options = {}) {
  if (key) {
    const message = t(key, options)
    $('.imdb.Form-errorMessage').html(message)
  } else {
    $('.imdb.Form-errorMessage').html('')
  }
}

globalapp.requestAddArtistField = function AddArtistField() {
  var ArtistIDField = document.createElement('input')
  ArtistIDField.classList.add('Input', 'is-small')
  ArtistIDField.type = 'text'
  ArtistIDField.id = 'artist_id_' + ArtistCount
  ArtistIDField.name = 'artist_ids[]'
  ArtistIDField.size = 45
  ArtistIDField.placeholder = t('client.upload.imdb')

  var ArtistField = document.createElement('input')
  ArtistField.classList.add('Input', 'is-small')
  ArtistField.type = 'text'
  ArtistField.id = 'artist_' + ArtistCount
  ArtistField.name = 'artists[]'
  ArtistField.size = 45
  ArtistField.placeholder = t('client.upload.english_name')

  var ArtistSubField = document.createElement('input')
  ArtistSubField.classList.add('Input', 'is-small')
  ArtistSubField.type = 'text'
  ArtistSubField.id = 'artist_sub_' + ArtistCount
  ArtistSubField.name = 'artists_sub[]'
  ArtistSubField.size = 25
  ArtistSubField.placeholder = t('client.upload.sub_name')

  var ImportanceField = document.createElement('input')
  ImportanceField.id = 'importance_' + ArtistCount
  ImportanceField.name = 'importance[]'
  ImportanceField.type = 'hidden'

  const div = document.createElement('div')
  div.classList.add('Form-inputs', 'is-artist')
  div.appendChild(ArtistIDField)
  div.appendChild(ArtistField)
  div.appendChild(ArtistSubField)
  div.appendChild(ImportanceField)
  $('#artistfields .show-more').before(div)

  if ($('#artist_0').data('gazelle-autocomplete') || $('#artist').data('gazelle-autocomplete')) {
    $(ArtistField).live('focus', function () {
      $(ArtistField).autocomplete({
        serviceUrl: 'artist.php?action=autocomplete',
      })
    })
  }

  ArtistCount++
}

globalapp.requestRemoveArtistField = function RemoveArtistField() {
  if (ArtistCount === 1) {
    return
  }
  $('#artistfields .Form-inputs.is-artist').last().remove()
  ArtistCount--
}

globalapp.requestRemoveAllArtistFields = function removeAllArtistFields() {
  $('#artistfields .Form-inputs.is-artist').slice(1).remove()
  ArtistCount = 1
}

globalapp.requestArtistsShowMore = function artistsShowMore({ hide } = {}) {
  if (hide) {
    $('.u-formRequestArtistList .Form-inputs').slice(5).hide()
    $('.u-formRequestArtistList.show-more').gshow()
  } else {
    $('.u-formRequestArtistList.Form-inputs').slice(5).show()
    $('.u-formRequestArtistList.show-more').ghide()
  }
}

globalapp.requestNoImdbId = function noImdbId() {
  const form = $('.FormRequest')
  form.toggleClass('u-formRequestNoImdbId')
  $('.u-formRequestNoImdbNote').toggleClass('hidden')
}

globalapp.requestNewTorrent = function newTorrent() {
  const form = $('.FormRequest')
  form.removeClass('u-formNewRequest')
  form.addClass('u-formSeedRequest')
  form.addClass('u-formRequest')
}

globalapp.requestSeedTorrent = function seedTorrent() {
  const form = $('.FormRequest')
  form.addClass('u-formNewRequest')
  form.removeClass('u-formSeedRequest')
  form.addClass('u-formRequest')
}
