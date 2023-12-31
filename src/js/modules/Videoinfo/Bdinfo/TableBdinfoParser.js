import Debug from 'debug'
import { chunk, zipObject } from 'lodash-es'
import { splitIntoLines, splitIntoBDInfoSections } from '../utils'
import { VideinfoTableSpaceError } from '../errors'

const debug = Debug('mediainfo')

export default class TableBdinfoParser {
  parse(rawText) {
    let text = rawText
    if (!text.startsWith('DISC INFO:')) {
      text = `DISC INFO:\n\n${text}`
    }

    let sections = splitIntoBDInfoSections(text)
    sections = sections.filter((v) => {
      if (v.startsWith('(*)')) {
        return false
      }
      return true
    })
    const chunks = chunk(sections, 2)
    console.log({ chunks })
    debug('CHUNKS', chunks)

    const result = {
      'DISC INFO': {},
      'PLAYLIST REPORT': {},
      VIDEO: [],
      AUDIO: [],
      SUBTITLES: [],
    }
    for (const [rawSectionName, sectionBody] of chunks) {
      const sectionName = rawSectionName.replace(':', '').toUpperCase()
      if (['VIDEO', 'AUDIO', 'SUBTITLES'].includes(sectionName)) {
        const items = this.splitTable(sectionName, sectionBody)
        console.log('splitTable', { sectionName, items })
        result[sectionName] = items.map((v) => this.processItem(sectionName, v))
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
        const [resolution, frameRate, aspectRatio, ...rest] = item.Description.split(' / ')
        return {
          codec: item.Codec.replace(/ Video/, ''),
          bitrate: item.Bitrate,
          resolution,
          frameRate,
          aspectRatio,
          note: rest.join(' / ').trim(),
        }
      }
      case 'AUDIO': {
        const [channels, sampleRate, bitrate, ...rest] = item.Description.split(' / ')
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

  splitTable(name, text) {
    const result = []
    // eslint-disable-next-line no-unused-vars
    const [header, seperator, ...rest] = splitIntoLines(text)
    const names = header.split(/ {1,}/)
    if (names.length === 1) {
      throw new VideinfoTableSpaceError('BDInfoParser: Table空格格式不支持')
    }
    switch (name) {
      case 'VIDEO':
        for (const line of rest) {
          result.push(zipObject(names, line.match(/(.*?) *([0-9.\(\)]* kbps) *(.*)/).slice(1)))
        }
        break
      case 'AUDIO':
        for (const line of rest) {
          result.push(zipObject(names, line.match(/(.*?) *([a-zA-Z ]*[a-zA-Z]) *([0-9.]* kbps) *(.*)/).slice(1)))
        }
        break
      case 'SUBTITLES':
        for (const line of rest) {
          result.push(
            zipObject(names, line.match(/(Presentation Graphics) *([a-zA-Z ]*[a-zA-Z]) *([0-9.]* kbps) *(.*)/).slice(1))
          )
        }
        break
    }
    return result
  }
}
