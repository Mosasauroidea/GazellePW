function main() {
  if (!$('.MovieInfo-synopsis')[0]) {
    return
  }
  synopsisToggle()
  $(window).resize(synopsisToggle)
  $('.MovieInfo-synopsis').click(() => {
    $('.MovieInfo-synopsis').toggleClass('expand')
  })
}

function synopsisToggle() {
  const tooltip = $('.MovieInfo-synopsis').tooltipster('content')
  if (isOverflown($('.MovieInfo-synopsis > p')[0])) {
    $('.MovieInfo-synopsis').addClass('overflown')
    $('.MovieInfo-synopsis').tooltipster('content', tooltip)
  } else {
    $('.MovieInfo-synopsis').removeClass('overflown')
    $('.MovieInfo-synopsis').tooltipster('content', null)
  }
}

function isOverflown(ele) {
  return ele.scrollHeight > ele.clientHeight
}

main()
