import Debug from 'debug'
import { isArray } from 'lodash-es'
import { splitIntoLines, splitIntoSections } from '../utils'

const debug = Debug('mediainfo')

/*
 * new Mediainfo.parse(rawText)
 * #-> { general: { KEY: VALUE }, video: [..], .. }
 */
export default class MediainfoParser {
  parse(text) {
    const result = {
      general: {},
      video: [],
      audio: [],
      text: [],
      menu: {},
    }
    const sections = splitIntoSections(text)
    debug('SECTIONS', sections)
    for (const section of sections) {
      if (!section.match(/([a-zA-Z]+).*\n([\s\S]+)/)) {
        continue
      }
      const [rawSectionName, sectionBody] = section.match(/([a-zA-Z]+).*\n([\s\S]+)/).slice(1)
      const sectionName = rawSectionName.toLowerCase()
      const fields = this.extractFields({ sectionName, sectionBody })
      if (isArray(result[sectionName])) {
        result[sectionName].push(fields)
      } else {
        result[sectionName] = fields
      }
    }
    return result
  }

  extractFields({ sectionName, sectionBody }) {
    const result = {}
    const lines = splitIntoLines(sectionBody)
    for (const line of lines) {
      const found = line.match(/^(.*?):(.*)$/)
      if (!found) {
        console.log({ line, lines })
      }
      const [key, value] = found.slice(1).map((v) => v.trim())
      result[key.toLowerCase()] = value
    }
    return result
  }
}
