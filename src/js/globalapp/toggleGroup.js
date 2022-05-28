/*
- click: 折叠这个group
- cmd-click: 折叠所有group

Table
	button: u-toggleGroup-button
	row: group-id=x edition-id=x
hidden
*/
export default function toggleGroup(groupid, link, event) {
  var showRow = true
  var clickedRow = link
  while (clickedRow.nodeName != 'TR') {
    clickedRow = clickedRow.parentNode
  }
  var group_rows = clickedRow.parentNode.children
  var showing = $(clickedRow).nextElementSibling().has_class('hidden')
  var allGroups = event.ctrlKey || event.metaKey // detect ctrl or cmd

  // for dealing with Mac OS X
  // http://stackoverflow.com/a/3922353
  var allGroupsMac =
    event.keyCode == 91 || // WebKit (left apple)
    event.keyCode == 93 || // WebKit (right apple)
    event.keyCode == 224 || // Firefox
    event.keyCode == 17 // Opera
      ? true
      : null

  for (var i = 0; i < group_rows.length; i++) {
    var row = $(group_rows[i])
    if (row.has_class('Table-rowHeader')) {
      continue
    }
    if (
      allGroups ||
      allGroupsMac ||
      row[0].getAttribute('group-id') === String(groupid)
    ) {
      if (row.has_class('TableTorrent-rowMovieInfo')) {
        var section
        if (location.pathname.search('/artist.php$') !== -1) {
          section = translation.get('in_this_release_type')
        } else {
          section = translation.get('on_this_page')
        }
        var tooltip = showing
          ? translation.format(translation.get('collapse_this_group'), section)
          : translation.format(translation.get('expand_this_group'), section)
        $('.ToggleGroup-button', row).updateTooltip(tooltip)
        const parentClassList = $('.ToggleGroup-button', row).raw().parentNode
          .classList
        if (showing) {
          parentClassList.add('is-toHide')
        } else {
          parentClassList.remove('is-toHide')
        }
      } else {
        if (showing) {
          // show the row depending on whether the edition it's in is collapsed or not
          if (row.has_class('TableTorrent-rowCategory')) {
            row.gshow()
            showRow = $('a', row.raw()).raw().innerHTML != '+'
          } else {
            if (showRow) {
              row.gshow()
            } else {
              row.ghide()
            }
          }
        } else {
          row.ghide()
        }
      }
    }
  }
  if (event.preventDefault) {
    event.preventDefault()
  } else {
    // for IE < 9 support
    event.returnValue = false
  }
}
