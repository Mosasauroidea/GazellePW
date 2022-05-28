import translations from '../../../i18n'
import { get } from 'lodash-es'

window.translation = {
  lang() {
    let lang = cookie.get('lang')
    if (!(lang in translations)) {
      lang = 'chs'
    }
    return lang
  },

  get(str) {
    return get(translations[this.lang()], str, str)
  },

  format() {
    var s = arguments[0]
    for (var i = 0; i < arguments.length - 1; i++) {
      var reg = new RegExp('\\{' + i + '\\}', 'gm')
      s = s.replace(reg, arguments[i + 1])
    }
    return s
  },
}
