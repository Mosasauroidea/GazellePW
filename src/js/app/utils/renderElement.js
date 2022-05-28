import { isValidElement, cloneElement } from 'react'
import { render } from 'react-dom'

export default function renderElement(value) {
  const container = document.createElement('div')
  const close = () => {
    render(null, container)
    document.body.removeChild(container)
  }
  const element = isValidElement(value)
    ? cloneElement(value, { close })
    : value({ close })
  render(element, container)
  document.body.appendChild(container)
}
