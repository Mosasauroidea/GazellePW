/*
lightbox.css
*/

class Lightbox {
  init(image) {
    this.ShownImage = null

    if (image.naturalWidth === undefined) {
      const tmp = document.createElement('img')
      tmp.style.visibility = 'hidden'
      tmp.src = image.src
      image.naturalWidth = tmp.width
      tmp.parentNode.removeChild(tmp)
    }
    this.box(image)
  }

  box(image) {
    // Disable background scrolling. http://stackoverflow.com/a/13891717
    if ($(document).height() > $(window).height()) {
      const htmlNode = $('html')
      const bodyNode = $('body')
      const oldWidth = htmlNode.innerWidth()
      this.OriginalLeft = bodyNode.css('left')
      this.OriginalTop = bodyNode.css('top')
      this.OriginalScrollTop = htmlNode.scrollTop()
        ? htmlNode.scrollTop()
        : bodyNode.scrollTop() // Works for Chrome, Firefox, IE...

      bodyNode.addClass('lightbox__scroll-lock')
      bodyNode.css('top', -this.OriginalScrollTop)

      const newWidth = htmlNode.innerWidth()
      bodyNode.css('left', oldWidth - newWidth + 'px')
    }

    const lightboxNode = $('#lightbox')
    lightboxNode.html('<img src="' + image.src + '" />')
    lightboxNode.removeClass('hidden')
    lightboxNode.scrollTop(0)
    lightboxNode.focus()
    lightboxNode.click(this.unbox.bind(this))

    $('#lightbox__shroud').removeClass('hidden').click(this.unbox.bind(this))

    this.ShownImage = image
    this.BindHotKey('up', () => {
      return this.ShowNextImage(false)
    })
    this.BindHotKey('down', () => {
      return this.ShowNextImage(true)
    })
    this.BindHotKey('left', () => {
      return this.ShowNextImage(false)
    })
    this.BindHotKey('right', () => {
      return this.ShowNextImage(true)
    })
    this.BindHotKey('escape', () => {
      this.unbox()
    })
  }

  unbox() {
    // Unbind doesn't work
    // https://github.com/ccampbell/mousetrap/issues/306
    this.BindHotKey('up', function () {})
    this.BindHotKey('down', function () {})
    this.BindHotKey('left', function () {})
    this.BindHotKey('right', function () {})
    this.BindHotKey('escape', function () {})
    this.ShownImage = null

    $('#lightbox__shroud').addClass('hidden').off('click')
    $('#lightbox').addClass('hidden').html('').off('click')

    const bodyNode = $('body')
    bodyNode.removeClass('lightbox__scroll-lock')
    bodyNode.css('left', this.OriginalLeft)
    bodyNode.css('top', this.OriginalTop)
    $('html,body').scrollTop(this.OriginalScrollTop)
  }

  BindHotKey(hotkey, callback) {
    Mousetrap.bindGlobal(hotkey, callback, 'keydown')
  }

  ShowNextImage(showNext) {
    let lightboxImgElement = $('#lightbox > img')
    if (lightboxImgElement.length !== 1) return false

    lightboxImgElement = lightboxImgElement.get(0)

    let image
    if (showNext) {
      image = $(this.ShownImage).nextAll('.bbcode__image').first()
    } else {
      image = $(this.ShownImage).prevAll('.bbcode__image').first()
    }

    if (image.length === 0) return false

    this.ShownImage = image.get(0)
    lightboxImgElement.src = this.ShownImage.src

    return false
  }
}

window.lightbox = new Lightbox()
