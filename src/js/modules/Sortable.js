/* Sortable.css */

export default function sortable({ onDragEnd } = {}) {
  const items = document.querySelectorAll('.u-sortable-item')
  for (const item of items) {
    item.setAttribute('draggable', 'true')
    item.addEventListener('dragstart', handleDragStart)
    item.addEventListener('dragend', (event) => handleDragEnd(event, { onDragEnd }))
    item.addEventListener('dragenter', handleDragEnter)
    item.addEventListener('dragover', handleDragOver)
    item.addEventListener('dragleave', handleDragLeave)
    item.addEventListener('drop', handleDrop)
  }
}

let startElement

const handleDragStart = (event) => {
  const target = event.currentTarget
  target.classList.add('u-sortable-item--isStart')
  startElement = target
  event.dataTransfer.setData('text/plain', target.innerHTML)
  event.dataTransfer.effectAllowed = 'move'
}

const handleDragEnd = (event, { onDragEnd }) => {
  const target = event.currentTarget
  target.classList.remove('u-sortable-item--isStart')
  onDragEnd && onDragEnd()
}

const handleDragEnter = (event) => {
  const target = event.currentTarget
  target.classList.add('u-sortable-item--isOver')
}
const handleDragOver = (event) => {
  event.preventDefault()
}

const handleDragLeave = (event) => {
  const target = event.currentTarget
  target.classList.remove('u-sortable-item--isOver')
}

const handleDrop = (event) => {
  event.stopPropagation()
  const target = event.currentTarget
  target.classList.remove('u-sortable-item--isOver')
  if (target !== startElement) {
    startElement.innerHTML = target.innerHTML
    target.innerHTML = event.dataTransfer.getData('text/plain')
  }
}
