import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartTorrentByDay = () => {
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: t('client.stats.torrentByDay'),
    },
    series: [
      {
        name: t('client.stats.uploads'),
        data: window.DATA['statsTorrentByDay'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.in,
        })),
        dataLabels: { enabled: true },
      },
      {
        name: t('client.stats.delete'),
        data: window.DATA['statsTorrentByDay'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.out,
        })),
        dataLabels: { enabled: true },
      },
      {
        name: t('client.stats.upload_alive'),
        data: window.DATA['statsTorrentByDay'].map((v) => ({
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
        day: '%m-%d',
      },
      tickInterval: 24 * 3600 * 1000,
    },
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartTorrentByDay' }} />
}
