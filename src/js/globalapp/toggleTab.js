/*
u-tab
  u-tabItem u-tabItem<Name>

toggleTable(event, '.u-tabItem<Name>')
*/
globalapp.toggleTab = function toggleTab(event, selector) {
  const target = event.target
  const currentTable = target.closest('.u-tabItem')
  const nextTable = target.closest('.u-tab').querySelector(selector)
  console.log(target.closest('.u-tab'))
  event.preventDefault()
  currentTable.style.display = 'none'
  nextTable.style.display = ''
}
