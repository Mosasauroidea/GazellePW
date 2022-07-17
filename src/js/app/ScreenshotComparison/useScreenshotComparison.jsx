import { useState, useRef, createRef } from 'react'
import { chunk } from 'lodash-es'
import { useKeyPrevent } from '#/js/app/hooks'
import { Dialog } from '#/js/app/components'
import {
  usePixelCompare,
  pixelCompareHandleCompareChange,
} from './PixelCompare'
import { useSolarCurve, solarCurveHandleCompareChange } from './SolarCurve'
import { useHelp } from './Help'
import useOverlay from './useOverlay'

const useScreenshotComparison = ({ columnNames, images, close, options }) => {
  Dialog.useScrollLock()
  const [currentColumn, setCurrentColumn] = useState(0)
  const [currentRow, setCurrentRow] = useState(0)
  const [threshold, setThreshold] = useState(0.01)
  const { overlay, setOverlay, closeOverlay } = useOverlay()
  const rows = chunk(images, columnNames.length)
  const rootRef = useRef()
  const rowRefs = rows.map(createRef)
  const imgRefs = rows.map((columns) => columns.map(createRef))
  let context = {
    columnNames,
    rows,
    rootRef,
    rowRefs,
    imgRefs,
    currentRow,
    setCurrentRow,
    currentColumn,
    setCurrentColumn,
    overlay,
    setOverlay,
    closeOverlay,
    threshold,
    setThreshold,
  }
  const pixelCompareContext = usePixelCompare(context)
  const solarCurveContext = useSolarCurve(context)
  useHelp()
  context = {
    ...context,
    ...pixelCompareContext,
    ...solarCurveContext,
  }

  const onCompareChange = ({ event, contextOverrides }) => {
    const args = {
      event,
      context: {
        ...context,
        ...contextOverrides,
      },
    }
    pixelCompareHandleCompareChange(args)
    solarCurveHandleCompareChange(args)
  }

  const handleMouseMove = ({ rowIndex, event }) => {
    const pointerX = event.pageX // -n to +clientWidthn
    const containerWidth = rootRef.current.clientWidth
    const columnCount = columnNames.length
    let newCurrentColumn = Math.floor((pointerX / containerWidth) * columnCount)
    if (newCurrentColumn >= columnCount) {
      newCurrentColumn = columnCount - 1
    }
    if (newCurrentColumn < 0) {
      newCurrentColumn = 0
    }
    setCurrentRow(rowIndex)
    setCurrentColumn(newCurrentColumn)
    if (currentRow !== rowIndex || currentColumn !== newCurrentColumn) {
      onCompareChange({
        event,
        contextOverrides: {
          currentRow: rowIndex,
          currentColumn: newCurrentColumn,
        },
      })
    }
  }

  const showColumn = (directionOrNum, event) => {
    let nextColumn
    if (typeof directionOrNum === 'string') {
      const direction = directionOrNum
      nextColumn = direction === 'next' ? currentColumn + 1 : currentColumn - 1
      if (nextColumn >= columnNames.length) {
        nextColumn = 0
      } else if (nextColumn < 0) {
        nextColumn = columnNames.length - 1
      }
    } else if (typeof directionOrNum === 'number') {
      nextColumn = directionOrNum - 1
      if (nextColumn > columnNames.length - 1 || nextColumn < 0) {
        return
      }
    }
    setCurrentColumn(nextColumn)
    if (currentColumn !== nextColumn) {
      onCompareChange({
        event,
        contextOverrides: {
          currentColumn: nextColumn,
        },
      })
    }
  }

  const showRow = (direction, event) => {
    let nextRow = direction === 'next' ? currentRow + 1 : currentRow - 1
    if (nextRow > rows.length - 1) {
      nextRow = 0
    } else if (nextRow < 0) {
      nextRow = rows.length - 1
    }
    rowRefs[nextRow].current.scrollIntoView()
    setCurrentRow(nextRow)
    if (currentRow !== nextRow) {
      onCompareChange({
        event,
        contextOverrides: {
          currentRow: nextRow,
        },
      })
    }
  }

  useKeyPrevent('Escape', () => {
    close()
  })

  useKeyPrevent('ArrowLeft, h', (event) => {
    showColumn('prev', event)
  })

  useKeyPrevent('ArrowRight, l', (event) => {
    showColumn('next', event)
  })

  useKeyPrevent('1, 2, 3, 4, 5, 6, 7, 8, 9', (event) => {
    showColumn(parseInt(event.key), event)
  })

  useKeyPrevent('ArrowUp, k', (event) => {
    showRow('prev', event)
  })

  useKeyPrevent('ArrowDown, j', (event) => {
    showRow('next', event)
  })

  return {
    ...context,
    handleMouseMove,
  }
}

export default useScreenshotComparison
