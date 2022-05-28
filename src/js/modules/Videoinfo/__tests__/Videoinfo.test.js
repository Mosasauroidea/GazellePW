import Videoinfo from '../Videoinfo'

test('特殊字符串', () => {
  const text = 'Complete\u2002\u200dname:'
  expect(Videoinfo.isValid(text)).toBeTruthy()
})
