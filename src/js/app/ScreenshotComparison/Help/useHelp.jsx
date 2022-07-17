import { useKeyPrevent } from '#/js/app/hooks'
import {
  openElement,
  closeElement,
  isElementOpen,
} from '#/js/app/utils/openElement'
import { Dialog } from '#/js/app/components'
import en from './en.mdx'
import chs from './chs.mdx'

const ID = 'help'

export default function useHelp() {
  const close = () => {
    closeElement(ID)
  }
  useKeyPrevent('?', () => {
    if (isElementOpen(ID)) {
      return
    }
    openElement(
      ID,
      <Dialog close={close}>
        <Dialog.Title close={close}>
          {lang.get('screenshot_comparison.help_title')}
        </Dialog.Title>
        <Dialog.Body>{select({ en, chs })}</Dialog.Body>
      </Dialog>
    )
  })
}

function select(Comps) {
  const Comp = Comps[lang.lang()]
  return <Comp />
}
