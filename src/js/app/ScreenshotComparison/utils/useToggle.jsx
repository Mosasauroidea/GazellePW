import { useState, useRef } from 'react'
import { useKeyPrevent } from '#/js/app/hooks'
import { isGpwHelperInjected } from '#/js/app/utils'
import { Dialog, Snackbar } from '#/js/app/components'

export default function useToggle({ key, context, active, deactive, checkGpwHelper }) {
  const [isActived, setIsActived] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  useKeyPrevent(key, async () => {
    if (checkGpwHelper && !isGpwHelperInjected()) {
      Dialog.open(({ close }) => (
        <Dialog close={close}>
          <Dialog.Title close={close} />
          <Dialog.Body>
            <div
              dangerouslySetInnerHTML={{
                __html: t('client.screenshot_comparison.gpw_helper_not_installed'),
              }}
            />
          </Dialog.Body>
        </Dialog>
      ))
      return
    }
    if (isLoading) {
      return
    }
    setIsLoading(true)
    const nextIsActived = !isActived
    if (nextIsActived) {
      await active({ context })
    } else {
      await deactive({ context })
    }
    setIsLoading(false)
    setIsActived(nextIsActived)
  })
  return [isActived, setIsActived]
}
