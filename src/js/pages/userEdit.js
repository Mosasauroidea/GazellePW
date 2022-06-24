/*
  user/edit.php
  dnd.css
  dnd.js
*/

import sortable from '#/modules/Sortable'

sortable({
  onDragEnd: () => {
    const texts = Array.from(document.querySelectorAll('.u-sortable-item')).map(
      (element) => element.textContent.trim()
    )
    const CustomTorrentTitle = { Items: texts }
    document.querySelector('#CustomTorrentTitleInput').value =
      JSON.stringify(CustomTorrentTitle)
  },
})

globalapp.userEditCustomTorrentTitleReset =
  function userEditCustomTorrentTitleReset() {
    document.querySelector('#CustomTorrentTitleInput').value = ''
  }
