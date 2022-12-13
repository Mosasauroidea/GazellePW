import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsPie } from './options'

export const ChartTorrentByProcessings = () => {
  const options = merge({}, optionsPie, {
    chart: {
      type: 'pie',
    },
    title: {
      text: t('client.stats.processing_distribution'),
    },
    series: [
      {
        name: t('client.stats.torrent_count'),
        data: window.DATA['statsTorrentProcessings'].map((v) => ({
          y: v.value,
          name: v.name,
        })),
      },
    ],
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartTorrentByProcessings' }} />
}
