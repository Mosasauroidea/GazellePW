import { useEffect } from 'react'

export default function useScrollLock() {
  useEffect(() => {
    document.documentElement.style.overflow = 'hidden'
    return () => {
      document.documentElement.style.overflow = ''
    }
  }, [])
}
