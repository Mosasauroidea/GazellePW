import useToggle from '../utils/useToggle'
import { active, deactive } from './pixelCompare'

export default function usePixelCompare(context) {
  const [isPixelCompareActived, setIsPixelCompareActived] = useToggle({
    key: 'a',
    context,
    active,
    deactive,
    checkGpwHelper: true,
  })
  return {
    isPixelCompareActived,
    setIsPixelCompareActived,
  }
}
