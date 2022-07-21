import { get } from 'lodash-es'

/*
<Table
  data={data}
  columns=[{
    header: 'Header',
    accessor: 'key',
  }]
  align = 'left' | 'center' | 'right'
/>
*/
export const TableData = ({ data, columns, align = 'left' }) => {
  return (
    <div className="TableContainer">
      <table className="Table">
        <thead>
          {columns.map((column) => (
            <tr className="Table-row" align={align}>
              <th className="Table-cellHeader">{column.header}</th>
            </tr>
          ))}
        </thead>
        <tbody>
          {data.map((row, index) => (
            <tr className="Table-row" align={align}>
              {columns.map((column) => (
                <td className="Table-cell">{get(row, column.accessor)}</td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
