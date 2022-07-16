import { get } from 'lodash-es'
import en from '../../../locales/en.yaml'
import zh_CN from '../../../locales/zh_CN.yaml'

const DEFULAT_LANG = 'en'

const translations = { en, zh_CN }

window.translation = {
  lang() {
    let lang = cookie.get('lang')
    lang = lang === 'chs' ? 'zh_CN' : lang
    if (!(lang in translations)) {
      lang = DEFAULT_LANG
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
