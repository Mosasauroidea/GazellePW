import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsPie } from './options'

export const ChartUserPlatforms = () => {
  const options = merge({}, optionsPie, {
    chart: {
      type: 'pie',
    },
    title: {
      text: t('client.stats.user_platform_distribution'),
    },
    series: [
      {
        name: t('client.stats.user_count'),
        data: window.DATA['statsUserPlatforms'].map((v) => ({
          y: v.value,
          name: v.name,
        })),
      },
    ],
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartUserPlatforms' }} />
}
