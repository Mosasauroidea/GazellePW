import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsPie } from './options'

export const ChartTorrentByReleaseTypes = () => {
  const options = merge({}, optionsPie, {
    chart: {
      type: 'pie',
    },
    title: {
      text: t('client.stats.release_distribution'),
    },
    series: [
      {
        name: t('client.stats.movie_count'),
        data: window.DATA['statsTorrentReleaseTypes'].map((v) => ({
          y: v.value,
          name: v.name,
        })),
      },
    ],
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartTorrentByReleaseTypes' }} />
}
