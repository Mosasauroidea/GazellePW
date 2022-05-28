import useEvent from './useEvent'

const useKey = (keyFilter, handler, options = {}) => {
  const { event = 'keydown', target } = options
  const handlerWrap = (handlerEvent) => {
    const keys = keyFilter.split(',').map((v) => v.trim())
    for (const key of keys) {
      if (handlerEvent.key === key) {
        handler(handlerEvent)
      }
    }
  }
  useEvent(event, handlerWrap, target)
}

export default useKey

export const useKeyPrevent = (key, handler, options = {}) => {
  useKey(key, (event) => {
    event.preventDefault()
    event.stopPropagation()
    handler(event)
  })
}
