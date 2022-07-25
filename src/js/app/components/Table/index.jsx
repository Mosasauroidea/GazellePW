import { TableData } from './TableData'
export * from './TableMdx'
export * from './Table'
export { TableData }

export const TableClientWhitelist = () => (
  <TableData
    columns={[{ header: t('client.other.client_whitelist'), accessor: 'ClientName' }]}
    data={window.DATA.clients}
  />
)
