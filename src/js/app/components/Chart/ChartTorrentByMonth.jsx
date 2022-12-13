import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartTorrentByMonth = () => {
  const chartData = window.DATA['statsTorrentByMonth']
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: t('client.stats.torrentByMonth'),
    },
    series: [
      {
        name: t('client.stats.uploads'),
        data: chartData.map((v, i) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.in,
        })),
        dataLabels: { enabled: true },
      },
      {
        name: t('client.stats.delete'),
        data: chartData.map((v, i) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.out,
        })),
        dataLabels: { enabled: true },
      },
      {
        name: t('client.stats.upload_alive'),
        data: chartData.map((v, i) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.net,
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
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartTorrentByMonth' }} />
}
