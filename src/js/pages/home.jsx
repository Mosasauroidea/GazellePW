/* private.php */

import { useState } from 'react'
import { render } from 'react-dom'
import { ChartTorrentByDay } from '#/js/app/components'

const StatsHome = () => <ChartTorrentByDay />

render(<StatsHome />, document.querySelector('#root-stats'))
