/* Chart.css */
import { forwardRef } from 'react'
import { merge } from 'lodash'
import Highcharts from 'highcharts'
import HighchartsReact from 'highcharts-react-official'
import ReactResizeDetector from 'react-resize-detector'

const DEFAULT_OPTIONS = {
  chart: { styledMode: true },
  credits: {
    enabled: false,
  },
  accessibility: {
    enabled: false,
  },
}

export const Chart = forwardRef(({ options, containerProps, ...rest }, ref) => (
  <ReactResizeDetector handleWidth handleHeight>
    {({ width, height }) => {
      options = merge({}, DEFAULT_OPTIONS, options, {
        chart: { width, height },
      })
      return (
        <HighchartsReact
          ref={ref}
          highcharts={Highcharts}
          options={options}
          containerProps={{
            // style: { height: '100%' },
            ...containerProps,
          }}
          {...rest}
        />
      )
    }}
  </ReactResizeDetector>
))
