import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartTorrentByDay = () => {
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: lang.get('stats.torrentByDay'),
    },
    series: [
      {
        data: window.DATA['statsTorrentByDay'].map((v) => ({
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
        day: '%m-%d',
      },
      tickInterval: 24 * 3600 * 1000,
    },
  })
  return (
    <Chart
      options={options}
      containerProps={{ className: 'ChartStat ChartTorrentByDay' }}
    />
  )
}
