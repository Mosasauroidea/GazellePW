/* Snackbar.css */

import cx from 'classnames'

const Snackbar = ({ message, onClick, type }) => {
  return (
    <div className={cx('Snackbar', type && `is-${type}`)} onClick={onClick}>
      <div className="Snackbar-body">{message}</div>
    </div>
  )
}

export default Snackbar
