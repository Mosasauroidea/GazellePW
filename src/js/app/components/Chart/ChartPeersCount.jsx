import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartPeersCount = () => {
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: t('client.stats.peers'),
    },
    series: [
      {
        name: t('client.stats.seeder_count'),
        data: window.DATA['statsPeersCount'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.seeder_count,
        })),
        dataLabels: { enabled: true },
        yAxis: 0,
      },
      {
        name: t('client.stats.leecher_count'),
        data: window.DATA['statsPeersCount'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.leecher_count,
        })),
        dataLabels: { enabled: true },
        yAxis: 1,
      },
    ],
    yAxis: [
      {
        allowDecimals: false,
        title: {
          text: t('client.stats.seeder_count'),
        },
      },
      {
        title: {
          text: t('client.stats.leecher_count'),
        },
        opposite: true,
        allowDecimals: false,
      },
    ],
    xAxis: {
      type: 'datetime',
      dateTimeLabelFormats: {
        day: '%m-%d',
      },
      tickInterval: 24 * 3600 * 1000,
    },
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartPeersCount' }} />
}
