import { isString, get, template, templateSettings } from 'lodash-es'
import en from '#/locales/en/client.yaml'
import zhCN from '#/locales/zh-CN/client.yaml'

const LOCALES = { en, chs: zhCN }

const DEFULAT_LANG = 'en'

templateSettings.interpolate = /\{\{([^\\}]*(?:\\.[^\\}]*)*)\}\}/g

window.lang = {
  lang() {
    let lang = cookie.get('lang')
    if (!(lang in LOCALES)) {
      lang = DEFAULT_LANG
    }
    return lang
  },

  get(key, { defaultValue, ...rest } = {}) {
    const locale = LOCALES[this.lang()]
    defaultValue = defaultValue !== undefined ? defaultValue : key
    let value = get(locale, key, defaultValue)
    if (isString(value)) {
      value = template(value)({ CONFIG: window.DATA.CONFIG, ...rest })
    }
    return value
  },
}
