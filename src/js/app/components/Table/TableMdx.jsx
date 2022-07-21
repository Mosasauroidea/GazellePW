import { Children, cloneElement } from 'react'

export const table = ({ children }) => {
  return (
    <div className="TableContainer">
      <table class="Table">{tableMap(children)}</table>
    </div>
  )
}
const tableClassNames = {
  tr: 'Table-row',
  th: 'Table-cellHeader',
  td: 'Table-cell',
}
const tableMap = (children) => {
  return Children.map(children, (element) => {
    const type = element.type
    const className = tableClassNames[type]
    const children = tableMap(element.props && element.props.children)
    return cloneElement(element, { className, children })
  })
}
