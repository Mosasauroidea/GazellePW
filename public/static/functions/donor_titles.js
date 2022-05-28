$(document).ready(function () {
  if ($('#donor_title_prefix_preview').size() === 0) {
    return
  }
  $('#donor_title_prefix_preview').text(
    $('#input-donor_title_prefix').val().trim() + ' '
  )
  $('#donor_title_suffix_preview').text(
    ' ' + $('#input-donor_title_suffix').val().trim()
  )

  if ($('#input-donor_title_comma').attr('checked')) {
    $('#donor_title_comma_preview').text('')
  } else {
    $('#donor_title_comma_preview').text(', ')
  }

  $('#input-donor_title_prefix').keyup(function () {
    if ($(this).val().length <= 30) {
      $('#donor_title_prefix_preview').text($(this).val().trim() + ' ')
    }
  })

  $('#input-donor_title_suffix').keyup(function () {
    if ($(this).val().length <= 30) {
      $('#donor_title_suffix_preview').text(' ' + $(this).val().trim())
    }
  })

  $('#input-donor_title_comma').change(function () {
    if ($(this).attr('checked')) {
      $('#donor_title_comma_preview').text('')
    } else {
      $('#donor_title_comma_preview').text(', ')
    }
  })
})
function previewColorUsername() {
  if ($('#limitedcolor').size()) {
    color = $('#limitedcolor').val().match('#[0-9a-fA-F]{6}')
    if (color) {
      $('#preview_color_username').css('color', color[0])
    }
  }
  if ($('#unlimitedcolor').size()) {
    color = $('#unlimitedcolor').val().match('#[0-9a-fA-F]{6}')
    if (color) {
      $('#preview_color_username').removeAttr('style')
      $('#preview_color_username').css('color', color[0])
    }
  }
  if ($('#gradientscolor').size()) {
    color = $('#gradientscolor')
      .val()
      .match('#[0-9a-fA-F]{6}(,#[0-9a-fA-F]{6}){1,2}')
    if (color) {
      $('#preview_color_username').removeAttr('style')
      $('#preview_color_username').css({
        'background-image': '-webkit-linear-gradient(left,' + color[0] + ')',
        '-webkit-background-clip': 'text',
        '-webkit-text-fill-color': 'transparent',
      })
    }
  }
}
$(document).ready(() => previewColorUsername())
