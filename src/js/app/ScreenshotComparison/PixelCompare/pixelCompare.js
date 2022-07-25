import pixelmatch from 'pixelmatch'
import { Snackbar } from '#/js/app/components'
import { imageDataToDataUrl, fetchImageData } from '../utils/canvas'

export async function active({ context }) {
  const { imgRefs, currentRow, currentColumn, setOverlay, columnNames, threshold } = context
  // Not set loading if active is fast
  const timeout = setTimeout(() => {
    Snackbar.open(t('client.screenshot_comparison.loading'))
  }, 1e3)
  const img1 = imgRefs[currentRow][0].current
  const img2 = imgRefs[currentRow][currentColumn].current
  const width = img1.width
  const height = img1.height
  const img1ImageData = await fetchImageData(img1.src)
  const img2ImageData = await fetchImageData(img2.src)
  const outputImageData = new ImageData(width, height)
  pixelmatch(img1ImageData.data, img2ImageData.data, outputImageData.data, width, height, {
    threshold,
  })
  const imageUrl = imageDataToDataUrl(outputImageData)
  const title = `${t('client.screenshot_comparison.pixel_compare')}: ${columnNames[0]} -> ${columnNames[currentColumn]}`
  clearTimeout(timeout)
  setOverlay((state) => ({ ...state, imageUrl, title }))
  Snackbar.close()
}

export function deactive({ context }) {
  context.closeOverlay()
}
