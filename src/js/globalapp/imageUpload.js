function imgUpload(file = false) {
  UploadImage(file, (url) => {
    $('#image').val(url)
    $('#uploaded_img').attr('src', url)
  })
}
globalapp.imgUpload = imgUpload

function imgUploadFillBBCode(containerId, loadingTarget) {
  loadingTarget = document.querySelector(loadingTarget)
  UploadImage(
    false,
    (url) => {
      $('#' + containerId).val($('#' + containerId).val() + '[img]' + url + '[/img]')
    },
    {
      onBefore() {
        loadingTarget.classList.add('u-loading-isLoading')
        loadingTarget.disabled = true
      },
      onFinal() {
        loadingTarget.classList.remove('u-loading-isLoading')
        loadingTarget.disabled = false
      },
    }
  )
}
globalapp.imgUploadFillBBCode = imgUploadFillBBCode

async function imgCopy() {
  const value = $('#image').val()
  if (value) {
    await navigator.clipboard.writeText(value)
    alert(t('client.common.copied'))
  }
}
globalapp.imgCopy = imgCopy

async function upload(file, cb, { onBefore, onFinal } = {}) {
  try {
    const formData = new FormData()
    formData.append('file', file)
    onBefore && onBefore()
    const res = await fetch('upload.php?action=imgupload', {
      method: 'POST',
      body: formData,
    })
    const text = await res.text()
    var json = JSON.parse(text)
    if (json['msg']) {
      console.error(json['msg'])
    }
    cb(json['name'])
  } catch (err) {
    console.error(err)
  } finally {
    onFinal && onFinal()
  }
}
globalapp.upload = upload

function UploadImage(file, after = (url) => {}, { onBefore, onFinal } = {}) {
  var input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/gif,image/jpeg,image/jpg,image/png,image/svg'
  function up(f) {
    upload(
      f,
      (name) => {
        after(name)
      },
      { onBefore, onFinal }
    )
  }
  if (file) {
    up(file)
  } else {
    input.onchange = function () {
      file = input.files[0]
      up(file)
    }
    input.click()
  }
}
globalapp.UploadImage = UploadImage

function imgAllowDrop(ev) {
  ev.preventDefault()
}
globalapp.imgAllowDrop = imgAllowDrop

function imgDrop(event) {
  event.preventDefault()
  if (event.dataTransfer.files.length) {
    var file = event.dataTransfer.files[0]
    if (/image\/\w+/.test(file.type)) {
      imgUpload(file)
    }
  }
}
globalapp.imgDrop = imgDrop
