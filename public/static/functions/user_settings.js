var PUSHOVER = 5
var TOASTY = 4
var PUSHBULLET = 6

$(document).ready(function () {
  var top =
    $('#settings_sections').offset().top -
    parseFloat($('#settings_sections').css('marginTop').replace(/auto/, 0))

  $(window).scroll(function (event) {
    if (window.innerWidth < 1024) {
      return
    }
    var y = $(this).scrollTop()
    if (y >= top) {
      $('#settings_sections').addClass('fixed')
    } else {
      $('#settings_sections').removeClass('fixed')
    }
  })

  $('#settings_sections li').each(function (index) {})

  $('#settings_search').on('keyup', function () {
    var search = $(this).val().toLowerCase()
    if ($.trim(search).length > 0) {
      $('#userform tr')
        .not('.Table-rowHeader')
        .each(function (index) {
          var text = $(this).find('td:first').text().toLowerCase()
          if (
            text.length > 0 &&
            search.length > 0 &&
            fuzzyMatch(text, search)
          ) {
            $(this).show()
          } else {
            $(this).hide()
          }
        })
    } else {
      $('#userform tr').show()
    }
  })

  // I'm sure there is a better way to do this but this will do for now.
  $('#notifications_Inbox_traditional').click(function () {
    $('#notifications_Inbox_popup').prop('checked', false)
  })
  $('#notifications_Inbox_popup').click(function () {
    $('#notifications_Inbox_traditional').prop('checked', false)
  })
  $('#notifications_Torrents_traditional').click(function () {
    $('#notifications_Torrents_popup').prop('checked', false)
  })
  $('#notifications_Torrents_popup').click(function () {
    $('#notifications_Torrents_traditional').prop('checked', false)
  })

  if ($('#pushservice').val() > 0) {
    $('.pushdeviceid').hide()
    $('#pushsettings').show()
    if ($('#pushservice').val() == PUSHBULLET) {
      fetchPushbulletDevices($('#pushkey').val())
      $('.pushdeviceid').show()
    }
  }

  $('#pushservice').change(function () {
    if ($(this).val() > 0) {
      $('#pushsettings').show(500)

      if ($(this).val() == TOASTY) {
        $('#pushservice_title').text('Device ID')
      } else if ($(this).val() == PUSHOVER) {
        $('#pushservice_title').text('User Key')
      } else {
        $('#pushservice_title').text('API Key')
      }
    } else {
      $('#pushsettings').hide(500)
    }

    if ($(this).val() == PUSHBULLET) {
      fetchPushbulletDevices($('#pushkey').val())
      $('.pushdeviceid').show(500)
    } else {
      $('.pushdeviceid').hide(500)
    }
  })

  $('#pushkey').blur(function () {
    if ($('#pushservice').val() == PUSHBULLET) {
      fetchPushbulletDevices($(this).val())
    }
  })
})

function fuzzyMatch(str, pattern) {
  pattern = pattern.split('').reduce(function (a, b) {
    return a + '.*' + b
  })
  return new RegExp(pattern).test(str)
}

/**
 * Gets device IDs from the pushbullet API
 *
 * @return array of dictionaries with devices
 */
function fetchPushbulletDevices(apikey) {
  $.ajax({
    url: 'ajax.php',
    data: {
      action: 'pushbullet_devices',
      apikey: apikey,
    },
    type: 'GET',
    success: function (data, textStatus, xhr) {
      var data = jQuery.parseJSON(data)
      var field = $('#pushdevice')
      var value = field.val()
      if (data.error || textStatus !== 'success') {
        if (data.error) {
          field.html(
            '<option class="Select-option">' + data.error.message + '</option>'
          )
        } else {
          $('#pushdevice').html(
            '<option class="Select-option">No devices fetched</option>'
          )
        }
      } else {
        if (data['devices'].length > 0) {
          field.html('')
        }
        for (var i = 0; i < data['devices'].length; i++) {
          var model = data['devices'][i]['extras']['model']
          var nickname = data['devices'][i]['extras']['nickname']
          var name = nickname !== undefined ? nickname : model
          var option = new Option(name, data['devices'][i]['iden'])

          option.selected = option.value == value
          field[0].add(option)
        }
      }
    },
    error: function (data, textStatus, xhr) {
      $('#pushdevice').html(
        '<option class="Select-option">' + textStatus + '</option>'
      )
    },
  })
}
