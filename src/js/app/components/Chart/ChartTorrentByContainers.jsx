import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsPie } from './options'

export const ChartTorrentByContainers = () => {
  const options = merge({}, optionsPie, {
    chart: {
      type: 'pie',
    },
    title: {
      text: t('client.stats.container_distribution'),
    },
    series: [
      {
        name: t('client.stats.torrent_count'),
        data: window.DATA['statsTorrentContainers'].map((v) => ({
          y: v.value,
          name: v.name,
        })),
      },
    ],
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartTorrentByContainers' }} />
}
