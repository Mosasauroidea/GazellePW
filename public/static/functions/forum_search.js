$(document).ready(function () {
  $('#type_body').click(function () {
    $('#post_created_row').gshow()
  })
  $('#type_title').click(function () {
    $('#post_created_row').ghide()
  })
})

function toggleAll(id) {
  var isChecked = $('#' + id).raw().checked
  $("input[data-category='" + id + "']").attr('checked', isChecked)
}
