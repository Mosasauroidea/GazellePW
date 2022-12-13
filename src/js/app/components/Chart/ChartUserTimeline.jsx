import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartUserTimeline = () => {
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: t('client.stats.user_timeline'),
    },
    series: [
      {
        name: t('client.stats.new_registrations'),
        data: window.DATA['statsUserTimeline'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.in,
        })),
        dataLabels: { enabled: true },
      },
      {
        name: t('client.stats.disabled_user'),
        data: window.DATA['statsUserTimeline'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.out,
        })),
        dataLabels: { enabled: true },
      },
    ],
    yAxis: {
      allowDecimals: false,
    },
    xAxis: {
      type: 'datetime',
      dateTimeLabelFormats: {
        month: '%Y-%m',
      },
      tickInterval: 30 * 24 * 3600 * 1000,
    },
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartUserTimeline' }} />
}
