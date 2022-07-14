export default {
  like: '赞',
  copied: '已复制',
  view: '查看',
  clear: '清除',
  hide: '隐藏',
  main: '主要',
  director: '导演',
  producer: '制片',
  composer: '作曲',
  cinematographer: '摄影',
  writer: '编剧',
  actor: '演员',
  confirm_purchase: '你确定要购买 {0}吗？',
  confirm_username: '请输入受赠者的用户名:',
  confirm_username: 'Enter username to give tokens to:',
  in_this_release_type: '本发布类型',
  on_this_page: '本页面',
  collapse_this_group:
    '折叠本影片。在左键单击的同时按住 [Command] <em>(Mac)</em> 或 [Ctrl] <em>(PC)</em> 键以折叠{0}所有的影片。',
  expand_this_group:
    '展开本影片。在左键单击的同时按住 [Command] <em>(Mac)</em> 或 [Ctrl] <em>(PC)</em> 键以展开{0}所有的影片。',
  are_you_sure_cannot_undone: '你确定要这样操作吗？该操作不可撤销！',
  request_has_been_unresolved: '请求已被归入未处理类别。请刷新浏览器查看。',
  pm_user_on_edit:
    '<span id="pmbox{0}"><label>编辑后私信通知发帖人？<input type="checkbox" name="pm" value="1" /></label></span>',
  are_you_sure_you_want_to_cancel: '你确定要取消吗？',
  are_you_sure_you_wish_to_delete_this_post: '你确定要删除这条回帖吗？',
  add_torrent_description: '添加种子描述',
  a_main_artist_is_required: '至少要有一个主要艺人',
  this_field_is_required: '此栏必填。',
  please_fix_this_field: '请修正此栏。',
  please_enter_a_valid_email_address: '请输入一个有效的邮箱地址。',
  please_enter_a_valid_url: '请输入一个有效的网址。',
  please_enter_a_valid_date: '请输入一个有效的日期。',
  please_enter_a_valid_date_iso: '请输入一个有效的日期（ISO）。',
  please_enter_a_valid_date_number: '请输入一个有效的数字。',
  please_enter_only_digits: '请输入纯数字。',
  please_enter_a_valid_credit_card_number: '请输入一个有效的信用卡号。',
  please_enter_the_same_value_again: '请重复输入一遍。',
  please_enter_no_more_than_n_characters: '请输入不超过 {0} 个字符。',
  please_enter_at_least_n_characters: '请输入至少 {0} 个字符。',
  please_enter_a_value_between_x_and_y_characters_long:
    '请输入一个介于 {0} 到 {1} 字符长度之间的值。',
  please_enter_a_value_between_x_and_y: '请输入一个介于 {0} 到 {1} 之间的值。',
  please_enter_a_value_less_than_or_equal_to_n: '请输入一个小于等于 {0} 的值。',
  please_enter_a_value_greater_than_or_equal_to_n:
    '请输入一个大于等于 {0} 的值。',
  check_all: '全选',
  uncheck_all: '取消全选',
  hide: '隐藏',
  show: '显示',

  screenshot_comparison: {
    help: `按 ? 键查看帮助文档`,
    help_title: '截图对比器帮助文档',
    loading: '加载中……',
    pixel_compare: '像素对比',
    solar_curve: '曲线滤镜',
    gpw_helper_not_installed:
      '请先安装 <a href="https://greasyfork.org/scripts/445653-gpw-helper/code/GPW-Helper.user.js" target="_blank">GPW 助手</a>, 然后重新加载此页面',
  },

  // Upload Page
  upload: {
    torrent_file_required: '请选中一个种子文件！',
    imdb_link_required: '请填写格式正确的 IMDb 链接或 ID！',
    releasetype_required:
      '请根据 <a href="/rules.php?p=upload">发布规则</a> 的定义指定片种！',
    movie_title_required: '请填写电影名！',
    year_required: '请填写电影首次公映的年份！',
    poster_required:
      '请填写电影海报图链！如果一张都找不到，请从电影整片里截一张并添上电影名。',
    movie_desc_required: '请填写电影简介！',
    source_required:
      '请选择片源。如果你不确定，请选 “Other”，然后输入 “Unknown”。',
    tag_required: '请填写至少一个类型标签！',
    subtitles_required: '请指明文件所包含的字幕，如果没有就勾选 “无字幕”。',
    subtitles_with_mediainfo:
      'MediaInfo 未提供语言信息，请根据实际情况手动勾选。',
    codec_required: '请选择一个编码。',
    resolution_required: '请选择一个分辨率。',
    container_required: '请选择一个容器。',
    processing_required: '请选择一个处理。',
    remaster_required: '请写明种子包含的非主体内容具体是什么',
    mediainfo_required: `必须填写 MediaInfo（或 BDInfo）。<br/>
      对于 DVD 原盘，请填写最大的 VOB 文件以及在它之前的（按文件名排序）、包含了片长信息的 IFO 文件的 MediaInfo 日志。`,
    mediainfo_invalid_chars: `你填入的 MediaInfo/BDInfo 存在错误，请自用 MdiaInfo 工具扫描获取，不要从他站直接粘贴。如果仍然失败，请在论坛反馈。`,
    mediainfo_complete_name_required: `必须要包含 Complete name 或 Disc Title 或 Disc Label。`,
    mediainfo_table_space: `BDInfo表格中的空格导致无法解析，请使用 Tab 空格（\\t）替代。`,
    desc_img_3_png: `请添加至少三张 PNG 格式的电影原始分辨率（非播放分辨率）截图，操作方法请见 <a href="wiki.php?action=article&id=51" target="_blank">本文</a>。`,
    desc_img_hosts: `请使用发布规则 <a href="rules.php?p=upload#r2.2.1" target="_blank">2.2.1</a> 所推荐的图床以保证截图的访问速度和有效时长。`,
  },
  invalid_imdb_link_note:
    '请填入格式合规的 IMDb 链接，形如 “tt1234567” 或 “https://www.imdb.com/title/tt1234567”。',
  torrent_group_exists_note:
    "站点已有此电影，<a href='/torrents.php?id={0}'>点此</a> 查看并确保你想要发布的种子不与既有种子重复后，通过页面上方的 “添加格式” 发布。",
  request_torrent_group_exists_note:
    "站点已有此电影，<a href='/torrents.php?id={0}'>点此</a> 查看并确保你想要的格式不存在，通过页面上方的 “请求格式” 发布求种。",
  imdb_unknown_error:
    '请检查 IMDb ID 是否填写有误，如果无误，请重试，重试无效，请联系管理员。',

  bbcode: {
    edit: '编辑',
    preview: '预览',
    loading: '加载中……',
  },

  // Subtitles Page
  warning_subtitle_file: '请选中一个字幕文件！',
  warning_subtitle_format: '请使用站点规则允许的字幕格式！',
  warning_subtitle_language: '请指定字幕的语言！',

  torrent_table: {
    collapse_edition:
      '折叠本清晰度组。在左键单击的同时按住 [Command] <em>(Mac)</em> 或 [Ctrl] <em>(PC)</em> 键以折叠本影片中所有的清晰度组。',
    expand_edition:
      '展开本清晰度组。在左键单击的同时按住 [Command] <em>(Mac)</em> 或 [Ctrl] <em>(PC)</em> 键以展开本影片中所有的清晰度组。',
  },

  staffpm: {
    error: '系统出错了',
    response_created: '成功创建回复',
    response_edited: '成功编辑回复',
    response_deleted: '成功删除回复',
    assign_assigned: '成功指定对话',
  },

  stats: {
    torrentByDay: '每日发种数',
    torrentByMonth: '每月发种数',
    torrentByYear: '每年发种数',
  },
}
