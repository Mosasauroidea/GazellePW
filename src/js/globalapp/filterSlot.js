import { pullAll } from 'lodash-es'
import { toggleRowBySlot } from './shared'

/*
table.filteredSlots = ['cn_quantity']

.TableTorrent
  <buttons>
    .TableTorrent-slotFilterButton.is-active <i>
    .TableTorrent-slotFilterButton[data-slot="clear"] <i>
  .Table-row data-slot=<slotName>
*/

globalapp.filterSlot = function filterSlot(event, slotNames) {
  event.preventDefault()
  const target = event.target.closest('.TableTorrent-slotFilterButton')
  const table = target.closest('.TableTorrent')
  const isActive = target.classList.contains('is-active')
  const clearBtn = table.querySelector(
    '.TableTorrent-slotFilterButton[data-slot="clear"]'
  )
  const isClear = slotNames.length === 0
  let filteredSlots = table.filteredSlots || []

  if (isClear) {
    filteredSlots = []
  } else if (isActive) {
    pullAll(filteredSlots, slotNames)
  } else {
    filteredSlots.push(...slotNames)
  }
  table.filteredSlots = filteredSlots

  // <button>.is-active
  for (const filterBtn of table.querySelectorAll(
    '.TableTorrent-slotFilterButton:not([data-slot="clear"])'
  )) {
    if (filteredSlots.includes(filterBtn.getAttribute('data-slot'))) {
      filterBtn.classList.add('is-active')
    } else {
      filterBtn.classList.remove('is-active')
    }
  }

  // <clearButton>
  if (filteredSlots.length > 0) {
    clearBtn.style.visibility = 'visible'
  } else {
    clearBtn.style.visibility = 'hidden'
  }

  for (const row of table.querySelectorAll(
    '.TableTorrent-rowTitle:not(.u-toggleEdition-hiddenByToggleEdition)'
  )) {
    toggleRowBySlot({ row, filteredSlots })
  }

  for (const row of table.querySelectorAll(
    '.TableTorrent-rowDetail:not(.u-hidden)'
  )) {
    row.classList.add('u-hidden')
  }
}
