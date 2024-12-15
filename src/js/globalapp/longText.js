function handleLongText() {
  for (const elem of document.querySelectorAll('.LongText')) {
    let text = elem.querySelector('.LongText-text')
    if (text.scrollHeight > text.clientHeight) {
      text.querySelector('.LongText-btn').style.display = 'block'
    }
  }
}

handleLongText()
