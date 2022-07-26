<?
//ini_set('max_file_uploads', 1);
View::show_header(t('server.subtitles.h2_subtitles'), 'validate_subtitles', 'PageSubtitleHome');
$TorrentID = isset($_GET['torrent_id']) ? $_GET['torrent_id'] : null;
if ($TorrentID) {
    $AllSubtitles = Subtitles::get($TorrentID);
}
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.subtitles.h2_subtitles') ?></div>
    </div>

    <div class="BodyContent">
        <div class="Box">
            <div class="Box-header"> <?= t('server.common.rules') ?> </div>
            <div class="Box-body"> <?= t('server.subtitles.subtitle_rules') ?> </div>
        </div>
    </div>
    <?
    if (isset($Err)) {
        echo "\t" . '<strong style="text-align: center;" class="u-colorWarning">' . $Err . "</strong>\n";
    }
    if ($TorrentID) {
        $TorrentDetail = Torrents::get_torrent($TorrentID);
        $Title = Torrents::torrent_simple_view($TorrentDetail['Group'], $TorrentDetail, [
            'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
        ]);
    ?>

        <div id="subtitle_upload_form_container" class="TableContainer">
            <form id="subtitle_upload_form" action="subtitles.php" method='post' enctype="multipart/form-data" accept-charset='UTF-8'>
                <table variant="header" class="Form-rowList" id="subtitle_upload_table">
                    <tr class="Form-rowHeader">
                        <td id="subtitle_for_torrent_title" class="Form-title"><?= t('server.subtitles.upload_a_subtitle') . ' > ' . $Title ?></td>
                    </tr>

                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.subtitles.subtitle_file') ?>:</td>
                        <td class="Form-inputs"><input id="file" name="file_input" type="file" accept=".sub,.idx,.sup,.srt,.vtt,.ass,.zip,.rar,.7z,.smi,.ssa"></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.subtitles.torrent_pl') ?>:</td>
                        <td class="Form-inputs"><input class="Input" type="text" readonly value="<?= site_url() . '/torrents.php?torrentid=' . $TorrentID ?>" name="torrent_pl_link" id="subtitle_torrent_pl" placeholder="<?= CONFIG['SITE_URL'] ?>/torrents.php?torrentid=12345"></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.common.language') ?>:</td>
                        <td class="Form-inputs">
                            <div id="subtitles_container" class="Form-errorContainer">
                                <div id="common_subtitles" class="grid_subtitles">
                                    <?
                                    function genSubcheckboxes($Labels, $Subtitles) {
                                        for ($i = 0; $i < count($Labels); $i++) {
                                            echo '<div class="subtitle">
                                            <input id="' . $Labels[$i] . '" type="checkbox" name="languages[]" value="' . $Labels[$i] . '"' . (strpos($Subtitles, $Labels[$i]) === false ? "" : "checked=\"checked\"") . '>
                                            <label for="' . $Labels[$i] . '">' .
                                                icon("flag/$Labels[$i]") .  t("server.upload.${Labels[$i]}") . '
                                            </label></div>';
                                        }
                                    }
                                    $Labels = ['chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean'];
                                    genSubcheckboxes($Labels, "");
                                    ?>
                                    <a href="javascript:$('#other_subtitles').new_toggle()"><?= t('server.upload.show_more') ?></a>
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
                <td class="right"><?= t('server.subtitles.source') ?>:</td>
                <td ><input class="Input" type="text" name="source" placeholder="<?= t('server.subtitles.a_subtitle_group') ?>"></td>
            </tr> -->
                    <!-- <tr>
                <td class="right"><?= t('server.subtitles.anonymous_upload') ?>:</td>
                <td ><label><input type="checkbox">
                <?= t('server.subtitles.do_not_show_my_username') ?></label></td>
            </tr> -->
                    <tr class="Form-row">
                        <td colspan="2" class="center"><?= t('server.subtitles.subtitle_upload_warning') ?></td>
                    </tr>
                    <tr class="Form-row">
                        <td colspan="2" class="center"><input class="Button" id="post" name="submit" type="submit" value="<?= t('server.subtitles.button_upload') ?>"></td>
                    </tr>
                </table>
            </form>
        </div>
    <?
    }
    ?>
</div>



<?
View::show_footer();
