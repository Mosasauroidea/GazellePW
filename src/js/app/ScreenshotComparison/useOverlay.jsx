import { useState } from 'react'

const initState = {
  imageUrl: null,
  title: null,
}

export default function useOverlay() {
  const [overlay, setOverlay] = useState(initState)
  const closeOverlay = () => {
    setOverlay(initState)
  }
  return {
    overlay,
    setOverlay,
    closeOverlay,
  }
}
