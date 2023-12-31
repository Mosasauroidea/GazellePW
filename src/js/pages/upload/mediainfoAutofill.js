import { isArray, isString, isBoolean } from 'lodash-es'
import Videoinfo from '#/js/modules/Videoinfo'

const SHOW = Symbol('show-action')

export default function mediainfoAutofill(text) {
  if (!text) {
    return
  }

  const info = Videoinfo.convertBBCode(text)
  if (!info) {
    return
  }

  const fields = toFields(info)

  $(`[name="subtitles[]"]`).prop('checked', false)
  const hasSubtitle = !fields['no_subtitles']
  if (hasSubtitle) {
    document.querySelector('#other_subtitles').classList.remove('hidden')
  }

  for (const [key, value] of Object.entries(fields)) {
    const selector = `#${key}`
    try {
      if (isString(value)) {
        // 当选择自动填充的时候才修改
        if ($(selector).val() === '' && !$(selector).prop('disabled')) {
          $(selector).val(value)
        }
      } else if (isBoolean(value)) {
        $(selector).prop('checked', value)
      } else if (value === SHOW) {
        $(selector).show()
      }
    } catch (error) {
      console.log('invalid selector: ' + error.message)
    }
  }

  // Unprocessed
  if ($('[name=processing]').val() === 'Untouched' && info.diskType) {
    $('[name=processing_other]').val(info.diskType)
  }

  $('[name=processing]')[0].dispatchEvent(new Event('change'))
  $('[name=codec]')[0].dispatchEvent(new Event('change'))
  $('[name=resolution]')[0].dispatchEvent(new Event('change'))
  $('[name=container]')[0].dispatchEvent(new Event('change'))

  $('.FormValidation')[0].validator.validate()
}

function toFields(info) {
  const fields = {
    // not auto filled: processing.
    source: info.source,
    codec: info.codec,
    container: info.container,
    ...toResolution(info),
    ...toLangs(info),
    ...toOption(info.videoOption),
    ...toOption(info.audioOption),
  }
  return fields
}

function toOption(option) {
  return Object.fromEntries(option.map((v) => [v, true]))
}

function toResolution(info) {
  if (isArray(info.resolution)) {
    const [width, height] = info.resolution
    return {
      resolution: 'Other',
      resolution_width: width,
      resolution_height: height,
    }
  } else {
    return {
      resolution: info.resolution,
    }
  }
}

function toLangs(info) {
  const langs = info.subtitles
    .map((v) => v.toLowerCase().replace(' ', '_'))
    .map((v) => (v === 'chinese' ? 'chinese_simplified' : v))
  const fields = Object.fromEntries(langs.map((v) => [v, true]))

  if (langs.length === 0) {
    fields['no_subtitles'] = true
  } else {
    fields['mixed_subtitles'] = true
  }

  return fields
}
