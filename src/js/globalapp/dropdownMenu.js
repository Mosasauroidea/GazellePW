for (const trigger of document.querySelectorAll('.Dropdown-trigger')) {
  trigger.addEventListener('click', (e) => {
    e.target.closest('.Dropdown').classList.toggle('is-open')
  })
}
