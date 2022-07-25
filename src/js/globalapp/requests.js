const minimumVote = 100 * 1024 * 1024

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
    $('#new_bounty').raw().innerHTML = '0.00 MB'
    if ($('#bounty_after_tax').raw()) {
      $('#bounty_after_tax').raw().innerHTML = '0.00 MB'
    }
    $('#button').raw().disabled = true
  } else if (
    isNaN($('#amount_box').raw().value) ||
    (window.location.search.indexOf('action=new') != -1 && $('#amount_box').raw().value * mul < 100 * 1024 * 1024) ||
    (window.location.search.indexOf('action=view') != -1 && $('#amount_box').raw().value * mul < minimumVote)
  ) {
    $('#new_uploaded').raw().innerHTML = get_size($('#current_uploaded').raw().value)
    $('#new_bounty').raw().innerHTML = '0.00 MB'
    if ($('#bounty_after_tax').raw()) {
      $('#bounty_after_tax').raw().innerHTML = '0.00 MB'
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

globalapp.requestAddArtistField = function requestAddArtistField(movie = false) {
  var ArtistIDField = document.createElement('input')
  ArtistIDField.type = 'hidden'
  ArtistIDField.id = 'artist_id_' + ArtistCount
  ArtistIDField.name = 'artist_ids[]'
  ArtistIDField.size = 45

  var ArtistField = document.createElement('input')
  ArtistField.type = 'text'
  ArtistField.id = 'artist_' + ArtistCount
  ArtistField.name = 'artists[]'
  ArtistField.size = 45

  var ArtistChineseField = document.createElement('input')
  ArtistChineseField.type = 'text'
  ArtistChineseField.id = 'artist_chinese_' + ArtistCount
  ArtistChineseField.name = 'artists_chinese[]'
  ArtistChineseField.size = 25

  var ImportanceField = document.createElement('select')
  ImportanceField.id = 'importance_' + ArtistCount
  ImportanceField.name = 'importance[]'
  ImportanceField.options[0] = new Option(t('client.common.director'), '1')
  ImportanceField.options[1] = new Option(t('client.common.writer'), '2')
  ImportanceField.options[2] = new Option(t('client.common.producer'), '3')
  ImportanceField.options[3] = new Option(t('client.common.composer'), '4')
  ImportanceField.options[4] = new Option(t('client.common.cinematographer'), '5')
  ImportanceField.options[5] = new Option(t('client.common.actor'), '6')

  var x = $('#artistfields').raw()
  const div = document.createElement('div')
  div.classList.add('artist')
  div.appendChild(ArtistIDField)
  div.appendChild(ArtistField)
  div.appendChild(ArtistChineseField)
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

globalapp.requestRemoveArtistField = function requestRemoveArtistField() {
  if (ArtistCount === 1) {
    return
  }
  $('#artistfields .artist').last().remove()
  ArtistCount--
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

globalapp.requestToggle = function reqeustToggle(id, disable) {
  var arr = document.getElementsByName(id + '[]')
  var master = $('#toggle_' + id).raw().checked
  for (const element of arr) {
    element.checked = master
    if (disable == 1) {
      element.disabled = master
    }
  }
}

globalapp.requestMovieAutofill = function requestMovieAutofill(event) {
  const target = event.currentTarget
  var imdb = $('#imdb').val().match(/tt\d+/)
  if (imdb) {
    imdb = imdb[0]
  } else {
    return
  }

  globalapp.buttonSetLoading(target, true)
  $.ajax({
    url: 'upload.php',
    data: {
      action: 'movie_info',
      imdbid: imdb,
    },
    type: 'GET',
    error: (error) => {
      globalapp.buttonSetLoading(target, false)
      console.error(error)
    },
    success: (data) => {
      globalapp.buttonSetLoading(target, false)
      const errorMessage = $('.imdb.Form-errorMessage')
      if (data.code) {
        globalapp.setFormError(
          data.code === 1
            ? 'error.invalid_imdb_link_note'
            : data.code === 2
            ? 'error.request_torrent_group_exists_note'
            : 'error.imdb_unknown_error',
          data.code === 2 && { groupId }
        )
        return
      }
      data = data.response
      errorMessage.text('')
      if (data.Title) {
        $('#title').val(data.Title)
      }
      if (data.SubTitle) {
        $('#subtitle').val(data.SubTitle)
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
      for (var i = 0; i < artists.length; i++) {
        var artistid, importanceid, artistimdbid, artist_cname
        if (i) {
          artistid = '#artist_' + i
          importanceid = '#importance_' + i
          artistimdbid = '#artist_id_' + i
          artist_cname = '#artist_chinese_' + i
          globalapp.requestAddArtistField(true)
        } else {
          artistid = '#artist'
          importanceid = '#importance'
          artistimdbid = '#artist_id'
          artist_cname = '#artist_chinese'
        }
        $(artistid).val(artists[i])
        $(importanceid).val(importance[i])
        $(artistimdbid).val(artist_ids[i])
        if (data.ChineseName) {
          $(artist_cname).val(data.ChineseName[[artists[i]]])
        }
      }
    },
    dataType: 'json',
  })
}
