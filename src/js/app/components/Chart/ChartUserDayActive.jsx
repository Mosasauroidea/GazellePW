import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartUserDayActive = () => {
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: t('client.stats.uv'),
    },
    series: [
      {
        data: window.DATA['statsUserActive'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.uv,
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
        day: '%m-%d',
      },
      tickInterval: 24 * 3600 * 1000,
    },
    legend: {
      enabled: false,
    },
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartUserDayActive' }} />
}
