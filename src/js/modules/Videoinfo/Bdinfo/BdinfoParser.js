import SummaryBdinfoParser from './SummaryBdinfoParser'
import TableBdinfoParser from './TableBdinfoParser'

/*
Formats
  - table format
  - summary format

# Full text 

  Disc Title:     LIANG BO
  VIDEO:
  Codec                   Bitrate             Description     
  -----                   -------             -----------     
  MPEG-4 AVC Video        0 kbps              1080p / 23.976 fps / 16:9 / High Profile 4.1

  QUICK SUMMARY:
  Disc Title: LIANG BO
  Video: MPEG-4 AVC Video / 0 kbps / 1080p / 23.976 fps / 16:9 / High Profile 4.1
*/

const FORMAT = {
  SUMMARY: 'SUMMARY',
  SUMMARY2: 'SUMMARY2',
  TABLE: 'TABLE',
}

export default class BdinfoParser {
  parse(text) {
    const format = this.identifyFormat(text)
    switch (format) {
      case FORMAT.SUMMARY:
      case FORMAT.SUMMARY2: {
        let newText = text
        if (format === FORMAT.SUMMARY) {
          newText = this.extractSummary(text)
        }
        return new SummaryBdinfoParser().parse(newText)
      }
      case FORMAT.TABLE:
        return new TableBdinfoParser().parse(text)
      default:
        throw new Error('BDInfo未知格式:', text)
    }
  }

  identifyFormat(text) {
    return text.match(/QUICK SUMMARY:/)
      ? FORMAT.SUMMARY
      : text.match(/VIDEO:/)
      ? FORMAT.TABLE
      : text.match(/Video:/)
      ? FORMAT.SUMMARY2
      : null
  }

  extractSummary(rawText) {
    // for compact summary
    const text = `${rawText}\n\n`
    return text
      .replace(/\n\r/, '\n')
      .match(/QUICK SUMMARY:([\s\S]+?)\n\s*\n/)[1]
  }
}
