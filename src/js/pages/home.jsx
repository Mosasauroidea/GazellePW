/* private.php */

import { useState } from 'react'
import { render } from 'react-dom'
import { ChartTorrentByDay } from '#/js/app/components'

const StatsHome = () => <ChartTorrentByDay />

render(<StatsHome />, document.querySelector('#root-stats'))

/*
if (window.matchMedia('(max-width: 768px)').matches) {
  for (const button of document.querySelectorAll(
    '.Post-toggleButton:not(.is-sticky)'
  )) {
    button.click()
  }
}
*/
