document.querySelector('.Dropdown-trigger').addEventListener('click', (e) => {
  e.target.closest('.Dropdown').classList.toggle('is-open')
})
