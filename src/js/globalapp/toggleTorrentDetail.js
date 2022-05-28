export default function toggleTorrentDetail(event, targetId) {
  event.preventDefault()
  const target = document.querySelector(targetId)
  target.classList.toggle('u-hidden')
}
