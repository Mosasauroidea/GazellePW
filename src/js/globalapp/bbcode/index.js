import BBCodeToolbar from './BBCodeToolbar'
import BBCodePreview from './BBCodePreview'

/*
.u-bbcodeEditor
  .BBCodeToolbar
  .u-bbcodeTextarea
*/

document.addEventListener('DOMContentLoaded', () => {
  registerBBCodeToolbar()
  registerMediainfoToggle()
  BBCodePreview.register()
})

function registerBBCodeToolbar() {
  const editors = Array.from(document.querySelectorAll('.u-bbcodeEditor'))
  for (const editor of editors) {
    const toolbar = editor.querySelector('.BBCodeToolbar')
    const textarea = editor.querySelector('.u-bbcodeTextarea')
    new BBCodeToolbar({ textarea, toolbar }).register()
  }
}

function registerMediainfoToggle() {
  document.addEventListener('click', (e) => {
    if (e.target.getAttribute('data-action') !== 'toggle-mediainfo') {
      return
    }
    e.preventDefault()
    e.target.parentElement.nextElementSibling.classList.toggle('hidden')
    e.target.parentElement.nextElementSibling.nextElementSibling.classList.toggle(
      'hidden'
    )
  })
}
