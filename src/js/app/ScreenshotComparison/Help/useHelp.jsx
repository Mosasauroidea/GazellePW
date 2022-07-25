import { useKeyPrevent } from '#/js/app/hooks'
import { openElement, closeElement, isElementOpen } from '#/js/app/utils/openElement'
import { Dialog } from '#/js/app/components'

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
        <Dialog.Title close={close}>{t('client.screenshot_comparison.help_title')}</Dialog.Title>
        <Dialog.Body>{lang.element('ScreenshotComparisonHelp.mdx')}</Dialog.Body>
      </Dialog>
    )
  })
}
