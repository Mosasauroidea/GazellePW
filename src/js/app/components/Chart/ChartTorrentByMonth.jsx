import { merge } from 'lodash'
import { Chart } from '#/app/components'
import { optionsLineSingle } from './optionsLineSingle'

export const ChartTorrentByMonth = () => {
  const options = merge({}, optionsLineSingle, {
    title: {
      text: translation.get('stats.torrentByMonth'),
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
