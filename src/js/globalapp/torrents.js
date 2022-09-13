globalapp.addTorrentItem = function addTorrentItem(itemId, allItemId) {
  let item = $('#' + itemId)
  let allItem = $('#' + allItemId)
  let selectValue = allItem.raw().options[allItem.raw().selectedIndex].innerHTML
  if (allItem.raw().options[allItem.raw().selectedIndex].value === '') {
  } else if (item.raw().value == '') {
    item.raw().value = selectValue
  } else {
    item.raw().value = item.raw().value + ', ' + selectValue
  }
}