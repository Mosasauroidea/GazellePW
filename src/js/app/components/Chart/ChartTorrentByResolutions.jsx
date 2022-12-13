import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsPie } from './options'

export const ChartTorrentByResolutions = () => {
  const options = merge({}, optionsPie, {
    chart: {
      type: 'pie',
    },
    title: {
      text: t('client.stats.resolution_distribution'),
    },
    series: [
      {
        name: t('client.stats.torrent_count'),
        data: window.DATA['statsTorrentResolutions'].map((v) => ({
          y: v.value,
          name: v.name,
        })),
      },
    ],
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartTorrentByResolutions' }} />
}
