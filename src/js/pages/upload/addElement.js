import { registerValidation } from './validation'

export function addElement({ target, appendTo, transform }) {
  const cloned = document.querySelector(target).cloneNode(true)
  document.querySelector(appendTo).appendChild(cloned)
  if (transform) {
    transform(cloned)
  }
}

export function removeElement({ target }) {
  const targets = Array.from(document.querySelectorAll(target))
  if (targets.length <= 1) {
    return
  }
  targets[targets.length - 1].remove()
}

export function addMediaInfoTextarea(e) {
  e.preventDefault()
  addElement({
    target: '#mediainfo .Form-errorContainer',
    appendTo: '#mediainfo .Form-items',
    transform(node) {
      node.classList.remove('form-invalid')
      const errorMessage = node.querySelector('.Form-errorMessage')
      if (errorMessage) {
        errorMessage.innerHTML = ''
      }

      const textarea = node.querySelector('textarea')
      textarea.value = ''

      const previewHtml = node.querySelector('.BBCodePreview-html')
      previewHtml.innerHTML = 'MEDIAINFO'

      registerValidation()
    },
  })
}

export function removeMediaInfoTextarea(e) {
  e.preventDefault()
  removeElement({
    target: '#mediainfo .Form-errorContainer',
  })
}
