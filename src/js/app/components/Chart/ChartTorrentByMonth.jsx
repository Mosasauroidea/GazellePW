import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartTorrentByMonth = () => {
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: lang.get('stats.torrentByMonth'),
    },
    series: [
      {
        data: window.DATA['statsTorrentByMonth'].map((v, i) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.uploads,
        })),
        dataLabels: { enabled: true },
      },
    ],
    xAxis: {
      type: 'datetime',
      dateTimeLabelFormats: {
        month: '%Y-%m',
      },
      tickInterval: 30 * 24 * 3600 * 1000,
    },
  })
  return (
    <Chart
      options={options}
      containerProps={{ className: 'ChartStat ChartTorrentByMonth' }}
    />
  )
}
