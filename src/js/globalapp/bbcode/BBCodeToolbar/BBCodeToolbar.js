import '#/js/forked/mousetrap.min'
import commands from './commands'

/*
BBCodeToolbar.css
*/
export default class BBCodeToolbar {
  constructor({ textarea, toolbar }) {
    this.textareaEl = textarea
    this.toolbarEl = toolbar
  }

  register() {
    if (!this.toolbarEl) {
      return
    }
    this.toolbarEl.addEventListener('click', (e) => {
      const cmdName = e.target.closest('[data-cmd]')?.getAttribute('data-cmd')
      if (!cmdName) {
        return
      }
      commands[cmdName].exec(this)
    })
    for (const cmd of Object.values(commands)) {
      if (cmd.hotkey) {
        this.bindHotKey(cmd.hotkey, () => cmd.exec(this))
      }
    }
    this.registerUploadTool()
  }

  registerUploadTool() {
    const progressBar = $(this.toolbarEl).find('.BBCodeToolbar-uploadProgressBar')
    const progress = $(this.toolbarEl).find('.BBCodeToolbar-uploadProgress')
    const progressText = $(this.toolbarEl).find('.BBCodeToolbar-uploadProgressText')
    const input = $(this.toolbarEl).find('.BBCodeToolBar-imageUpload')
    const button = $(this.toolbarEl).find('.BBCodeToolbar-uploadButton')
    const textarea = this.textareaEl
    const replacedName = '{{ IMAGE }}'
    const threshold = 90
    input.fileupload({
      dataType: 'json',
      loadImageMaxFileSize: 52428800,
      imageMaxWidth: 7680,
      imageMaxHeight: 4320,
      singleFileUploads: false,
      acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
      maxFileSize: 52428800,
      dropZone: $(this.textareaEl),
      pasteZone: $(this.textareaEl),
      url: 'upload.php?action=imgupload',
      progressall: function (e, data) {
        var progress = parseInt((data.loaded / data.total) * 100, 10)
        if (progress > threshold) {
          progress = threshold
        }
        progressBar.css('width', progress + '%')
        progressText.text(t('client.upload.uploading') + progress + '%')
      },
      formData: input.serializeArray(),
      add: function (e, data) {
        data.submit()
        progress.show()
        button.prop('disabled', true)
        textarea.value = textarea.value + replacedName
      },
      done: function (e, data) {
        if (data.result.error) {
          progressText.text(data.result.error)
          textarea.value = textarea.value.replace(replacedName, '')
        } else {
          let newText = ''
          data.result.files.forEach((file, index) => {
            newText += '[img]' + file.name + '[/img]\n'
          })
          textarea.value = textarea.value.replace(replacedName, newText)
          progressText.text(t('client.upload.uploaded'))
          progressBar.css('width', '100%')
        }
        progress.fadeOut(5000)
        button.prop('disabled', false)
      },
      fail: function (e) {
        textarea.value = textarea.value.replace(replacedName, '')
        button.prop('disabled', false)
        progressText.text('Unknown error')
        progress.fadeOut(5000)
      },
    })
  }

  surroundSelectedText(before, after, type) {
    const el = this.textareaEl
    const text = el.value
    const textBefore = text.slice(0, el.selectionStart)
    const textSelected = text.slice(el.selectionStart, el.selectionEnd)
    const textAfter = text.slice(el.selectionEnd)
    let selected = textSelected
    // move \n to <after>
    if (selected.match(/\n$/)) {
      selected = selected.trimEnd()
      after = after + '\n'
    }
    if (type === 'block') {
      selected = `\n${selected.trim()}\n`
    }
    const newText = textBefore + before + selected + after + textAfter
    // move cursor
    // no selection -> move to middle
    // has selection -> move to end
    let newCursor
    if (el.selectionEnd === el.selectionStart) {
      newCursor = (textBefore + before).length
    } else {
      newCursor = (textBefore + before + selected + after).length
    }
    this.textareaEl.value = newText
    this.moveCursor(newCursor)
    el.focus()
  }

  /*
  insertText(textArea, newText) {
    const selection = $(textArea).getSelection()
    const selectedText = selection.text
    const text = textArea.value
    textArea.value =
      text.slice(0, selection.start) +
      selectedText +
      newText +
      text.slice(selection.end)
    const index = selection.start + selectedText.length + newText.length
    $(textArea).setSelection(index, index)
    textArea.focus()
  }

  replaceSelectedText(textArea, newText) {
    const selection = $(textArea).getSelection()
    const text = textArea.value
    textArea.value =
      text.slice(0, selection.start) + newText + text.slice(selection.end)
    const index = selection.start + newText.length
    $(textArea).setSelection(index, index)
    textArea.focus()
  }
  */

  moveCursor(cursor) {
    this.textareaEl.setSelectionRange(cursor, cursor)
  }

  isFocus() {
    return this.textareaEl === document.activeElement
  }

  bindHotKey(hotkey, callback) {
    Mousetrap.bindGlobal(
      hotkey,
      () => {
        if (this.isFocus()) {
          callback.call(this, null)
          return false
        }
        return true
      },
      'keydown'
    )
  }
}
