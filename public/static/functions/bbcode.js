var BBCode = {
  spoiler: function (link) {
    var sibling = $(link.nextSibling)
    // 把 display:none 从 class 改为 style
    if (sibling.has_class('hidden')) {
      sibling.css('display', 'none').remove_class('hidden')
    }

    // 使用 jquery 的显隐效果
    sibling.slideToggle(300, 'linear', function () {
      if (sibling.is(':hidden')) {
        link.innerHTML = 'Show'
      } else {
        link.innerHTML = 'Hide'
      }
    })
  },
}
