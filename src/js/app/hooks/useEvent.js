import { useEffect, useRef } from 'react'

const useEvent = (name, handler, target = window) => {
  const handlerRef = useRef()
  handlerRef.current = handler
  useEffect(() => {
    const newHandler = (event) => {
      handlerRef.current(event)
    }
    target.addEventListener(name, newHandler)
    return () => {
      target.removeEventListener(name, newHandler)
    }
  }, [name, target])
}

export default useEvent
