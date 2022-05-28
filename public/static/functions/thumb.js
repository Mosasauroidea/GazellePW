function thumb(itemid, userid, type) {
  $.ajax({
    url: 'user.php',
    data: {
      action: 'thumb',
      itemid: itemid,
      userid: userid,
      type: type,
    },
    type: 'POST',
    success: (data) => {
      switch (type) {
        case 'post':
          $('#thumb' + itemid + ',#unthumb' + itemid).toggle()
          $('#thumbcnt' + itemid).text(data.count == '0' ? '赞' : data.count)
          break
        case 'torrent':
          $('#thumb' + itemid + ',#unthumb' + itemid).toggle()
          $('#thumbcnt' + itemid).text(data.count == '0' ? '赞' : data.count)
          break
      }
    },
    dataType: 'json',
  })
}
function unthumb(itemid, userid, type) {
  $.ajax({
    url: 'user.php',
    data: {
      action: 'unthumb',
      itemid: itemid,
      userid: userid,
      type: type,
    },
    type: 'POST',
    success: (data) => {
      switch (type) {
        case 'post':
          $('#thumb' + itemid + ',#unthumb' + itemid).toggle()
          $('#thumbcnt' + itemid).text(data.count == '0' ? '赞' : data.count)
          break
        case 'torrent':
          $('#thumb' + itemid + ',#unthumb' + itemid).toggle()
          $('#thumbcnt' + itemid).text(data.count == '0' ? '赞' : data.count)
          break
      }
    },
    dataType: 'json',
  })
}
