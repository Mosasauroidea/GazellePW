function sendbonus(torrentid, bonus) {
  if (confirm('系统将会收取 10% 的税，确认赠送 ' + bonus + ' 积分给发布者？')) {
    $.ajax({
      url: 'torrents.php',
      data: {
        action: 'sendbonus',
        torrentid: torrentid,
        bonus: bonus,
      },
      type: 'POST',
      success: (data) => {
        if (data.send) {
          $(
            '#bonus' + bonus + torrentid + ',#abonus' + bonus + torrentid
          ).toggle()
          $('#bonuscnt' + torrentid).text(data.count ? data.count : '0')

          if (data.bonus) {
            var sb_nav_bonus = document.getElementById('nav_bonus')
            var sb_tooltip = jQuery(
              sb_nav_bonus.getElementsByClassName('tooltip')[0]
            )
            sb_tooltip.data('tooltipsterContent', '积分 (' + data.bonus + ')')
          }
        } else {
          alert('失败')
        }
      },
      dataType: 'json',
    })
  }
}
