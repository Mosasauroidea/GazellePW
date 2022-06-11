const button = document.querySelector('.BackToTop')
const offset = parseInt(
  getComputedStyle(button).getPropertyValue('--scrollOffset')
)
const opacity = getComputedStyle(button).getPropertyValue('--buttonOpacity')

document.addEventListener('scroll', () => {
  if (document.documentElement.scrollTop > offset) {
    button.style.transition = 'visibility 0s linear 0s, opacity 1s linear'
    button.style.visibility = 'visible'
    button.style.opacity = opacity
  } else {
    button.style.transition = 'visibility 0s linear 1s, opacity 1s linear'
    button.style.visibility = 'hidden'
    button.style.opacity = 0
  }
})

globalapp.backToTop = () => {
  document.body.scrollIntoView({
    behavior: 'smooth',
  })
}
