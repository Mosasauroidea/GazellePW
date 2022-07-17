import {
  openElement,
  closeElement,
  isElementOpen,
} from '#/js/app/utils/openElement'
import Snackbar from './Snackbar'

const ID = 'snackbar'

Snackbar.open = function open(message, props = {}) {
  Snackbar.close()
  openElement(ID, <Snackbar {...props} message={message} />)
}

Snackbar.notify = function notify(message, args) {
  Snackbar.open(message, { onClick: close, ...args })
  setTimeout(() => {
    Snackbar.close()
  }, 2e3)
}

Snackbar.error = function error(message) {
  Snackbar.notify(message, { type: 'error' })
}

Snackbar.close = function close() {
  closeElement(ID)
}

Snackbar.isOpen = function isOpen() {
  return isElementOpen(ID)
}

export default Snackbar
