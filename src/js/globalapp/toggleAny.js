/*
toggle targetSelector `u-hidden` class.

<div class="targetSelector u-hidden"> // support multiple targets
<a href='#' onclick="globalapp.toggleAny(event, '.targetSelector')>
  <span class="u-toggleAny-show">Show</span>   // optional
  <span class="u-toggleAny-hide u-hidden">Hide</span>
*/

globalapp.toggleAny = function toggleAny(
  event,
  targetSelector,
  { closest: closestSelector, hideSelf = false } = {}
) {
  event.preventDefault()
  const self = event.target
  const root = closestSelector ? self.closest(closestSelector) : document
  for (const targetElement of root.querySelectorAll(targetSelector)) {
    const target = targetElement.classList
    if (target.contains('u-hidden')) {
      target.remove('u-hidden')
    } else {
      target.add('u-hidden')
    }
  }

  const textShow =
    self.parentElement.querySelector('.u-toggleAny-show')?.classList
  const textHide =
    self.parentElement.querySelector('.u-toggleAny-hide')?.classList
  if (textShow && textHide) {
    if (textShow.contains('u-hidden')) {
      textShow.remove('u-hidden')
      textHide.add('u-hidden')
    } else {
      textShow.add('u-hidden')
      textHide.remove('u-hidden')
    }
  }

  if (hideSelf) {
    self.classList.add('u-hidden')
  }
}
