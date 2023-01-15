import MediainfoParser from './Mediainfo/MediainfoParser'
import MediainfoConverter from './Mediainfo/MediainfoConverter'
import BdinfoParser from './Bdinfo/BdinfoParser'
import BdinfoConverter from './Bdinfo/BdinfoConverter'
import { removeMediainfoTag } from './utils'
import { VideinfoTableSpaceError } from './errors'

export default class Videoinfo {
  static validateCompleteNameRequired(text) {
    text = preProcess(text)
    return Boolean(text.match(/(Complete name\s*:|Disc (Title|Label)\s*:)/i))
  }

  static validateMediaInfo(text) {
    try {
      const info = Videoinfo.convertBBCode(text)
      if (info.codec && info.container && info.resolution) {
        return true
      }
    } catch (err) {
      return false
    }
    return false
  }

  static validateTableSpace(text) {
    try {
      Videoinfo.convertBBCode(text)
    } catch (err) {
      if (err instanceof VideinfoTableSpaceError) {
        return false
      }
      return true
    }
    return true
  }

  static getType(text) {
    text = preProcess(text)
    return text.match(/Disc (Title|Label)\s*:/i) ? 'bdinfo' : text.match(/Complete name\s*:/i) ? 'mediainfo' : null
  }

  static convertBBCode(text) {
    text = preProcess(text)
    text = removeMediainfoTag(text)
    const type = this.getType(text)
    switch (type) {
      case 'mediainfo': {
        const info = new MediainfoParser().parse(text)
        if (!info) {
          return
        }
        const fields = new MediainfoConverter().convert(info)
        console.log('mediainfo', { info, fields })
        return fields
      }
      case 'bdinfo': {
        const info = new BdinfoParser().parse(text)
        if (!info) {
          return
        }
        return new BdinfoConverter().convert(info)
      }
      default:
        console.error('mediainfo unknown type, no Disc Title/Label or Complete name')
        return null
    }
  }
}

function preProcess(text) {
  const replaces = [
    { from: '\u2002', to: ' ' },
    { from: '\u200d', to: '' },
  ]
  for (const { from, to } of replaces) {
    text = text.replaceAll(from, to)
  }
  return text
}
