import { render } from 'react-dom'
import { ChartTorrentByMonth } from '#/app/components'

const StatsHome = () => <ChartTorrentByMonth />

render(<StatsHome />, document.querySelector('#root'))
