import { active } from './solarCurve'

export default async function solarCurveHandleCompareChange({
  type,
  event,
  context,
}) {
  const { isSolarCurveActived } = context
  if (!isSolarCurveActived) {
    return
  }
  await active({ context })
}
