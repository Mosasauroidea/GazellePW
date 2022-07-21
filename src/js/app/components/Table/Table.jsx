export const Table = ({ children, ...rest }) => (
  <div className="TableContainer">
    <table className="Table" {...rest}>
      {children}
    </table>
  </div>
)

export const Tr = ({ children, ...rest }) => (
  <tr className="Table-row" {...rest}>
    {children}
  </tr>
)

export const Th = ({ children, ...rest }) => (
  <th className="Table-cellHeader" {...rest}>
    {children}
  </th>
)

export const Td = ({ children, ...rest }) => (
  <td className="Table-cell" {...rest}>
    {children}
  </td>
)
