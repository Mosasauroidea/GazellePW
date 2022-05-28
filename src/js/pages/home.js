if (window.matchMedia('(max-width: 768px)').matches) {
  for (const button of document.querySelectorAll(
    '.Post-toggleButton:not(.is-sticky)'
  )) {
    button.click()
  }
}
