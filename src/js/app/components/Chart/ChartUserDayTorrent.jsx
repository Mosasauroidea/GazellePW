import { merge } from 'lodash'
import { Chart } from '#/js/app/components'
import { optionsSingle } from './options'

export const ChartUserDayTorrent = () => {
  const options = merge({}, optionsSingle, {
    chart: {
      type: 'spline',
    },
    title: {
      text: t('client.stats.uploaded_user'),
    },
    series: [
      {
        data: window.DATA['statsTorrentByDayUser'].map((v) => ({
          date: v.date,
          x: new Date(v.date).getTime(),
          y: v.net,
        })),
        dataLabels: { enabled: true },
      },
    ],
    yAxis: {
      allowDecimals: false,
    },
    xAxis: {
      type: 'datetime',
      dateTimeLabelFormats: {
        month: '%Y-%m-%d',
      },
      tickInterval: 24 * 3600 * 1000,
    },
  })
  return <Chart options={options} containerProps={{ className: 'ChartStat ChartUserDayTorrent' }} />
}
