import {
    validateDescImg3Png,
    validateDescImgHosts,
    validateDescComparison,
} from '../validation'

describe('validateImg3Png', () => {
    test('ok', () => {
        expect(
            validateDescImg3Png(`
        [img]https://ptpimg.me/a.png[/img]
        [img]https://ptpimg.me/b.png[/img]
        [img=https://ptpimg.me/c.png]
       `)
        ).toBeTruthy()
    })
    test('less than 3 imgs', () => {
        expect(
            validateDescImg3Png(`
        [img]https://ptpimg.me/a.png[/img]
        [img]https://ptpimg.me/b.png[/img]
       `)
        ).toBeFalsy()
    })
})

describe('validateDescImgHosts', () => {
    test.only('correct img host', () => {
        expect(
            validateDescImgHosts(`
        [img=https://ptpimg.me/b.png]
        [img=https://ptpimg.me/c.png]
    `)
        ).toBeTruthy()
    })
    test('wrong img host', () => {
        expect(
            validateDescImgHosts(`
        [img=https://ptpimg.wrong/b.png]
    `)
        ).toBeFalsy()
    })
})

describe('validateDescComparison', () => {
    test('ok', () => {
        expect(
            validateDescComparison(`
      [comparison=A, B]
        https://ptpimg.me/b.png

        [img]https://ptpimg.me/c.png[/img]
      [/comparison]
    `)
        ).toBeTruthy()
    })
    test('no comparison', () => {
        expect(validateDescComparison(`no`)).toBeTruthy()
    })
    test('wrong img host', () => {
        expect(
            validateDescComparison(`
      [comparison=A, B]
        https://a.com/b.png
        https://ptpimg.me/c.png
      [/comparison]
    `)
        ).toBeFalsy()
    })
})
