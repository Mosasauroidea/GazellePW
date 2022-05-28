/*
<a onclick="globalapp.toggleAny(event, 'targetSelector', { root: 'rootSelector', updateText: false })">

root      # optional
  target: u-hidden 

<button>
  show text: u-toggleAny-show
  hide text: u-toggleAny-hide u-hidden
*/

export default function toggleAny(
  event,
  targetSelector,
  { root: rootSelector, updateText = false } = {}
) {
  event.preventDefault()
  const button = event.target
  const root = rootSelector ? button.closest(rootSelector) : document
  const target = root.querySelector(targetSelector).classList
  const textShow =
    button.parentElement.querySelector('.u-toggleAny-show')?.classList
  const textHide =
    button.parentElement.querySelector('.u-toggleAny-hide')?.classList
  if (target.contains('u-hidden')) {
    target.remove('u-hidden')
  } else {
    target.add('u-hidden')
  }
  if (textShow.contains('u-hidden')) {
    textShow?.remove('u-hidden')
    textHide?.add('u-hidden')
  } else {
    textShow?.add('u-hidden')
    textHide?.remove('u-hidden')
  }
}
