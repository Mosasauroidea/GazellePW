import { useRef } from 'react'
import { useKeyPrevent, useEvent } from '#/js/app/hooks'
import useScrollLock from './useScrollLock'

/* Dialog.css */

const Dialog = ({ close, children }) => {
  const contentRef = useRef()

  useScrollLock()

  useKeyPrevent('Escape', () => {
    close()
  })

  useEvent('mousedown', (event) => {
    if (contentRef.current?.contains(event.target)) return
    close()
  })

  return (
    <div className="Dialog" role="dialog">
      <div className="Dialog-center">
        <div className="Dialog-overlay" />
        <span className="Dialog-center2" />
        <div ref={contentRef} className="Dialog-main">
          {children}
        </div>
      </div>
    </div>
  )
}

const Title = ({ children, close }) => (
  <div className="Dialog-header">
    <div className="Dialog-title">{children}</div>
    <div className="Dialog-closeButton" onClick={close}>
      <svg
        className="Dialog-closeButtonIcon"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </div>
  </div>
)

const Body = ({ children }) => <div className="Dialog-body">{children}</div>

const Footer = ({ children }) => <div className="Dialog-footer">{children}</div>

Dialog.Title = Title
Dialog.Body = Body
Dialog.Footer = Footer

export default Dialog
