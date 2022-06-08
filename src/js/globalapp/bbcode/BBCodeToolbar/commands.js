const commands = {
  bold: {
    hotkey: 'ctrl+b',
    exec(toolbar) {
      toolbar.surroundSelectedText('[b]', '[/b]', 'inline')
    },
  },
  italic: {
    hotkey: 'ctrl+i',
    exec(toolbar) {
      toolbar.surroundSelectedText('[i]', '[/i]', 'inline')
    },
  },
  underline: {
    hotkey: 'ctrl+u',
    exec(toolbar) {
      toolbar.surroundSelectedText('[u]', '[/u]', 'inline')
    },
  },
  strikethrough: {
    hotkey: 'ctrl+s',
    exec(toolbar) {
      toolbar.surroundSelectedText('[s]', '[/s]', 'inline')
    },
  },
  image: {
    hotkey: 'ctrl+m',
    exec(toolbar) {
      toolbar.surroundSelectedText('[img]', '[/img]', 'inline')
    },
  },
  spoiler: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[spoiler]', '[/spoiler]', 'block')
    },
  },
  hide: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[hide]', '[/hide]', 'block')
    },
  },
  quote: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[quote]', '[/quote]', 'block')
    },
  },
  alignLeft: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[align=left]', '[/align]', 'inline')
    },
  },
  alignCenter: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[align=center]', '[/align]', 'inline')
    },
  },
  alignRight: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[align=right]', '[/align]', 'inline')
    },
  },
  code: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[code]', '[/code]', 'block')
    },
  },
  video: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[video]', '[/video]', 'inline')
    },
  },
  mediainfo: {
    exec(toolbar) {
      toolbar.surroundSelectedText('[mediainfo]', '[/mediainfo]', 'block')
    },
  },
  comparison: {
    exec(toolbar) {
      toolbar.surroundSelectedText(
        '[comparison=Source, Encode]',
        '[/comparison]',
        'block'
      )
    },
  },
  link: {
    hotkey: ['ctrl+h', 'ctrl+k'],
    // exec: this.BbCodeLink,
  },
  list: {
    // exec: toolbar.BbCodeList(),
  },
  size: {
    // exec: toolbar.BbCodeSize(),
  },
  color: {
    // exec: toolbar.BbCodeColor(),
  },
  youtube: {
    // exec: toolbar.BbCodeYoutube(),
  },
  staff: {
    // exec: toolbar.BbCodeStaff(),
  },
  emoticon: {
    // exec: toolbar.BbCodeEmoticon(),
  },
}

export default commands

/*
  BbCodeLink(event) {
    var toolbar = this

    function OnOkPressed() {
      var textField = toolbar.getTextField()
      var link = $('#BbcodePopupLink').val()
      toolbar.surroundSelectedText(
        textField.get(0),
        '[url=' + link + ']',
        '[/url]'
      )
      $('.js-bbcode-toolbar__link-button', toolbar.toolbarEl).qtip('hide')
    }

    function OnCancelPressed() {
      $('.js-bbcode-toolbar__link-button', toolbar.toolbarEl).qtip('hide')
      toolbar.getTextField().focus()
    }

    var dialog =
      '<div>Link URL:<br>' +
      '<input class="Input" type="text" id="BbcodePopupLink" size="64" /><br>' +
      '<input class="Button" type="button" value="OK" id="BbcodePopupLinkOkButton" />' +
      '<input class="Button" type="button" value="Cancel" id="BbcodePopupLinkCancelButton"/>' +
      '</div>'

    $('.js-bbcode-toolbar__link-button', toolbar.toolbarEl)
      .qtip({
        position: {
          at: 'left bottom',
          adjust: { method: 'none none' },
          viewport: $(window),
        },
        content: { text: dialog },
        show: { event: false },
        hide: {
          delay: 100,
          event: 'unfocus',
          fixed: true,
        },
        style: {
          classes: 'qtip-ptp qtip-shadow',
          tip: false,
        },
        events: {
          hide: function () {
            // This is needed to make sure that the tooltip contents get destroyed, so multiple toolbars
            // can work. This wouldn't be needed if the tooltip contents didn't use IDs.
            $(this).qtip('destroy')
          },

          render: function (event, api) {
            $('#BbcodePopupLinkOkButton').click(function () {
              OnOkPressed()
              return false
            })

            $('#BbcodePopupLinkCancelButton').click(function () {
              OnCancelPressed()
              return false
            })

            $('#BbcodePopupLink').keydown(function (event) {
              if (event.which == 13) {
                // Enter
                OnOkPressed()
                event.preventDefault()
              } else if (event.which == 27) {
                // Escape
                OnCancelPressed()
                event.preventDefault()
              }
            })
          },
        },
      })
      .qtip('show')

    $('#BbcodePopupLink').focus()

    return false
  }

  BbCodeList(event) {
    var textField = this.getTextField()
    var listText = ''

    // Each non-empty line will be a separate list item.
    var selection = this.getSelectedText(textField.get(0))
    if (selection.length > 0) {
      var lines = selection.split(/\n/)
      for (var i = 0; i < lines.length; ++i) {
        var line = $jq.trim(lines[i])
        if (line.length > 0) {
          if (listText.length > 0) listText += '\n'
          listText += '[*] ' + line
        }
      }
    } else {
      listText = '[*] '
    }

    this.replaceSelectedText(textField.get(0), listText)

    return false
  }

  BbCodeSize(event) {
    var toolbar = this

    var dialog =
      '<div>' +
      '<div class="bbcode-toolbar__size-popup-row js-bbcode-toolbar__size-popup-row" data-size="1"><span class="bbcode-size-1">Size 1</span></div>' +
      '<div class="bbcode-toolbar__size-popup-row js-bbcode-toolbar__size-popup-row" data-size="2"><span class="bbcode-size-2">Size 2 (normal)</span></div>' +
      '<div class="bbcode-toolbar__size-popup-row js-bbcode-toolbar__size-popup-row" data-size="3"><span class="bbcode-size-3">Size 3</span></div>' +
      '<div class="bbcode-toolbar__size-popup-row js-bbcode-toolbar__size-popup-row" data-size="4"><span class="bbcode-size-4">Size 4</span></div>' +
      '<div class="bbcode-toolbar__size-popup-row js-bbcode-toolbar__size-popup-row" data-size="6"><span class="bbcode-size-6">Size 6</span></div>' +
      '<div class="bbcode-toolbar__size-popup-row js-bbcode-toolbar__size-popup-row" data-size="8"><span class="bbcode-size-8">Size 8</span></div>' +
      '<div class="bbcode-toolbar__size-popup-row js-bbcode-toolbar__size-popup-row" data-size="10"><span class="bbcode-size-10">Size 10</span></div>' +
      '</div>'

    $('.js-bbcode-toolbar__size-button', toolbar.toolbarEl)
      .qtip({
        position: {
          at: 'left bottom',
          adjust: { method: 'none none' },
          viewport: $(window),
        },
        content: { text: dialog },
        show: { event: false },
        hide: {
          delay: 100,
          event: 'unfocus',
          fixed: true,
        },
        style: {
          classes: 'qtip-ptp qtip-shadow',
          tip: false,
        },
        events: {
          render: function (event, api) {
            $('.js-bbcode-toolbar__size-popup-row').click(function () {
              var textField = toolbar.getTextField()
              var size = $(this).attr('data-size')
              toolbar.surroundSelectedText(
                textField.get(0),
                '[size=' + size + ']',
                '[/size]'
              )
              api.hide()
              return false
            })
          },
        },
      })
      .qtip('show')

    return false
  }

  BbCodeColor(event) {
    var toolbar = this

    // The colors were taken from GMail.
    var colors = [
      '000000',
      '444444',
      '666666',
      '999999',
      'cccccc',
      'eeeeee',
      'f3f3f3',
      'ffffff',
      'ff0000',
      'ff9900',
      'ffff00',
      '00ff00',
      '00ffff',
      '0000ff',
      '9900ff',
      'ff00ff',
      'f4cccc',
      'fce5cd',
      'fff2cc',
      'd9ead3',
      'd0e0e3',
      'cfe2f3',
      'd9d2e9',
      'ead1dc',
      'ea9999',
      'f9cb9c',
      'ffe599',
      'b6d7a8',
      'a2c4c9',
      '9fc5e8',
      'b4a7d6',
      'd5a6bd',
      'e06666',
      'f6b26b',
      'ffd966',
      '93c47d',
      '76a5af',
      '6fa8dc',
      '8e7cc3',
      'c27ba0',
      'cc0000',
      'e69138',
      'f1c232',
      '6aa84f',
      '45818e',
      '3d85c6',
      '674ea7',
      'a64d79',
      '990000',
      'b45f06',
      'bf9000',
      '38761d',
      '134f5c',
      '0b5394',
      '351c75',
      '741b47',
      '660000',
      '783f04',
      '7f6000',
      '274e13',
      '0c343d',
      '073763',
      '20124d',
      '4c1130',
    ]
    var dialog = "<div style='line-height: 0;'><div>"
    for (var i = 0; i < colors.length; ++i) {
      dialog +=
        '<span class="bbcode-toolbar__color-popup-color js-bbcode-toolbar__color-popup-color" data-color="' +
        colors[i] +
        '" style="background-color: #' +
        colors[i] +
        '"></span>'
      if ((i + 1) % 8 == 0) {
        dialog += '</div>'
        if (i < 63) dialog += '<div>'
      }
    }
    dialog += '</div>'

    $('.js-bbcode-toolbar__color-button', toolbar.toolbarEl)
      .qtip({
        position: {
          at: 'left bottom',
          adjust: { method: 'none none' },
          viewport: $(window),
        },
        content: { text: dialog },
        show: { event: false },
        hide: {
          delay: 100,
          event: 'unfocus',
          fixed: true,
        },
        style: {
          classes: 'qtip-ptp qtip-shadow',
          tip: false,
        },
        events: {
          render: function (event, api) {
            $('.js-bbcode-toolbar__color-popup-color').click(function () {
              var textField = toolbar.getTextField()
              var color = $(this).attr('data-color')
              toolbar.surroundSelectedText(
                textField.get(0),
                '[color=#' + color + ']',
                '[/color]'
              )
              api.hide()
              return false
            })
          },
        },
      })
      .qtip('show')

    return false
  }

  BbCodeEmoticon(event) {
    function AddOne(imageName, code, title, width, height) {
      return (
        '<img src="https://static.passthepopcorn.me/static/common/smileys/' +
        imageName +
        '"' +
        ' data-code="' +
        code +
        '"' +
        ' data-tooltip="' +
        title +
        '"' +
        ' class="bbcode-toolbar__emoticon-popup-item js-bbcode-toolbar__emoticon-popup-item"' +
        ' style="width: ' +
        width +
        'px; height: ' +
        height +
        'px;" />'
      )
    }

    var toolbar = this

    var dialog =
      '<div>' +
      '<div>' +
      AddOne('smile.gif', ':)', 'Smile', 20, 20) +
      AddOne('sad.gif', ':(', 'Sad', 20, 20) +
      AddOne('biggrin.gif', ':D', 'Big grin', 20, 20) +
      AddOne('ohshit.gif', ':o', 'Surprised', 20, 20) +
      AddOne('tongue.gif', ':P', 'Tongue out', 20, 20) +
      AddOne('blank.gif', ':|', 'Straight face', 20, 20) +
      '</div>' +
      '<div>' +
      AddOne('angry.gif', ':angry:', 'Angry', 20, 20) +
      AddOne('blush.gif', ':blush:', 'Blushing', 20, 20) +
      AddOne('cool.gif', ':cool:', 'Cool', 20, 20) +
      AddOne('creepy.gif', ':creepy:', 'Creepy', 20, 20) +
      AddOne('crying.gif', ":'(", 'Crying', 20, 20) +
      AddOne('eyesright.gif', '&gt;.&gt;', 'Eyes right', 20, 20) +
      '</div>' +
      '<div>' +
      AddOne('frown.gif', ':frown:', 'Frowning', 20, 20) +
      AddOne('hmm.gif', ':unsure:', 'Unsure', 20, 20) +
      AddOne('laughing.gif', ':lol:', 'LOL', 20, 20) +
      AddOne('ninja.gif', ':ninja:', 'Ninja', 20, 20) +
      AddOne('no.gif', ':no:', 'No', 20, 20) +
      AddOne('nod.gif', ':nod:', 'Nodding', 20, 20) +
      '</div>' +
      '<div>' +
      AddOne('ohnoes.gif', ':ohno:', 'Oh, no!', 20, 20) +
      AddOne('omg.gif', ':omg:', 'OMG!', 20, 20) +
      AddOne('shifty.gif', ':shifty:', 'Shifty', 20, 20) +
      AddOne('sick.gif', ':sick:', 'Sick', 20, 20) +
      AddOne('wink.gif', ':wink:', 'Winking', 20, 20) +
      AddOne('worried.gif', ':worried:', 'Worried', 20, 20) +
      '</div>' +
      '<div>' +
      AddOne('wtf.gif', ':wtf:', 'WTF?!', 20, 20) +
      AddOne('wub.gif', ':wub:', 'Wub', 20, 30) +
      AddOne('paddle.gif', ':paddle:', 'Paddle', 28, 28) +
      AddOne('wave.gif', ':wave:', 'Waves', 27, 21) +
      AddOne('heart.gif', '&lt;3', 'Heart', 21, 18) +
      '</div>' +
      '<div>' +
      AddOne('thanks.gif', ':thanks:', 'Thanks!', 40, 31) +
      AddOne('crom.gif', ':crom:', 'Thanks!', 35, 29) +
      AddOne('sorry.gif', ':sorry:', 'Sorry!', 38, 31) +
      '</div>' +
      '</div>'

    $('.js-bbcode-toolbar__emoticon-button', toolbar.toolbarEl)
      .qtip({
        position: {
          at: 'left bottom',
          adjust: { method: 'none none' },
          viewport: $(window),
        },
        content: { text: dialog },
        show: { event: false },
        hide: {
          delay: 100,
          event: 'unfocus',
          fixed: true,
        },
        style: {
          classes: 'qtip-ptp qtip-shadow',
          tip: false,
        },
        events: {
          render: function (event, api) {
            $('.js-bbcode-toolbar__emoticon-popup-item').click(function () {
              var textField = toolbar.getTextField()
              var code = $(this).attr('data-code')
              toolbar.insertText(textField.get(0), ' ' + code)
              api.hide()
              return false
            })
          },
        },
      })
      .qtip('show')

    return false
  }

  BbCodeStaff(event) {
    var textField = this.getTextField()
    var username = $('.js-bbcode-toolbar__staff-button', this.toolbarEl).data(
      'username'
    )
    this.surroundSelectedText(
      textField.get(0),
      '[staff=' + username + ']',
      '[/staff]'
    )

    return false
  }

  */
