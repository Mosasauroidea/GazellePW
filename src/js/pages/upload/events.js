import mediainfoAutofill from './mediainfoAutofill'
import { addMediaInfoTextarea, removeMediaInfoTextarea } from './addElement'

document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('#imdb')?.value) {
    document.querySelector('#imdb_button').click()
  }

  document.querySelector('[name="mediainfo[]"]').addEventListener('change', (e) => {
    var re = new RegExp(String.fromCharCode(160), 'g')
    e.target.value = e.target.value.replace(re, ' ')
    mediainfoAutofill(e.target.value)
  })

  document.querySelector('#add-mediainfo').addEventListener('click', addMediaInfoTextarea)
  document.querySelector('#remove-mediainfo').addEventListener('click', removeMediaInfoTextarea)
})
