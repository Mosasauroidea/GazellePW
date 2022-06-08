/*
TorrentSearch
is-basic is-advanced
is-basicText is-advancedText is-advancedLink
is-inputAction
u-hidden
*/

globalapp.toggleSearchTorrentAdvanced = function toggleSearchTorrentAdvanced(
  event,
  mode
) {
  event.preventDefault()
  const target = event.target
  const root = target.closest('.SearchTorrent')
  switch (mode) {
    case 'basic':
      root.querySelector('.is-basicText').classList.remove('u-hidden')
      root.querySelector('.is-basicLink').classList.add('u-hidden')
      root.querySelector('.is-advancedText').classList.add('u-hidden')
      root.querySelector('.is-advancedLink').classList.remove('u-hidden')
      root.querySelector('.is-inputAction').value = 'basic'
      root.querySelector('.is-freeTorrent').classList.remove('u-hidden')

      for (const v of root.querySelectorAll('.is-basic')) {
        v.classList.remove('u-hidden')
      }
      for (const v of root.querySelectorAll('.is-advanced')) {
        v.classList.add('u-hidden')
      }
      for (const v of root.querySelectorAll('.is-basicInput')) {
        v.disabled = false
      }
      for (const v of root.querySelectorAll('.is-basicInput')) {
        v.disabled = true
      }
      return
    case 'advanced':
      root.querySelector('.is-basicText').classList.add('u-hidden')
      root.querySelector('.is-basicLink').classList.remove('u-hidden')
      root.querySelector('.is-advancedText').classList.remove('u-hidden')
      root.querySelector('.is-advancedLink').classList.add('u-hidden')
      root.querySelector('.is-freeTorrent').classList.add('u-hidden')
      root.querySelector('.is-inputAction').value = 'advanced'
      for (const v of root.querySelectorAll('.is-basic')) {
        v.classList.add('u-hidden')
      }
      for (const v of root.querySelectorAll('.is-advanced')) {
        v.classList.remove('u-hidden')
      }
      for (const v of root.querySelectorAll('.is-basicInput')) {
        v.disabled = true
      }
      for (const v of root.querySelectorAll('.is-basicInput')) {
        v.disabled = false
      }
      return
  }
}
