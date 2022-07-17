$(document).ready(function () {
  $('.forum_category').click(function (e) {
    var id = this.id
    var isChecked = $(this).text() != lang.get('common.check_all')
    isChecked
      ? $(this).text(lang.get('common.check_all'))
      : $(this).text(lang.get('common.uncheck_all'))
    $("input[data-category='" + id + "']").attr('checked', !isChecked)
    e.preventDefault()
  })

  $('#type_body').click(function () {
    $('#post_created_row').gshow()
  })
  $('#type_title').click(function () {
    $('#post_created_row').ghide()
  })
})
