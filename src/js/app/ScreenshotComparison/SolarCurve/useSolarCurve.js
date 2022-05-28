import useToggle from '../utils/useToggle'
import { active, deactive } from './solarCurve'

export default function useSolarCurve(context) {
  const [isSolarCurveActived, setIsSolarCurveActived] = useToggle({
    key: 's',
    context,
    active,
    deactive,
    checkGpwHelper: true,
  })
  return {
    isSolarCurveActived,
    setIsSolarCurveActived,
  }
}
