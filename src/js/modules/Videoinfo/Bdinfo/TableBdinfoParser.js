import Debug from 'debug'
import { chunk, zipObject } from 'lodash-es'
import { splitIntoLines, splitIntoSections } from '../utils'
import { VideinfoTableSpaceError } from '../errors'

const debug = Debug('mediainfo')

export default class TableBdinfoParser {
  parse(rawText) {
    let text = rawText
    if (!text.startsWith('DISC INFO:')) {
      text = `DISC INFO:\n\n${text}`
    }

    const sections = splitIntoSections(text)
    const chunks = chunk(sections, 2)
    // console.log({ chunks })
    debug('CHUNKS', chunks)

    const result = {
      'DISC INFO': {},
      'PLAYLIST REPORT': {},
      VIDEO: [],
      AUDIO: [],
      SUBTITLES: [],
    }
    for (const [rawSectionName, sectionBody] of chunks) {
      const sectionName = rawSectionName.replace(':', '')
      if (
        [
          'VIDEO',
          'AUDIO',
          'SUBTITLES',
          'FILES',
          'CHAPTERS',
          'STREAM DIAGNOSTICS',
        ].includes(sectionName)
      ) {
        const items = this.splitTable(sectionBody)
        console.log('splitTable', { sectionName, items })
        result[sectionName] = items.map((v) => this.processItem(sectionName, v))
      } else {
        const lines = splitIntoLines(sectionBody)
        for (const line of lines) {
          const [key, value] = line.match(/^(.+?):(.*)$/).slice(1)
          result[sectionName][key] = this.processValue(key, value.trim())
        }
      }
    }
    return {
      ...result['DISC INFO'],
      ...result['PLAYLIST REPORT'],
      Video: result.VIDEO,
      Audio: result.AUDIO,
      Subtitle: result.SUBTITLES,
    }
  }

  processValue(key, value) {
    switch (key) {
      case 'Disc Size':
      case 'Size':
        return parseInt(value.replace(/,/g, '').replace('bytes', '').trim())
      case 'Length':
        return value.replace('(h:m:s.ms)', '').trim()
      default:
        return value
    }
  }

  processItem(sectionName, item) {
    switch (sectionName) {
      case 'VIDEO': {
        const [resolution, frameRate, aspectRatio, note] =
          item.Description.split(' / ')
        return {
          codec: item.Codec.replace(/ Video/, ''),
          bitrate: item.Bitrate,
          resolution,
          frameRate,
          aspectRatio,
          note,
        }
      }
      case 'AUDIO': {
        const [channels, sampleRate, bitrate, ...rest] =
          item.Description.split(' / ')
        return {
          codec: item.Codec,
          language: item.Language,
          channels: channels.trim(),
          sampleRate: sampleRate.trim(),
          bitrate: bitrate.trim(),
          note: rest.join(' / ').trim(),
        }
      }
      case 'SUBTITLES': {
        return {
          language: item.Language,
          bitrate: item.Bitrate,
          codec: item.Codec,
        }
      }
    }
  }

  splitTable(text) {
    const result = []
    // eslint-disable-next-line no-unused-vars
    const [header, seperator, ...rest] = splitIntoLines(text)
    const names = header.split(/ {5,}/)
    if (names.length === 1) {
      throw new VideinfoTableSpaceError('BDInfoParser: Table空格格式不支持')
    }
    for (const line of rest) {
      const cells = line.split(/ {5,}/)
      result.push(zipObject(names, cells))
    }
    return result
  }
}
