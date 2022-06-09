const offset = 500
const button = document.querySelector('.BackToTop')

document.addEventListener('scroll', () => {
  if (document.documentElement.scrollTop > offset) {
    button.style.bottom = '20px'
  } else {
    button.style.bottom = '-100px'
  }
})

globalapp.backToTop = () => {
  document.body.scrollIntoView({
    behavior: 'smooth',
  })
}
