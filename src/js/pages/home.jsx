/* private.php */

import { useState } from 'react'
import { render } from 'react-dom'
import { ChartTorrentByDay } from '#/js/app/components'

if (document.querySelector('#root-stats')) {
  const StatsHome = () => <ChartTorrentByDay />
  render(<StatsHome />, document.querySelector('#root-stats'))
}

// Hide Announcements on mobile
if (window.matchMedia('(max-width: 768px)').matches) {
  for (const post of document.querySelectorAll('.PostArticle')) {
    post.classList.add('hidden')
  }
}
