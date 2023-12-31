import { uniq } from 'lodash-es'
import { calcDiskType, AUDIO_OPTION, VIDEO_OPTION } from '../utils'

export default class BdinfoConverter {
  convert(info) {
    const container = 'm2ts'
    const source = 'Blu-ray'
    const codec = this.extractCodec(info)
    const processing = 'Untouched'
    const diskType = calcDiskType(info['Disc Size'])
    const resolution = this.extractResolution(info)
    const subtitles = this.extractSubtitle(info)
    const videoOption = this.extractVideoOption(info)
    const audioOption = this.extractAudioOption(info)
    return {
      source,
      codec,
      processing,
      resolution,
      container,
      subtitles,
      diskType,
      videoOption,
      audioOption,
    }
  }

  extractResolution(info) {
    return info['Video'][0]['resolution']
  }

  extractSubtitle(info) {
    return uniq(info['Subtitle'].map((v) => v.language))
  }

  extractVideoOption(info) {
    let options = new Set()
    for (const v of info.Video) {
      if (v.note.match(/Dolby Vision/)) {
        options.add(VIDEO_OPTION.DOLBYVISION)
      }
      if (v.note.match(/HDR10\+/)) {
        options.add(VIDEO_OPTION.HDR10PLUS)
      }
      if (v.note.match(/HDR/)) {
        options.add(VIDEO_OPTION.HDR10)
      }
      if (v.note.match(/10 bits/)) {
        options.add(VIDEO_OPTION.BIT10)
      }
    }
    return Array.from(options)
  }

  extractAudioOption(info) {
    let options = new Set()
    for (const a of info.Audio) {
      if (a.channels.match(/5\.1/)) {
        options.add(AUDIO_OPTION.CHANNEL51)
      }
      if (a.channels.match(/7\.1/)) {
        options.add(AUDIO_OPTION.CHANNEL71)
      }
      if (a.codec.match(/Atmos/)) {
        options.add(AUDIO_OPTION.DOLBYATMOS)
      }
      if (a.codec.match(/DTS\:X/)) {
        options.add(AUDIO_OPTION.DTSX)
      }
    }
    return Array.from(options)
  }

  extractCodec(info) {
    const codec = info.Video[0].codec
    return codec.match(/AVC/) ? 'H.264' : codec.match(/(HEVC|H256)/) ? 'H.265' : 'Other'
  }
}
