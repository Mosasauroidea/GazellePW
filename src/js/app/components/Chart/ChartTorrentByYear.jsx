import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartTorrentByYear = () => {
  const data = window.DATA['statsTorrentByYear']
  if (!data) {
    return null
  }
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'column',
    },
    title: {
      text: t('client.stats.torrentByYear'),
    },
    series: [
      {
        data: data.map((v, i) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.uploads,
        })),
        dataLabels: { enabled: true },
      },
    ],
    xAxis: {
      type: 'datetime',
    },
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartTorrentByYear' }} />
}
