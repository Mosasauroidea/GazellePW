/*
  user/edit.php
  dnd.css
  dnd.js
*/

import sortable from '#/js/modules/Sortable'

sortable({
  onDragEnd: () => {
    const items = Array.from(document.querySelectorAll('.u-sortable-item span')).map((element) =>
      element.getAttribute('data-value').trim()
    )
    document.querySelector('#SettingTorrentTitleInput').value = items.join(',')
  },
})

globalapp.userEditSettingTorrentTitleReset = function userEditSettingTorrentTitleReset() {
  document.querySelector('#SettingTorrentTitleInput').value = ''
}
