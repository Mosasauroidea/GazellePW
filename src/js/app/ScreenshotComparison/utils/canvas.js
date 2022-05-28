export function imageDataToDataUrl(imageData) {
  const canvas = document.createElement('canvas')
  canvas.width = imageData.width
  canvas.height = imageData.height
  const ctx = canvas.getContext('2d')
  ctx.putImageData(imageData, 0, 0)
  return canvas.toDataURL('image/png')
}

export async function fetchImageData(url) {
  const res = await gpwHelper.fetch(url)
  const blob = await res.blob()
  const imageBitmap = await createImageBitmap(blob)
  const width = imageBitmap.width
  const height = imageBitmap.height
  const canvas = document.createElement('canvas')
  canvas.width = width
  canvas.height = height
  const ctx = canvas.getContext('2d')
  ctx.drawImage(imageBitmap, 0, 0)
  return ctx.getImageData(0, 0, width, height)
}
