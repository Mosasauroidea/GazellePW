import { render } from 'react-dom'
import {
  ChartUserTimeline,
  ChartUserClasses,
  ChartUserPlatforms,
  ChartUserBrowsers,
  ChartTorrentByMonth,
  ChartTorrentByDay,
  ChartTorrentByProcessings,
  ChartTorrentByCodecs,
  ChartTorrentByContainers,
  ChartTorrentBySources,
  ChartTorrentByResolutions,
  ChartTorrentByReleaseTypes,
  ChartPeersCount,
  ChartUserDayActive,
  ChartSeedingUser,
  ChartUserDayTorrent,
} from '#/js/app/components'

const ChartUserHome = () => (
  <>
    <ChartUserClasses />
    <ChartUserPlatforms />
    <ChartUserBrowsers />
  </>
)

const ChartTorrentSpecific = () => (
  <>
    <ChartTorrentByReleaseTypes />
    <ChartTorrentBySources />
    <ChartTorrentByCodecs />
    <ChartTorrentByContainers />
    <ChartTorrentByResolutions />
    <ChartTorrentByProcessings />
  </>
)
if (document.querySelector('#chart_user_timeline')) {
  render(<ChartUserTimeline />, document.querySelector('#chart_user_timeline'))
}
if (document.querySelector('#chart_user_home')) {
  render(<ChartUserHome />, document.querySelector('#chart_user_home'))
}
if (document.querySelector('#chart_torrent_by_month')) {
  render(<ChartTorrentByMonth />, document.querySelector('#chart_torrent_by_month'))
}
if (document.querySelector('#chart_torrent_by_day')) {
  render(<ChartTorrentByDay />, document.querySelector('#chart_torrent_by_day'))
}

if (document.querySelector('#chart_torrent_specific')) {
  render(<ChartTorrentSpecific />, document.querySelector('#chart_torrent_specific'))
}

if (document.querySelector('#chart_peers_count')) {
  render(<ChartPeersCount />, document.querySelector('#chart_peers_count'))
}
if (document.querySelector('#chart_user_day_active')) {
  render(<ChartUserDayActive />, document.querySelector('#chart_user_day_active'))
}

if (document.querySelector('#chart_seeding_user')) {
  render(<ChartSeedingUser />, document.querySelector('#chart_seeding_user'))
}

if (document.querySelector('#chart_user_day_torrent')) {
  render(<ChartUserDayTorrent />, document.querySelector('#chart_user_day_torrent'))
}
