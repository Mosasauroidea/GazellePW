globalapp.buttonSetLoading = (target, loading) => {
  if (loading) {
    $(target).addClass('is-loading').prop('disabled', true)
  } else {
    $(target).removeClass('is-loading').prop('disabled', false)
  }
}
