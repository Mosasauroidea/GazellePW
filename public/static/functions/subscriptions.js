function Subscribe(topicid, newName) {
  ajax.get('userhistory.php?action=thread_subscribe&topicid=' + topicid + '&auth=' + authkey, function () {
    var subscribeLink = $('#subscribelink' + topicid)
    var oldName = subscribeLink.html()
    if (newName) {
      subscribeLink.html(newName)
    }
    subscribeLink
      .removeAttr('onclick')
      .off('click')
      .click(function () {
        Subscribe(topicid, oldName)
        return false
      })
  })
}

function SubscribeComments(page, pageid, newName) {
  ajax.get(
    'userhistory.php?action=comments_subscribe&page=' + page + '&pageid=' + pageid + '&auth=' + authkey,
    function () {
      var subscribeLink = $('#subscribelink_' + page + pageid)
      var oldName = subscribeLink.html()
      if (newName) {
        subscribeLink.html(newName)
      }
      subscribeLink
        .removeAttr('onclick')
        .off('click')
        .click(function () {
          SubscribeComments(page, pageid, oldName)
          return false
        })
    }
  )
}

function Collapse(newName) {
  var collapseLink = $('#collapselink')
  if ($('.Table-row').results() > 0) {
    $('.Table-row').gtoggle()
    var oldName = collapseLink.html()
    if (newName) {
      collapseLink.html(newName)
    }
    collapseLink
      .removeAttr('onclick')
      .off('click')
      .click(function () {
        Collapse(oldName)
        return false
      })
  }
}
