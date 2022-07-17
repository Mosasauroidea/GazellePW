import { get } from 'lodash-es'
import en from '#/locales/en/client.yaml'
import zhCN from '#/locales/zh-CN/client.yaml'

const DEFULAT_LANG = 'en'

const translations = { en, chs: zhCN }

window.lang = {
  lang() {
    let lang = cookie.get('lang')
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
