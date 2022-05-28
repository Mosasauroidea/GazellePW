import {
  openElement,
  closeElement,
  isElementOpen,
} from '#/app/utils/openElement'
import Snackbar from './Snackbar'

const ID = 'snackbar'

function open({ message }) {
  closeElement(ID)
  openElement(ID, <Snackbar message={message} />)
}

function close() {
  closeElement(ID)
}

function isOpen() {
  return isElementOpen(ID)
}

Snackbar.open = open
Snackbar.close = close
Snackbar.isOpen = isOpen

export default Snackbar
