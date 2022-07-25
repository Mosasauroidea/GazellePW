import { Snackbar } from '#/js/app/components'
import { fetchImageData, imageDataToDataUrl } from '../utils/canvas'

export async function active({ context }) {
  const { imgRefs, currentRow, currentColumn, setOverlay, columnNames } = context
  // Not set loading if active is fast
  const timeout = setTimeout(() => {
    Snackbar.open(t('client.screenshot_comparison.loading'))
  }, 1e3)
  const img = imgRefs[currentRow][currentColumn].current
  const width = img.width
  const height = img.height
  const inputImageData = await fetchImageData(img.src)
  const outputImageData = new ImageData(width, height)
  applyCurve({
    input: inputImageData.data,
    output: outputImageData.data,
    width,
    height,
    curve: solarCurve,
  })
  const imageUrl = imageDataToDataUrl(outputImageData)
  const title = `${t('client.screenshot_comparison.solar_curve')}: ${columnNames[currentColumn]}`
  clearTimeout(timeout)
  setOverlay((state) => ({ ...state, imageUrl, title }))
  Snackbar.close()
}

export function deactive({ context }) {
  context.closeOverlay()
}

function applyCurve({ input, output, width, height, curve }) {
  // apply curve to each pixel
  for (let y = 0; y < height; y++) {
    for (let x = 0; x < width; x++) {
      const pos = (y * width + x) * 4
      drawPixel({
        output,
        pos,
        r: curve(input[pos]),
        g: curve(input[pos + 1] - 5),
        b: curve(input[pos + 2] + 5),
        a: input[pos + 3],
      })
    }
  }
}

function drawPixel({ output, pos, r, g, b, a }) {
  output[pos + 0] = r
  output[pos + 1] = g
  output[pos + 2] = b
  output[pos + 3] = a
}

// https://www.google.com/search?q=y%3D127.999*sin(0.00000198394*x%5E3%2B0.00076183231*x%5E2%2B0.2*x-3.14159%2F2)%2B127.5&pws=0&gl=us&gws_rd=cr
function solarCurve(x, t = 5, k = 5.5) {
  const m = k * Math.PI - 128 / t
  const A = (-1 / 4194304) * m
  const B = (3 / 32768) * m
  const C = 1 / t
  return Math.round(127.9999 * Math.sin(A * x ** 3 + B * x ** 2 + C * x - Math.PI / 2) + 127.5)
}
