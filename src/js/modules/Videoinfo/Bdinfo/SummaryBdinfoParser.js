import Debug from 'debug'
import { isArray } from 'lodash-es'
import { splitIntoLines } from '../utils'

const debug = Debug('mediainfo')

export default class SummaryBdinfoParser {
  parse(text) {
    const lines = splitIntoLines(text)
    const result = {
      Video: [],
      Audio: [],
      Subtitle: [],
    }
    debug('LINES', lines)
    for (const line of lines) {
      // (*) Indicates included stream hidden by this playlist.
      if (line.startsWith('* ')) {
        continue
      }
      const found = line.match(/^([\w ]+): (.*)$/)
      if (!found) {
        throw new Error(`Line is not a 'A: B' format -- '${line}'`)
      }
      const [key, value] = found.slice(1)
      const newValue = this.processValue(key, value.trim())
      if (isArray(result[key])) {
        result[key].push(newValue)
      } else {
        result[key] = newValue
      }
    }
    return result
  }

  processValue(key, value) {
    switch (key) {
      case 'Disc Size':
      case 'Size':
        return parseInt(value.replace(/,/g, '').replace('bytes', '').trim())
      case 'Video': {
        const [codec, bitrate, resolution, frameRate, aspectRatio, note] =
          value.split(' / ')
        return {
          codec: codec.replace(/ Video/, ''),
          bitrate,
          resolution,
          frameRate,
          aspectRatio,
          note,
        }
      }
      case 'Audio': {
        const [language, codec, channels, sampleRate, bitrate, ...rest] =
          value.split(' / ')
        return {
          language,
          codec,
          channels,
          sampleRate,
          bitrate,
          note: rest.join(' / ').trim(),
        }
      }
      case 'Subtitle': {
        const [language, bitrate] = value.split(' / ')
        return {
          language,
          bitrate,
        }
      }
      default:
        return value
    }
  }
}
