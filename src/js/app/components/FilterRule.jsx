import { useState } from 'react'

const FilterRule = (props) => {
  const [value, setValue] = useState()

  const onChange = (event) => {
    const value = event.target.value
    setValue(value)
  }

  return <input className="Input" type="text" value={value} onChange={onChange} {...props} />
}

export default FilterRule

/*
<FilterRule placeholder="Filter (empty to reset)" />
Example: The search term **HD** returns all rules containing **HD**. The search term **HD+trump** returns all rules containing both **HD** and **trump**.

<FilterRule placeholder="输入关键词" />
示例：搜索 **高清** 得到与 **高清** 相关的规则。搜索词 **高清** + **替代** 得到所有与 **高清** 和 **替代** 相关的规则。

function findRule() {
  var query_string = $('#search_string').val()
  var q = query_string.replace(/\s+/gm, '').split('+')
  var regex = []
  for (var i = 0; i < q.length; i++) {
    regex[i] = new RegExp(q[i], 'mi')
  }
  $('#actual_rules li[id^=r]').each(function () {
    var show = true
    for (var i = 0; i < regex.length; i++) {
      if (!regex[i].test($(this).html())) {
        show = false
        break
      }
    }
    $(this).toggle(show)
  })
  $('.before_rules').toggle(query_string.length === 0)
}

$(document).ready(function () {
  var search_string = $('#search_string')
  var original_value = search_string.val()
  search_string.keyup(findRule)
  search_string.focus(function () {
    if ($(this).val() === original_value) {
      $(this).val('')
    }
  })
  search_string.blur(function () {
    if ($(this).val() === '') {
      $(this).val(original_value)
      $('.before_rules').show()
    }
  })
})
*/
