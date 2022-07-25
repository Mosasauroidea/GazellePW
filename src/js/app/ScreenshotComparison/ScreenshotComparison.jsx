import PropTypes from 'prop-types'
import { Dialog } from '#/js/app/components'
import useScreenshotComparison from './useScreenshotComparison'
import { PixelCompareThreshold } from './PixelCompare'

/*
ScreenshotComparison.css

open(['Source', 'Encode'], imgUrls, { debug: true })
*/
export default function openScreenshotComparison(columnNames, images, options = {}) {
  Dialog.open(<ScreenshotComparison columnNames={columnNames} images={images} options={options} />)
}

const ScreenshotComparison = ({ close, columnNames, images, options }) => {
  const context = useScreenshotComparison({
    close,
    columnNames,
    images,
    options,
  })
  const { rootRef, handleMouseMove, currentColumn, currentRow, rows, rowRefs, imgRefs, overlay } = context

  return (
    <div ref={rootRef} className="ScreenshotComparison">
      <div className="ScreenshotComparison-header">
        <div className="ScreenshotComparison-title">{overlay.title || columnNames[currentColumn].trim()}</div>
        <PixelCompareThreshold context={context} />
      </div>
      <div className="ScreenshotComparison-rows">
        {rows.map((columns, rowIndex) => (
          <div
            ref={rowRefs[rowIndex]}
            key={rowIndex}
            className="ScreenshotComparison-row"
            onMouseMove={(event) => handleMouseMove({ rowIndex, event })}
          >
            <div className="ScreenshotComparison-placeholder">
              <img src={columns[0]} style={{ visibility: 'hidden' }} />
            </div>
            {columns.map((image, columnIndex) => (
              <div key={columnIndex} className="ScreenshotComparison-imageContainer">
                <img
                  ref={imgRefs[rowIndex][columnIndex]}
                  key={columnIndex}
                  className="ScreenshotComparison-image"
                  src={image}
                  style={{
                    visibility: currentColumn === columnIndex ? 'visible' : 'hidden',
                  }}
                />
              </div>
            ))}
            {overlay.imageUrl && rowIndex === currentRow && (
              <div className="ScreenshotComparison-imageContainer ScreenshotComparison-overlayContainer">
                <img className="ScreenshotComparison-image ScreenshotComparison-overlay" src={overlay.imageUrl} />
              </div>
            )}
          </div>
        ))}
      </div>
      <div className="ScreenshotComparison-help">{t('client.screenshot_comparison.help')}</div>
    </div>
  )
}

ScreenshotComparison.propTypes = {
  close: PropTypes.func.isRequired,
  columnNames: PropTypes.arrayOf(PropTypes.string).isRequired,
  images: PropTypes.arrayOf(PropTypes.string).isRequired,
  options: PropTypes.object,
}
