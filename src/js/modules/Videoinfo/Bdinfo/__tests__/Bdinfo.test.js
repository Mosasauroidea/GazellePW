import BdinfoParser from '../BdinfoParser'
import BdinfoConverter from '../BdinfoConverter'

describe('all', () => {
  let info
  beforeAll(() => {
    info = new BdinfoParser().parse(
      readFixture(__dirname, 'bdinfo-summary-long')
    )
  })
  it('parser', () => {
    expect(info).toMatchObject({
      'Disc Size': 14315681792,
      Subtitle: [
        {
          language: 'Chinese',
        },
        {
          language: 'Chinese',
        },
        {
          language: 'English',
        },
      ],
    })
  })
  it('converter', () => {
    expect(new BdinfoConverter().convert(info)).toMatchObject({
      subtitles: ['Chinese', 'English'],
    })
  })
})

it('summary long', () => {
  const result = new BdinfoParser().parse(
    readFixture(__dirname, 'bdinfo-summary-long')
  )
  expect(result).toMatchObject({
    'Disc Size': 14315681792,
  })
})

test('compact summary', () => {
  const result = new BdinfoParser().parse(
    readFixture(__dirname, 'bdinfo-summary-short1')
  )
  expect(result).toMatchObject({
    'Disc Size': 14315681792,
  })
})

test('summary short2', () => {
  const result = new BdinfoParser().parse(
    readFixture(__dirname, 'bdinfo-summary-short2')
  )
  expect(result).toEqual({
    Video: [
      {
        codec: 'MPEG-4 AVC',
        bitrate: '23352 kbps',
        resolution: '1080p',
        frameRate: '23.976 fps',
        aspectRatio: '16:9',
        note: 'High Profile 4.1',
      },
    ],
    Audio: [
      {
        language: 'English',
        codec: 'DTS-HD Master Audio',
        channels: '7.1',
        sampleRate: '48 kHz',
        bitrate: '5028 kbps',
        note: '24-bit (DTS Core: 5.1 / 48 kHz / 1509 kbps / 24-bit / DN -2dB)',
      },
      {
        language: 'Portuguese',
        codec: 'Dolby Digital Audio',
        channels: '5.1',
        sampleRate: '48 kHz',
        bitrate: '640 kbps',
        note: 'DN -2dB',
      },
      {
        language: 'English',
        codec: 'Dolby Digital Audio',
        channels: '2.0',
        sampleRate: '48 kHz',
        bitrate: '192 kbps',
        note: 'DN -4dB',
      },
    ],
    Subtitle: [
      { language: 'English', bitrate: '28.869 kbps' },
      { language: 'Portuguese', bitrate: '28.151 kbps' },
      { language: 'English', bitrate: '0.130 kbps' },
    ],
    'Disc Title': 'AVENGERS_ENDGAME',
    'Disc Size': 45180271242,
    Protection: 'AACS',
    Playlist: '00800.MPLS',
    Size: 43469426688,
    Length: '3:01:11.360',
    'Total Bitrate': '31.99 Mbps',
  })
})

test('table', () => {
  const result = new BdinfoParser().parse(
    readFixture(__dirname, 'bdinfo-table')
  )
  expect(result).toEqual({
    'Disc Title':
      'Avengers.Endgame.2019.1080p.Blu-ray.AVC.DTS-HD.MA.7.1-HDChina',
    'Disc Label': 'AVENGER&#39;S ENDGAME_HDC',
    'Disc Size': 45578826277,
    Protection: 'AACS',
    Extras: 'BD-Java',
    BDInfo: '0.7.5.5',
    Name: '00800.MPLS',
    Length: '3:01:11.360',
    Size: 43883360256,
    'Total Bitrate': '32.29 Mbps',
    Video: [
      {
        codec: 'MPEG-4 AVC',
        bitrate: '23352 kbps',
        resolution: '1080p',
        frameRate: '23.976 fps',
        aspectRatio: '16:9',
        note: 'High Profile 4.1',
      },
    ],
    Audio: [
      {
        codec: 'DTS-HD Master Audio',
        language: 'English',
        channels: '7.1',
        sampleRate: '48 kHz',
        bitrate: '5028 kbps',
        note: '24-bit (DTS Core: 5.1 / 48 kHz /  1509 kbps / 24-bit / DN -2dB)',
      },
      {
        codec: 'Dolby Digital Audio',
        language: 'English',
        channels: '2.0',
        sampleRate: '48 kHz',
        bitrate: '320 kbps',
        note: 'DN -29dB',
      },
    ],
    Subtitle: [
      {
        language: 'English',
        bitrate: '33.823 kbps',
        codec: 'Presentation Graphics',
      },
      {
        language: 'English',
        bitrate: '69.912 kbps',
        codec: 'Presentation Graphics',
      },
    ],
  })
})
