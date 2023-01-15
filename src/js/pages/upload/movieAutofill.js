globalapp.uploadMovieAutofill = function uploadMovieAutofill() {
  const target = document.querySelector('.Button.autofill')
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
    error: (err) => {
      globalapp.buttonSetLoading(target, false)
      globalapp.setFormError('client.common.imdb_unknown_error')
    },
    success: (data) => {
      globalapp.buttonSetLoading(target, false)
      globalapp.setFormError(null)
      if (data.code) {
        globalapp.setFormError(
          data.code === 1
            ? 'client.error.invalid_imdb_link_note'
            : data.code === 2
            ? 'client.error.torrent_group_exists_note'
            : 'client.error.imdb_unknown_error',
          data.code === 2 && { groupID: data.error.GroupID }
        )
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
      if (data.Plot) {
        $('#desc').val(data.Plot)
      }
      if (data.MainPlot) {
        $('#maindesc').val(data.MainPlot)
      }
      if (data.Production) {
        $('#remaster_record_label').val(data.Production.replace(/, ?/, ' / '))
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
      if (data.Writers) {
        Object.keys(data.Writers).map((k) => {
          artists.push(data.Writers[k])
          artist_ids.push(k)
          importance.push(2)
        })
      }
      if (data.Producers) {
        Object.keys(data.Producers).map((k) => {
          artists.push(data.Producers[k])
          artist_ids.push(k)
          importance.push(3)
        })
      }
      if (data.Composers) {
        Object.keys(data.Composers).map((k) => {
          artists.push(data.Composers[k])
          artist_ids.push(k)
          importance.push(4)
        })
      }
      if (data.Cinematographers) {
        Object.keys(data.Cinematographers).map((k) => {
          artists.push(data.Cinematographers[k])
          artist_ids.push(k)
          importance.push(5)
        })
      }
      if (data.Casts) {
        Object.keys(data.Casts).map((k) => {
          artists.push(data.Casts[k])
          artist_ids.push(k)
          importance.push(6)
        })
      }
      if (data.RestCasts) {
        Object.keys(data.RestCasts).map((k) => {
          artists.push(data.RestCasts[k])
          artist_ids.push(k)
          importance.push(6)
        })
      }
      globalapp.uploadRemoveAllArtistFields()
      for (var i = 0; i < artists.length; i++) {
        var artistid, importanceid, artistimdbid, artist_sub_name
        if (i) {
          artistid = '#artist_' + i
          importanceid = '#importance_' + i
          artistimdbid = '#artist_id_' + i
          artist_sub_name = '#artist_sub_' + i
          globalapp.uploadAddArtistField(true)
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
          $(artist_sub_name).prop('disabled', true)
        }
      }
      $('.FormValidation')[0].validator.validate()
      $('.FormUpload').addClass('u-formUploadAutoFilled')
      if (artists.length > 0) {
        $('.u-formUploadArtistList input:not([name="artists_sub[]"]), .u-formUploadArtistList select').prop(
          'disabled',
          true
        )
      }
      if (artists.length >= 5) {
        globalapp.uploadArtistsShowMore({ hide: true })
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
