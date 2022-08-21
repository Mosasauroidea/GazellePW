$(document).ready(function () {
  $('#paster').click(function () {
    var info =
      $('#join-date-value').text().replace(/\s/g, '') +
      '\n' +
      $('#last-access-date-value').text().replace(/\s/g, '') +
      '\n' +
      $('#uploaded-value').text().replace(/\s/g, '') +
      '\n' +
      $('#downloaded-value').text().replace(/\s/g, '') +
      '\n' +
      $('#ratio-value').text().replace(/\s/g, '') +
      '\n' +
      $('#required-ratio-value').text().replace(/\s/g, '') +
      '\n'
    $('#Reason').val($('#Reason').val() + info)
    $('#Reason').height($('#Reason')[0].scrollHeight)
  })
})
