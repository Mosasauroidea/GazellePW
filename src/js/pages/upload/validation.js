import Pristine from '#/js/forked/pristine'
import Videoinfo from '#/js/modules/Videoinfo'

const SOURCE_HAS_NO_PROCESSING = ['', 'TV', 'HDTV', 'WEB']
const DVD = ['DVD']
const BD = ['Blu-ray']
const SELECT_HAS_OTHER_INPUT = ['Other']
const SELECT_REQUIRED = ['', '---']
const IMDB_ID_PATTERN = /tt\d+/
const IMAGE_HOSTS = window.DATA['IMAGE_HOST_WHITELIST']

document.addEventListener('DOMContentLoaded', () => {
  registerValidation()
})

export function registerValidation() {
  const form = document.querySelector('.FormValidation')

  const validator = new Pristine(form, {
    classTo: 'Form-errorContainer',
    errorClass: 'form-invalid',
    successClass: 'form-valid',
    errorTextParent: 'Form-errorContainer',
    errorTextTag: 'div',
    errorTextClass: 'Form-errorMessage',
  })

  form.validator = validator

  form.onsubmit = function (e) {
    const valid = validator.validate()
    document.querySelector('.form-invalid .Form-errorMessage')?.classList.remove('animate__animated', 'animate__flash')
    if (valid) {
      $('input:disabled, select:disabled').prop('disabled', false)
      $('#post').addClass('is-loading').prop('disabled', true)
    } else {
      document.querySelector('.form-invalid').scrollIntoView()
      document
        .querySelector('.form-invalid .Form-errorMessage')
        .classList.add('animate__animated', 'animate__flash', 'animate__repeat-3')
    }
    return valid
  }

  const { addValidator, addValidatorSelectInput } = createValidator({
    validator,
  })

  handleSelectInput({ watch: true, apply: true })

  addValidatorSelectInput({
    selector: `[name="releasetype"]`,
    validate: validateSelectInputRequired,
    messageKey: 'client.upload.releasetype_required',
  })

  handleSourceAndProcessing()
  addValidatorSelectInput({
    selector: `[name="source"]`,
    validate: validateSelectInputRequired,
    messageKey: 'client.upload.source_required',
  })
  addValidatorSelectInput({
    selector: `[name="processing"]`,
    validate: validateProcessing,
    messageKey: 'client.upload.processing_required',
  })

  handleResolution()

  handleSubtitle()
  addValidator({
    selector: `[name="artist_ids[]"], [name="artists[]"],[name="importance[]"]`,
    validate: validateArtists,
    messageKey: 'client.upload.at_least_one_director',
  })
  addValidator({
    selector: `[name="subtitles[]"], [name=subtitle_type]`,
    validate: validateSubtitle,
    messageKey: 'client.upload.subtitles_required',
  })
  addValidator({
    selector: `[name="subtitles[]"], [name=subtitle_type]`,
    validate: validateSubtitleWithMediainfo,
    messageKey: 'client.upload.subtitles_with_mediainfo',
  })

  addValidator({
    selector: `[name="file_input"]`,
    validate: validateRequired,
    messageKey: 'client.upload.torrent_file_required',
  })
  addValidator({
    selector: `[name="tags"]`,
    validate: validateRequired,
    messageKey: 'client.upload.tag_required',
  })
  addValidator({
    selector: `[name="imdb"]`,
    validate: validateImdb,
    messageKey: 'client.upload.imdb_link_required',
  })
  addValidator({
    selector: `[name="title"]`,
    validate: validateRequired,
    messageKey: 'client.upload.movie_title_required',
  })
  addValidator({
    selector: `[name="year"]`,
    validate: validateRequired,
    messageKey: 'client.upload.year_required',
  })
  addValidator({
    selector: `[name="image"]`,
    validate: validateRequired,
    messageKey: 'client.upload.poster_required',
  })
  addValidator({
    selector: `[name="desc"], [name="maindesc"]`,
    validate: validateDesc,
    messageKey: 'client.upload.movie_desc_required',
  })

  addValidator({
    selector: `[name="mediainfo[]"]`,
    validate: validateMediainfoRequired,
    messageKey: 'client.upload.mediainfo_required',
  })
  addValidator({
    selector: `[name="mediainfo[]"]`,
    validate: wrap(Videoinfo.validateCompleteNameRequired),
    messageKey: 'client.upload.mediainfo_complete_name_required',
  })
  addValidator({
    selector: `[name="mediainfo[]"]`,
    validate: wrap(Videoinfo.validateTableSpace),
    messageKey: 'client.upload.mediainfo_table_space',
  })

  addValidator({
    selector: `[name="mediainfo[]"]`,
    validate: wrap(Videoinfo.validateMediaInfo),
    messageKey: 'client.upload.mediainfo_valid_format',
  })

  addValidator({
    selector: `[name="release_desc"]`,
    validate: validateDescImg3Png,
    messageKey: 'client.upload.desc_img_3_png',
  })
  addValidator({
    selector: `[name="release_desc"]`,
    validate: validateDescImgHosts,
    messageKey: 'client.upload.desc_img_hosts',
  })

  addValidatorSelectInput({
    selector: `[name="codec"]`,
    validate: validateSelectInputRequired,
    messageKey: 'client.upload.codec_required',
  })
  addValidatorSelectInput({
    selector: `[name="container"]`,
    validate: validateSelectInputRequired,
    messageKey: 'client.upload.container_required',
  })
  addValidatorSelectInput({
    selector: `[name="resolution"]`,
    validate: validateSelectInputRequired,
    messageKey: 'client.upload.resolution_required',
  })
  addValidator({
    selector: '[name=movie_edition_information], [name=remaster_title_show], [name=remaster_custom_title]',
    validate: validateRemaster,
    messageKey: 'client.upload.remaster_required',
  })
}

function validateRequired(value) {
  if (this.type === 'radio' || this.type === 'checkbox') {
    return this.pristine.self.form.querySelectorAll('input[name="' + this.getAttribute('name') + '"]:checked').length
  } else {
    return Boolean(value)
  }
}

function validateSelectInputRequired({ select, inputs }) {
  if (SELECT_REQUIRED.includes(select.value) && !select.disabled) {
    return false
  } else if (
    SELECT_HAS_OTHER_INPUT.includes(select.value) &&
    // input.disabled无效: 因为设置disabled和validate同时发生
    !inputs.every((v) => Boolean(v.value))
  ) {
    return false
  } else {
    return true
  }
}

function validateDesc() {
  const desc = document.querySelector('[name=desc]').value
  const mainDesc = document.querySelector('[name=maindesc]').value
  if (desc || mainDesc) {
    return true
  }
  return false
}

function validateProcessing({ select, inputs }) {
  const source = document.querySelector('[name=source]').value

  if (SOURCE_HAS_NO_PROCESSING.includes(source)) {
    return true
  }

  if (SELECT_REQUIRED.includes(select.value) && !select.disabled) {
    return false
  } else if (
    [...DVD, ...BD].includes(source) &&
    ['Untouched'].includes(select.value) &&
    !inputs.every((v) => Boolean(v.value))
  ) {
    return false
  } else {
    return true
  }
}

export function validateMediainfoRequired(value) {
  return Boolean(value)
}

function wrap(validate) {
  return function wrapValidate(value) {
    if (!value) {
      return true
    }
    return validate(value)
  }
}

export function validateDescImg3Png(value) {
  if (!value) {
    return false
  }
  const matches = value.match(/\[img=.*?png\s*]|\[img\].*?png\s*\[\/img\]/gi)
  if (!matches) {
    return false
  }
  return new Set(matches).size >= 3
}

export function validateDescImgHosts(value) {
  if (!value) {
    return true
  }
  const matches = [...value.matchAll(/(\[img=(.*?)]|\[img\](.*?)\[\/img\])/gi)]
  const pattern = `(${IMAGE_HOSTS.join('|')})/`
  let count = 0
  if (matches) {
    for (const match of matches) {
      const img = match[2] || match[3]
      if (img.match(pattern)) {
        count++
      }
    }
  }
  return count >= 3
}

export function validateDescComparison(value) {
  if (!value) {
    return true
  }
  const matches = [...value.matchAll(/\[comparison.*?\]([\s\S]*?)\[\/comparison\]/gi)]
  const pattern = `(${IMAGE_HOSTS.join('|')}).*?png`
  if (matches) {
    for (const match of matches) {
      const lines = match[1]
        .replace(/\n\r/, '\n')
        .split(/\n/)
        .map((v) => v.trim())
        .filter((v) => v.length > 0)
      console.log('validate desc comparison', { lines })
      const isOk = lines.every((line) => line.match(pattern))
      if (!isOk) {
        return false
      }
    }
  }
  return true
}

function validateImdb(value) {
  if ((value && value.match(IMDB_ID_PATTERN)) || $('[name=no_imdb_link]').prop('checked')) {
    return true
  } else {
    return false
  }
}

function validateRemaster() {
  const notMainMovie = $('[name=not_main_movie]').prop('checked')
  const remasterTitleShow = $('[name=remaster_title_show]').val()
  const remasterCustomTitle = $('[name=remaster_custom_title]').val()
  if (notMainMovie && !(remasterTitleShow.match(/额外内容/) || remasterCustomTitle)) {
    return false
  }
  return true
}

function createValidator({ validator }) {
  const form = validator.form
  return {
    addValidator({ selector, validate, messageKey }) {
      const inputs = Array.from(form.querySelectorAll(selector))
      const message = t(messageKey)
      for (const input of inputs) {
        validator.addValidator(input, validate, message)
      }
    },

    addValidatorSelectInput({ selector, validate, messageKey }) {
      const message = t(messageKey)
      const select = form.querySelector(selector)
      if (!select) {
        return
      }
      let inputs = []
      const nextEl = select.nextElementSibling
      if (nextEl) {
        inputs = ['INPUT', 'SELECT'].includes(nextEl.tagName) ? [nextEl] : Array.from(nextEl.querySelectorAll('input'))
      }
      for (const elem of [select, ...inputs]) {
        validator.addValidator(elem, () => validate({ select, inputs }), message)
      }
    },
  }
}

export function handleSourceAndProcessing() {
  const validator = document.querySelector('.FormValidation').validator
  const source = document.querySelector('[name=source]')
  const processing = document.querySelector('[name=processing]')
  const processingContainer = document.querySelector('#processing-container')
  const processingOther = document.querySelector('[name=processing_other]')

  function handle() {
    if (SOURCE_HAS_NO_PROCESSING.includes(source.value)) {
      processing.value = ''
      processingContainer.classList.add('hidden')
    } else {
      processingContainer.classList.remove('hidden')
    }

    // processing other
    if (processing.value === 'Untouched' && [...DVD, ...BD].includes(source.value)) {
      processingOther.disabled = false
      processingOther.classList.remove('hidden')
      const showSelector = BD.includes(source.value) ? '.bd' : '.dvd'
      const hideSelector = BD.includes(source.value) ? '.dvd' : '.bd'
      for (const show of processingOther.querySelectorAll(showSelector)) {
        show.classList.remove('hidden')
      }
      for (const hide of processingOther.querySelectorAll(hideSelector)) {
        hide.classList.add('hidden')
      }
    } else {
      processingOther.classList.add('hidden')
      processingOther.value = ''
      processingOther.disabled = true // for validation
    }
  }

  // for ?
  // handle()

  source.addEventListener('change', (e) => {
    handle()
    validator.validate(processing)
  })

  processing.addEventListener('change', (e) => {
    handle()
    validator.validate(source)
  })
}

function handleResolution() {
  document.querySelector(`[name=resolution]`).addEventListener('change', () => {
    document.querySelector(`[name=resolution_width]`).value = ''
    document.querySelector(`[name=resolution_height]`).value = ''
  })
}

function handleSubtitle() {
  for (const el of document.querySelectorAll(`[name=subtitle_type]`)) {
    el.addEventListener('change', (e) => {
      const value = e.target.getAttribute('data-value')
      const disabled = value === 'no-sub'
      if (disabled) {
        $('#other_subtitles').addClass('hidden')
      } else {
        $('#other_subtitles').removeClass('hidden')
      }
      for (const sub of document.querySelectorAll(`[name="subtitles[]"]`)) {
        sub.disabled = disabled
        if (disabled) {
          sub.checked = false
        }
      }
    })
  }
}
function validateSubtitle() {
  const form = this.pristine.self.form
  const subtitleType = Array.from(form.querySelectorAll(`[name="subtitle_type"]:checked`))[0]
  if (!subtitleType) {
    return false
  }
  const type = subtitleType.getAttribute('data-value')
  if (type !== 'no-sub') {
    if (Array.from(form.querySelectorAll(`[name="subtitles[]"]:checked`)).length === 0) {
      return false
    }
  }
  return true
}

function validateArtists() {
  const artist_ids = document.querySelectorAll('[name="artist_ids[]"]')
  const artists = document.querySelectorAll('[name="artists[]"]')
  const importances = document.querySelectorAll('[name="importance[]"]')
  let hasDirector = false
  for (var i = 0; i < artist_ids.length; i++) {
    if (importances[i].value == 1 && (artist_ids[i].value || artists[i].value)) {
      hasDirector = true
    }
  }
  if (hasDirector) {
    return true
  }

  return false
}

function validateSubtitleWithMediainfo() {
  const checkedSubtitles = Array.from(document.querySelectorAll(`[name="subtitles[]"]:checked`))
  if (checkedSubtitles.length > 0) {
    return true
  }

  const mediainfo = document.querySelector('[name="mediainfo[]"]').value
  if (!mediainfo) {
    return true
  }

  const info = Videoinfo.convertBBCode(mediainfo)
  if (!info) {
    return true
  }

  if (info.subtitles.length > 0) {
    return false
  }

  return true
}

/*
 * <div class="SelectInput">
 *   <select>
 *   <NEXT_SIBLING style="visibility: hidden;">
 * </div>
 */
export function handleSelectInput({ watch, apply }) {
  function toggleVisible(select, other) {
    if (SELECT_HAS_OTHER_INPUT.includes(select.value)) {
      other.classList.remove('hidden')
      other.disabled = false // for validation
    } else if (other != null) {
      other.classList.add('hidden')
      other.disabled = true
    }
  }

  for (const selectInput of Array.from(document.querySelectorAll('.SelectInput'))) {
    const select = selectInput.querySelector('select')
    const input = select.nextElementSibling
    if (watch) {
      select.addEventListener('change', (e) => toggleVisible(select, input))
    }
    if (apply) {
      toggleVisible(select, input)
    }
  }
}
