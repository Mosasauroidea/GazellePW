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
        name: '1',
        data: window.DATA['ChartTorrentByMonth'].map((v, i) => ({
          x: i,
          y: v.in,
        })),
      },
    ],
  })
  return (
    <div style={{ height: 300 }}>
      <Chart options={options} />
    </div>
  )
}
