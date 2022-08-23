<?
//ini_set('max_file_uploads', 1);
View::show_header(Lang::get('subtitles', 'h2_subtitles'), 'validate_subtitles', 'PageSubtitleHome');
$TorrentID = isset($_GET['torrent_id']) ? $_GET['torrent_id'] : null;
if ($TorrentID) {
    $Data = $Cache->get_value("torrent_sub_title_$TorrentID");
    if (!$Data) {
        $DB->query("
        select 
            subtitles.id, 
            subtitles.languages, 
            subtitles.torrent_id, 
            subtitles.source, 
            subtitles.download_times, 
            subtitles.format, 
            subtitles.size, 
            subtitles.uploader, 
            subtitles.upload_time, 
            subtitles.name
        from subtitles 
            where torrent_id=$TorrentID");
        $AllSubtitles = $DB->to_array();
        $Cache->cache_value("torrent_sub_title_$TorrentID", [$AllSubtitles]);
    } else {
        list($AllSubtitles) = $Data;
    }
    $TorrentInfo = Torrents::get_torrent($TorrentID);
} else {

}
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= Lang::get('subtitles', 'h2_subtitles') ?></div>
        <div class="BodyNavLinks">
            <!-- <a href="subtitles.php?action=new" class="brackets"><?= Lang::get('subtitles', 'new_subtitle') ?></a> -->
            <!-- <a href="forums.php" class="brackets"><?= Lang::get('subtitles', 'request_a_subtitle') ?></a>
        <a href="subtitles.php?action=new" class="brackets"><?= Lang::get('subtitles', 'my_subtitles') ?></a>
        <a href="subtitles.php?action=new" class="brackets"><?= Lang::get('subtitles', 'bookmarked_subtitles') ?></a> -->
        </div>
    </div>

    <div class="BodyContent">
        <?
        if (empty($TorrentID)) {
        ?>
            <div class="center"><strong class="u-colorWarning"><?= Lang::get('subtitles', 'top_warning') ?></strong></div>
        <?
        }
        ?>
        <div class="Post">
            <div class="Post-header"> <?= Lang::get('global', 'rules') ?> </div>
            <div class="Post-body"> <?= Lang::get('subtitles', 'subtitle_rules') ?> </div>
        </div>
    </div>

    <!-- <div id="subtitle_search_box">
        <input class="Input" type="text" id="subtitle_search_title" placeholder="<?= Lang::get('subtitles', 'title_or_imdb_link') ?>">
        <input class="Input" type="text" id="subtitle_search_year"  placeholder="<?= Lang::get('subtitles', 'year_optional') ?>">
        <select class="Input" id="subtitle_search_language" name="TargetLanguageId" class="form__input">
            <option class="Select-option" value="14">简中</option><option class="Select-option" value="14">繁中</option><option class="Select-option" value="3">English</option><option class="Select-option" value="14">日语 japanese</option><option class="Select-option" value="19">韩语 korean</option><option class="Select-option" value="" selected="selected">---</option><option class="Select-option" value="22">Arabic</option><option class="Select-option" value="49">Brazilian Portuguese</option><option class="Select-option" value="29">Bulgarian</option><option class="Select-option" value="14">Chinese</option><option class="Select-option" value="23">Croatian</option><option class="Select-option" value="30">Czech</option><option class="Select-option" value="10">Danish</option><option class="Select-option" value="9">Dutch</option><option class="Select-option" value="38">Estonian</option><option class="Select-option" value="15">Finnish</option><option class="Select-option" value="6">German</option><option class="Select-option" value="26">Greek</option><option class="Select-option" value="40">Hebrew</option><option class="Select-option" value="41">Hindi</option><option class="Select-option" value="24">Hungarian</option><option class="Select-option" value="28">Icelandic</option><option class="Select-option" value="47">Indonesian</option><option class="Select-option" value="16">Italian</option></option><option class="Select-option" value="37">Latvian</option><option class="Select-option" value="39">Lithuanian</option><option class="Select-option" value="12">Norwegian</option><option class="Select-option" value="52">Persian</option><option class="Select-option" value="17">Polish</option><option class="Select-option" value="21">Portuguese</option><option class="Select-option" value="13">Romanian</option><option class="Select-option" value="7">Russian</option><option class="Select-option" value="31">Serbian</option><option class="Select-option" value="42">Slovak</option><option class="Select-option" value="43">Slovenian</option><option class="Select-option" value="11">Swedish</option><option class="Select-option" value="20">Thai</option><option class="Select-option" value="18">Turkish</option><option class="Select-option" value="34">Ukrainian</option><option class="Select-option" value="25">Vietnamese</option>
        </select>
        <select class="Input" id="subtitle_search_format" name="" class="form__input">
            <option class="Select-option"><?= Lang::get('global', 'format') ?></option>
            <option class="Select-option">SRT</option>
            <option class="Select-option">ASS</option>
            <option class="Select-option">SUP</option>
            <option class="Select-option">VTT</option>
            <option class="Select-option"><?= Lang::get('global', 'others') ?></option>
        </select>
        <button class="Button"><?= Lang::get('subtitles', 'search') ?></button>
    </div> -->
    <!-- 原来的那个表我挪到字幕收藏里边去了，基于单个字幕的展示更适合那里，这里我认为还是应该采用分组管理 -->

    <!-- <table id="subtitle_browse_table">
        <tr class="colhead">
            <td>这一列是伸缩展开按钮的位置</td>
            <td style="70px"><?= Lang::get('global', 'language') ?></td>
            <td><?= Lang::get('subtitles', 'movie_title') ?></td>
            <td><?= Lang::get('global', 'format') ?></td>
            <td><?= Lang::get('global', 'size') ?></td>
            <td><?= Lang::get('subtitles', 'times_of_download') ?></td>
            <td><?= Lang::get('global', 'time') ?></td>
        </tr>
        <tr class="group">
            <td>这一列是伸缩展开按钮的位置</td>
            <td colspan="6">x264 / WEB / 1080p / MKV</td>
        </tr>
        <tr class="torrent">
            <td>这一列是伸缩展开按钮的位置</td>
            <td colspan="6">[流浪地球] The Wandering Earth (2019) by 郭帆</td>
        </tr>
        <tr class="subtitle">
            <td><img class="national_flags" src="static/common/flags/China.png"></td>
            <td><a href="subtitles.php?action=detail">Liu.lang.di.qiu.2019.REPACK.1080p.BluRay.DDP7.1.x264-Geek.srt</a>
            <span class="floatright">[ <a>DL</a> | <a>RP</a> ]</span>
        </td>
            <td>SRT</td>
            <td>3.28 KB</td>
            <td>166</td>
            <td>1 分前</td>
        </tr>
    </table> -->
    <?
    if (isset($Err)) {
        echo "\t" . '<p style="text-align: center;" class="u-colorWarning">' . $Err . "</p>\n";
    }
    if ($TorrentID) {
        $TorrentDetail = Torrents::get_torrent($TorrentID);
        $Title = Torrents::torrent_simple_view($TorrentDetail['Group'], $TorrentDetail);
    ?>

        <div id="subtitle_upload_form_container" class="TableContainer">
            <form id="subtitle_upload_form" action="subtitles.php" method='post' enctype="multipart/form-data" accept-charset='UTF-8'>
                <table variant="header" class="Form-rowList" id="subtitle_upload_table">
                    <tr class="Form-rowHeader">
                        <td id="subtitle_for_torrent_title" class="Form-title"><?= Lang::get('subtitles', 'upload_a_subtitle') . ' > ' . $Title ?></td>
                    </tr>

                    <tr class="Form-row">
                        <td class="Form-label"><?= Lang::get('subtitles', 'subtitle_file') ?>:</td>
                        <td class="Form-inputs"><input id="file" name="file_input" type="file" accept=".sub,.idx,.sup,.srt,.vtt,.ass,.zip,.rar,.7z,.smi,.ssa"></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= Lang::get('subtitles', 'torrent_pl') ?>:</td>
                        <td class="Form-inputs"><input class="Input" type="text" readonly value="<?= site_url() . '/torrents.php?torrentid=' . $TorrentID ?>" name="torrent_pl_link" id="subtitle_torrent_pl" placeholder="<?= SITE_URL ?>/torrents.php?torrentid=12345"></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= Lang::get('global', 'language') ?>:</td>
                        <td class="Form-inputs">
                            <div id="subtitles_container" class="Form-errorContainer">
                                <div id="common_subtitles" class="grid_subtitles">
                                    <?
                                    function genSubcheckboxes($Labels, $Subtitles) {
                                        for ($i = 0; $i < count($Labels); $i++) {
                                            echo '<div class="subtitle">
                                            <input id="' . $Labels[$i] . '" type="checkbox" name="languages[]" value="' . $Labels[$i] . '"' . (strpos($Subtitles, $Labels[$i]) === false ? "" : "checked=\"checked\"") . '>
                                            <label for="' . $Labels[$i] . '">' .
                                                icon("flag/$Labels[$i]") .  Lang::get('upload', $Labels[$i]) . '
                                            </label></div>';
                                        }
                                    }
                                    $Labels = ['chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean'];
                                    genSubcheckboxes($Labels, "");
                                    ?>
                                    <a href="javascript:$('#other_subtitles').new_toggle()"><?= Lang::get('upload', 'show_more') ?></a>
                                </div>
                                <div id="other_subtitles" style="display: none;">
                                    <div class="grid_subtitles">
                                        <?
                                        $Labels = ['no_subtitles', 'arabic', 'brazilian_port', 'bulgarian', 'croatian', 'czech', 'danish', 'dutch', 'estonian', 'finnish', 'french', 'german', 'greek', 'hebrew', 'hindi', 'hungarian', 'icelandic', 'indonesian', 'italian', 'latvian', 'lithuanian', 'norwegian', 'persian', 'polish', 'portuguese', 'romanian', 'russian', 'serbian', 'slovak', 'slovenian', 'spanish', 'swedish', 'thai', 'turkish', 'ukrainian', 'vietnamese'];
                                        genSubcheckboxes($Labels, "");
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <!-- <tr>
                <td class="right"><?= Lang::get('subtitles', 'source') ?>:</td>
                <td class=""><input class="Input" type="text" name="source" placeholder="<?= Lang::get('subtitles', 'a_subtitle_group') ?>"></td>
            </tr> -->
                    <!-- <tr>
                <td class="right"><?= Lang::get('subtitles', 'anonymous_upload') ?>:</td>
                <td class=""><label><input type="checkbox">
                <?= Lang::get('subtitles', 'do_not_show_my_username') ?></label></td>
            </tr> -->
                    <tr class="Form-row">
                        <td colspan="2" class="center"><?= Lang::get('subtitles', 'subtitle_upload_warning') ?></td>
                    </tr>
                    <tr class="Form-row">
                        <td colspan="2" class="center"><input class="Button" id="post" name="submit" type="submit" value="<?= Lang::get('subtitles', 'button_upload') ?>"></td>
                    </tr>
                </table>
            </form>
        </div>
        <?
        if (!empty($AllSubtitles)) {

        ?>
            <table class="TableSubtitle Table" id="subtitle_browse_table">
                <tr class="Table-rowHeader">
                    <td class="TableSubtitle-cellLanguage Table-cell"><?= Lang::get('global', 'language') ?></td>
                    <td class="TableSubtitle-cellName Table-cell"><?= Lang::get('subtitles', 'subtitle_names') ?></td>
                    <td class="TableSubtitle-celllFormat Table-cell"><?= Lang::get('global', 'format') ?></td>
                    <td class="TableSubtitle-cellSize Table-cell"><?= Lang::get('global', 'size') ?></td>
                    <td class="TableSubtitle-cellDownloads Table-cell"><?= Lang::get('subtitles', 'times_of_download') ?></td>
                    <td class="TableSubtitle-cellUploader Table-cell"><?= Lang::get('subtitles', 'subtitle_uploader') ?></td>
                    <td class="TableSubtitle-cellCreatedAt TableSubtitle-cellTime Table-cell"><?= Lang::get('global', 'time') ?></td>
                </tr>
                <?

                if ($AllSubtitles) {
                    $Labels = ['chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean', 'no_subtitles', 'arabic', 'brazilian_port', 'bulgarian', 'croatian', 'czech', 'danish', 'dutch', 'estonian', 'finnish', 'french', 'german', 'greek', 'hebrew', 'hindi', 'hungarian', 'icelandic', 'indonesian', 'italian', 'latvian', 'lithuanian', 'norwegian', 'persian', 'polish', 'portuguese', 'romanian', 'russian', 'serbian', 'slovak', 'slovenian', 'spanish', 'swedish', 'thai', 'turkish', 'ukrainian', 'vietnamese'];
                    foreach ($AllSubtitles as $Subtitle) {
                        $LanguageArray = explode(',', $Subtitle['languages']);
                        $IsNew = time_ago($Subtitle['upload_time']) < 60;
                        $CanRM = check_perms('users_mod');
                ?>
                        <tr class="TableSubtitle-row Table-row">
                            <td class="TableSubtitle-cellLanguage Table-cell">
                                <?
                                foreach ($LanguageArray as $Language) {
                                ?>
                                    <?= icon("flag/$Language") ?>
                                <?
                                }
                                ?>
                            </td>
                            <td class="TableSubtitle-cellName Table-cell">
                                <span class="floatright">
                                    <span>[ </span>
                                    <a href="subtitles.php?action=download&id=<?= $Subtitle['id'] ?>" class="tooltip" title="<?= Lang::get('global', 'download') ?>">DL</a>
                                    <span> | </span>
                                    <a href="reportsv2.php?action=report&type=subtitle_track_bad&id=<?= $TorrentID ?>" class="tooltip" title="<?= Lang::get('global', 'report') ?>">RP</a>
                                    <?= $CanRM ? '| <a href="subtitles.php?action=delete&id=' . $Subtitle['id'] . '" class="tooltip" title="' . Lang::get('global', 'remove') . '">RM</a> ' : '' ?>]
                                </span>
                                <?= $Subtitle['name'] . ' ' ?>
                                <span class="u-colorWarning"><?= ($IsNew ? '(' . Lang::get('subtitles', 'new') . '!)' : '') ?></span>
                            </td>
                            <td class="TableSubtitle-cellFormat Table-cell"><?= $Subtitle['format'] ?></td>
                            <td class="TableSubtitle-cellSize Table-cell"><?= Format::get_size($Subtitle['size']) ?></td>
                            <td class="TableSubtitle-cellDownloads Table-cell"><?= $Subtitle['download_times'] ?></td>
                            <td class="TableSubtitle-cellUploader Table-cell"><?= Users::format_username($Subtitle['uploader'], false, false, false) ?></td>
                            <td class="TableSubtitle-cellCreatedAt TableSubtitle-cellTime Table-cell"><?= time_diff($Subtitle['upload_time']) ?></td>
                        </tr>
                <?
                    }
                }
                ?>

            </table>
    <?
        }
    }
    ?>
</div>



<?
View::show_footer();
