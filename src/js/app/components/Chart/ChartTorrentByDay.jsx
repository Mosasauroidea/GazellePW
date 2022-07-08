import { merge } from 'lodash'
import { Chart } from '#/app/components'
import { optionsLineSingle } from './optionsLineSingle'

export const ChartTorrentByDay = () => {
  const options = merge({}, optionsLineSingle, {
    title: {
      text: translation.get('stats.torrentByDay'),
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
