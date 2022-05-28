import { render } from 'react-dom'

export function openElement(id, element) {
  const container = createContainer(id)
  render(element, container)
  document.body.appendChild(container)
}

export function closeElement(id) {
  const container = findContainer(id)
  if (!container) {
    return
  }
  render(null, container)
  document.body.removeChild(container)
  removeContainer(id)
}

export function isElementOpen(id) {
  return !!findContainer(id)
}

const containers = {}

function findContainer(id) {
  return containers[id]
}

function createContainer(id) {
  const container = document.createElement('div')
  containers[id] = container
  return container
}

function removeContainer(id) {
  containers[id] = undefined
}
