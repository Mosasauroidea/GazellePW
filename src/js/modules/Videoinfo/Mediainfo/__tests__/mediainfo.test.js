import MediainfoParser from '../MediainfoParser'
import MediainfoConverter from '../MediainfoConverter'

describe('mediainfo-1', () => {
  let info
  beforeAll(() => {
    info = new MediainfoParser().parse(readFixture(__dirname, 'mediainfo-1'))
  })

  test('Parser', () => {
    expect(info).toMatchObject({
      general: {
        'complete name':
          'The.Handmaiden : .2016.Hybrid.1080p.BluRay.DDP.5.1.x264.D-Z0N3.mkv',
      },
      video: [{ format: 'AVC', 'codec id': 'V_MPEG4/ISO/AVC' }],
    })
  })

  test('Converter', () => {
    expect(new MediainfoConverter().convert(info)).toMatchObject({
      codec: 'x264',
    })
  })
})
