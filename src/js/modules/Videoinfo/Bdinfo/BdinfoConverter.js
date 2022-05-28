import { uniq } from 'lodash-es'
import { calcDiskType } from '../utils'

export default class BdinfoConverter {
  convert(info) {
    const container = 'm2ts'
    const source = 'Blu-ray'
    const codec = this.extractCodec(info)
    const processing = 'Untouched'
    const diskType = calcDiskType(info['Disc Size'])
    const resolution = this.extractResolution(info)
    const subtitles = this.extractSubtitle(info)
    return {
      source,
      codec,
      processing,
      resolution,
      container,
      subtitles,
      diskType,
    }
  }

  extractResolution(info) {
    return info['Video'][0]['resolution']
  }

  extractSubtitle(info) {
    return uniq(info['Subtitle'].map((v) => v.language))
  }

  extractCodec(info) {
    const codec = info.Video[0].codec
    return codec.match(/AVC/)
      ? 'H.264'
      : codec.match(/(HEVC|H256)/)
      ? 'H.265'
      : 'Other'
  }
}
