import { render } from 'react-dom'
import { MDXProvider } from '@mdx-js/react'
import { isString, get, template, templateSettings } from 'lodash-es'
import en from '#/locales/en/client.yaml'
import zhHans from '#/locales/zh-Hans/client.yaml'
import ScreenshotComparisionHelpEn from '#/locales/en/ScreenshotComparisonHelp.mdx'
import ScreenshotComparisionHelpZhHans from '#/locales/zh-Hans/ScreenshotComparisonHelp.mdx'
import DonateOverviewEn from '#/locales/en/Donate/Overview.mdx'
import DonateOverviewZhHans from '#/locales/zh-Hans/Donate/Overview.mdx'
import RulesGoldenRulesEn from '#/locales/en/Rules/GoldenRules.mdx'
import RulesGoldenRulesZhHans from '#/locales/zh-Hans/Rules/GoldenRules.mdx'
import RulesChartEn from '#/locales/en/Rules/Chart.mdx'
import RulesChartZhHans from '#/locales/zh-Hans/Rules/Chart.mdx'
import RulesCollagesEn from '#/locales/en/Rules/Collages.mdx'
import RulesCollagesZhHans from '#/locales/zh-Hans/Rules/Collages.mdx'
import RulesRequestsEn from '#/locales/en/Rules/Requests.mdx'
import RulesRequestsZhHans from '#/locales/zh-Hans/Rules/Requests.mdx'
import RulesTagsEn from '#/locales/en/Rules/Tags.mdx'
import RulesTagsZhHans from '#/locales/zh-Hans/Rules/Tags.mdx'
import RulesRatioEn from '#/locales/en/Rules/Ratio.mdx'
import RulesRatioZhHans from '#/locales/zh-Hans/Rules/Ratio.mdx'
import RulesClientsEn from '#/locales/en/Rules/Clients.mdx'
import RulesClientsZhHans from '#/locales/zh-Hans/Rules/Clients.mdx'
import RulesUploadEn from '#/locales/en/Rules/Upload.mdx'
import RulesUploadZhHans from '#/locales/zh-Hans/Rules/Upload.mdx'
import * as components from '#/js/app/components'

const LOCALES = { en, chs: zhHans }

const DEFULAT_LANG = 'en'

const COMPONENTS = {
  en: {
    'ScreenshotComparisonHelp.mdx': ScreenshotComparisionHelpEn,
    'Donate/Overview.mdx': DonateOverviewEn,
    'Rules/GoldenRules.mdx': RulesGoldenRulesEn,
    'Rules/Chat.mdx': RulesChartEn,
    'Rules/Collages.mdx': RulesCollagesEn,
    'Rules/Requests.mdx': RulesRequestsEn,
    'Rules/Tags.mdx': RulesTagsEn,
    'Rules/Ratio.mdx': RulesRatioEn,
    'Rules/Clients.mdx': RulesClientsEn,
    'Rules/Upload.mdx': RulesUploadEn,
  },
  chs: {
    'ScreenshotComparisonHelp.mdx': ScreenshotComparisionHelpZhHans,
    'Donate/Overview.mdx': DonateOverviewZhHans,
    'Rules/GoldenRules.mdx': RulesGoldenRulesZhHans,
    'Rules/Chat.mdx': RulesChartZhHans,
    'Rules/Collages.mdx': RulesCollagesZhHans,
    'Rules/Requests.mdx': RulesRequestsZhHans,
    'Rules/Tags.mdx': RulesTagsZhHans,
    'Rules/Ratio.mdx': RulesRatioZhHans,
    'Rules/Clients.mdx': RulesClientsZhHans,
    'Rules/Upload.mdx': RulesUploadZhHans,
  },
}

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

  element(name) {
    const Component = COMPONENTS[this.lang()][name]
    if (!Component) {
      throw new Error(`i18n: key not found ${name}`)
    }
    const props = {
      ...window.DATA.CONFIG,
    }
    return <Component {...props} />
  },

  render(name) {
    const selector = name.replace(/\.|\//g, '-')
    render(<MDXProvider components={components}>{this.element(name)}</MDXProvider>, document.getElementById(selector))
  },
}
