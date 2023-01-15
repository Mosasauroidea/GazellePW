<?

use Gazelle\Torrent\Subtitle;

//ini_set('max_file_uploads', 1);
View::show_header(t('server.subtitles.h2_subtitles'), 'validate_subtitles', 'PageSubtitleHome');
$TorrentID = isset($_GET['torrent_id']) ? $_GET['torrent_id'] : null;
if (!is_number($TorrentID)) {
    error(-1);
}
if ($TorrentID) {
    $AllSubtitles = Subtitles::get($TorrentID);
} else {
    error(404);
}
$TorrentDetail = Torrents::get_torrent($TorrentID);
$Title = Torrents::torrent_simple_view($TorrentDetail['Group'], $TorrentDetail, true, [
    'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
]);

function genSubcheckboxes($Labels, $Subtitles) {
    foreach ($Labels as $Key => $Label) {
        $Checked = strpos($Subtitles, $Label) === false ? "" : "checked='checked'";
        $Icon = Subtitle::icon($Label);
?>
        <div class="Subtitle-item">
            <input id="<?= $Label ?>" type="checkbox" name="languages[]" value="<?= $Label ?>" <?= $Checked ?>>
            <label class="Checkbox-label Subtitle-itemLabel" for="<?= $Label ?>"><?= $Icon ?> <?= Subtitle::text($Label) ?></label>
        </div>
<?
    }
}
?>

<div class="LayoutBody">
    <div class="BodyHeader">
        <div id="subtitle_for_torrent_title" class="BodyHeader-nav"><?= t('server.common.subtitles') ?></div>
        <div class="BodyHeader-subNav"><?= $Title ?></div>
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

    ?>

    <div id="subtitle_upload_form_container" class="TableContainer">
        <form id="subtitle_upload_form" action="subtitles.php" method='post' enctype="multipart/form-data" accept-charset='UTF-8'>
            <input type="hidden" name="torrent_pl_link" id="subtitle_torrent_pl" value="<?= site_url() . '/torrents.php?torrentid=' . $TorrentID ?>" />
            <table variant="header" class="Form-rowList" id="subtitle_upload_table">
                <tr class="Form-rowHeader">
                    <td class="Form-title">
                        <?= t('server.subtitles.upload_a_subtitle') ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.subtitles.subtitle_file') ?>:</td>
                    <td class="Form-inputs"><input id="file" name="file_input" type="file" accept=".sub,.idx,.sup,.srt,.vtt,.ass,.zip,.rar,.7z,.smi,.ssa"></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.common.language') ?>:</td>
                    <td class="Form-inputs">
                        <div id="subtitles_container" class="Form-errorContainer">
                            <div id="common_subtitles" class="grid_subtitles">
                                <?

                                $Labels = ['chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean'];
                                genSubcheckboxes(Subtitle::allItem(Subtitle::MainItem), "");
                                ?>
                                <a href="javascript:$('#other_subtitles').new_toggle()"><?= t('server.upload.show_more') ?></a>
                            </div>
                            <div id="other_subtitles" style="display: none;">
                                <div class="grid_subtitles">
                                    <?
                                    $Labels = ['no_subtitles', 'arabic', 'brazilian_port', 'bulgarian', 'croatian', 'czech', 'danish', 'dutch', 'estonian', 'finnish', 'french', 'german', 'greek', 'hebrew', 'hindi', 'hungarian', 'icelandic', 'indonesian', 'italian', 'latvian', 'lithuanian', 'norwegian', 'persian', 'polish', 'portuguese', 'romanian', 'russian', 'serbian', 'slovak', 'slovenian', 'spanish', 'swedish', 'thai', 'turkish', 'ukrainian', 'vietnamese'];
                                    genSubcheckboxes(Subtitle::allItem(Subtitle::ExtraItem), "");
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
</div>



<?
View::show_footer();
