import { toggleRowBySlot } from './shared'

/*
- click: 折叠这个edition
- cmd-click: 折叠这个group下所有edition

Table
  button: u-toggleEdition-button
  row: group-id=x edition-id=x
  row: u-toggleEdition-alwaysHidden
u-hidden
*/

const BUTTON_COLLAPSE = '−'
const BUTTON_EXPAND = '+'

globalapp.toggleEdition = function toggleEdition(event, groupId, editionId) {
  event.preventDefault()
  const target = event.target
  const table = target.closest('.Table')
  const isAllEdition = event.ctrlKey || event.metaKey
  const isHidden = target.innerHTML === BUTTON_EXPAND
  const filteredSlots = table.filteredSlots || []

  let rows, toggleButtons
  if (isAllEdition) {
    rows = [...table.querySelectorAll(`[group-id="${groupId}"][edition-id]`)]
    toggleButtons = [...table.querySelectorAll(`.u-toggleEdition-button`)]
  } else {
    rows = [...table.querySelectorAll(`[group-id="${groupId}"][edition-id="${editionId}"]`)]
    toggleButtons = [target]
  }

  for (const row of rows) {
    if (isHidden) {
      if (filteredSlots.length === 0 || filteredSlots.includes(row.getAttribute('data-slot'))) {
        row.classList.remove('u-hidden')
      }
      row.classList.remove('u-toggleEdition-hiddenByToggleEdition') /* for filterSlot */
    } else {
      row.classList.add('u-hidden', 'u-toggleEdition-hiddenByToggleEdition')
    }
  }

  for (const row of table.querySelectorAll('.u-toggleEdition-alwaysHidden')) {
    row.classList.add('u-hidden')
  }

  for (const toggleButton of toggleButtons) {
    if (isHidden) {
      toggleButton.innerHTML = BUTTON_COLLAPSE
      $(toggleButton).updateTooltip(t('client.torrent_table.collapse_edition'))
    } else {
      toggleButton.innerHTML = BUTTON_EXPAND
      $(toggleButton).updateTooltip(t('client.torrent_table.expand_edition'))
    }
  }
}
