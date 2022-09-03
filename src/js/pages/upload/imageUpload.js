document.addEventListener('DOMContentLoaded', () => {
  $('#image_upload_choose_file').on('click', function () {
    $('#imageupload').trigger('click')
  })

  $('#image_uploader_cancel').on('click', function () {
    reset()
  })

  $('#image_host_body_bbcode a').on('click', function () {
    copy($('#image_host_body_bbcode .ImageHost-linkText'))
    return false
  })

  $('#image_host_body_link a').on('click', function () {
    copy($('#image_host_body_link .ImageHost-linkText'))
    return false
  })

  const threshold = 90
  $('#imageupload').fileupload({
    dataType: 'json',
    previewMaxWidth: 180,
    previewMaxHeight: 180,
    loadImageMaxFileSize: 52428800,
    imageMaxWidth: 7680,
    imageMaxHeight: 4320,
    singleFileUploads: false,
    acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
    maxFileSize: 52428800,
    url: 'upload.php?action=imgupload',
    dropZone: $('.ImageHost-dropArea'),
    pasteZone: $('.ImageHost-dropArea'),
    progressall: function (e, data) {
      var progress = parseInt((data.loaded / data.total) * 100, 10)
      if (progress > threshold) {
        progress = threshold
      }
      $('.ImageHost-progress').gshow()
      $('.ImageHost-progressBar').css('width', progress + '%')
      $('#image_host_text').text(t('client.upload.uploading') + progress + '%')
    },
    add: function (e, data) {
      reset()
      var $this = $(this),
        that = $this.data('blueimp-fileupload') || $this.data('fileupload'),
        options = that.options
      data
        .process(function () {
          return $this.fileupload('process', data)
        })
        .done(function () {
          done(data)
        })
        .fail(function (data) {
          var error = ''
          data.files.forEach((file, index) => {
            if (file.error && !error) {
              error = file.error
            }
          })
          failed(error)
        })
    },
    fail: function (e) {
      failed('Unknown error')
    },
    done: function (e, data) {
      uploaded(e, data)
    },
  })
})

function reset() {
  $('#image_uploader_preview').empty()
  $('#image_uploader_preview').gshow()
  $('#image_host_result').ghide()
  $('#image_host_result').empty()
  $('#image_uploader_upload').unbind('click')
  $('.ImageHost-body').ghide()
  $('.ImageHost-progress').ghide()
  $('#image_uploader_upload').prop('disabled', true)
  $('#image_upload_choose_file').prop('disabled', false)
  $('#image_host_text').removeClass('u-colorWarning, u-colorSuccess').empty()
}
function uploading(data) {
  $('.ImageHost-body').ghide()
  $('#image_host_body_bbcode .ImagHost-linkText').empty()
  $('#image_host_body_link .ImagHost-linkText').empty()
  var jqXHR = data.submit()
  $('#image_uploader_cancel').click(function () {
    jqXHR.abort()
  })
  $('#image_upload_choose_file, #image_uploader_upload').prop('disabled', true)
}
function uploaded(e, data) {
  if (data.result.error) {
    $('#image_host_text').text(data.result.error).addClass('u-colorWarning')
  } else {
    let links = ''
    let bbcodes = ''
    data.result.files.forEach((file, index) => {
      links += file.name + '\n'
      bbcodes += '[img]' + file.name + '[/img]\n'
      $(
        '<img class="ImageHost-imageItem" onclick="lightbox.init(this, $(this).width());" src="' +
          file.name +
          '" class="scale_image">'
      ).appendTo($('#image_host_result'))
    })

    $('#image_host_body_link .ImageHost-linkText').val(links)
    $('#image_host_body_bbcode .ImageHost-linkText').val(bbcodes)
    $('.ImageHost-body').gshow()
    $('#image_uploader_preview').ghide()
    $('#image_host_result').gshow()
    $('#image_host_text').text(t('client.upload.uploaded')).addClass('u-colorSuccess')
    $('.ImageHost-progressBar').css('width', '100%')
  }
  $('#image_upload_choose_file').prop('disabled', false)
}

function failed(error) {
  $('#image_host_text').text(error).addClass('u-colorWarning')
  $('#image_uploader_upload').prop('disabled', false)
}

function done(data) {
  data.context = $('#image_uploader_upload').click(function () {
    uploading(data)
  })
  data.files.forEach((file, index) => {
    $('#image_uploader_preview').append(file.preview)
  })
  $('#image_uploader_upload').prop('disabled', false)
}

async function copy(textarea) {
  try {
    Snackbar.notify(t('client.upload.copied'))
    await navigator.clipboard.writeText(textarea.val())
  } catch (e) {
    console.log(e)
  }
}
