import { render } from 'react-dom'
import { ChartTorrentByMonth, ChartTorrentByYear } from '#/app/components'

const StatsHome = () => (
  <>
    <ChartTorrentByMonth />
    <ChartTorrentByYear />
  </>
)

render(<StatsHome />, document.querySelector('#root'))
