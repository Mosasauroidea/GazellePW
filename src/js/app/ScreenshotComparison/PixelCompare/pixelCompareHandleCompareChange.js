import { active } from './pixelCompare'

export default async function pixelCompareHandleCompareChange({
  type,
  event,
  context,
}) {
  const { isPixelCompareActived } = context
  if (!isPixelCompareActived) {
    return
  }
  await active({ context })
}
