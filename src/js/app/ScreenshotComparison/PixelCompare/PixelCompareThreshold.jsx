import { active } from './pixelCompare'

const PixelCompareThreshold = ({ context }) => {
  if (!(context.isPixelCompareActived && context.overlay.title)) {
    return null
  }

  const { threshold, setThreshold } = context

  const handleChange = (event) => {
    const nextThreshold = parseFloat(event.target.value)
    setThreshold(nextThreshold)
    active({
      context: { ...context, threshold: nextThreshold },
    })
  }

  return (
    <div className="ScreenshotComparison-threshold">
      <input
        className="ScreenshotComparison-thresholdInput"
        type="range"
        min="0"
        max="0.1"
        step="0.01"
        value={threshold}
        onChange={handleChange}
      />
      {threshold}
    </div>
  )
}

export default PixelCompareThreshold
