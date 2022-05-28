function PreviewTitle(BBCode) {
  $.post(
    'bonus.php?action=title&preview=true',
    {
      title: $('#title').val(),
      BBCode: BBCode,
    },
    function (response) {
      $('#preview').html(response)
    }
  )
}

function NoOp(event, item, next, element) {
  return next && next(event, element)
}

/**
 * @param {Object} event
 * @param {String} item
 * @param {Function} next
 * @param {Object} element
 * @return {boolean}
 */
function ConfirmPurchase(event, item, next, element) {
  var check = next ? next(event, element) : true
  if (!check) {
    event.preventDefault()
    return false
  }
  check = confirm(translation.format(translation.get('confirm_purchase'), item))
  if (!check) {
    event.preventDefault()
    return false
  }
  return true
}
/**
 * @return {boolean}
 */
function ConfirmOther(event, element) {
  var name = prompt(translation.get('confirm_username'))
  if (!name || name === '') {
    return false
  }
  $(element).attr(
    'href',
    $(element).attr('href') + '&user=' + encodeURIComponent(name)
  )
  return true
}
