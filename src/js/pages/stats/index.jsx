import { render } from 'react-dom'
import { ChartTorrentByMonth, ChartTorrentByYear } from '#/js/app/components'

const StatsHome = () => (
  <>
    <ChartTorrentByMonth />
    <ChartTorrentByYear />
  </>
)

render(<StatsHome />, document.querySelector('#root'))
