<?
$TorrentID = isset($_GET['torrentid']) ? $_GET['torrentid'] : null;
// TODO by qwerty 获取失败
if (!$TorrentID) {
    die();
}
$AllSubtitles = Subtitles::get($TorrentID);
if (empty($AllSubtitles)) {
    die();
}

?>
<table class="TableSubtitle Table" id="subtitle_browse_table">
    <tr class="Table-rowHeader">
        <td class="Table-cellLeft TableSubtitle-cellLanguage Table-cell"><?= t('server.global.language') ?></td>
        <td class="TableSubtitle-cellName Table-cell"><?= t('server.subtitles.subtitle_names') ?></td>
        <td class="TableSubtitle-celllFormat Table-cell Table-cellRight"><?= t('server.global.format') ?></td>
        <td class="TableSubtitle-cellSize Table-cell Table-cellRight"><?= t('server.global.size') ?></td>
    </tr>
    <?

    $Labels = ['chinese_simplified', 'chinese_traditional', 'english', 'japanese', 'korean', 'no_subtitles', 'arabic', 'brazilian_port', 'bulgarian', 'croatian', 'czech', 'danish', 'dutch', 'estonian', 'finnish', 'french', 'german', 'greek', 'hebrew', 'hindi', 'hungarian', 'icelandic', 'indonesian', 'italian', 'latvian', 'lithuanian', 'norwegian', 'persian', 'polish', 'portuguese', 'romanian', 'russian', 'serbian', 'slovak', 'slovenian', 'spanish', 'swedish', 'thai', 'turkish', 'ukrainian', 'vietnamese'];
    foreach ($AllSubtitles as $Subtitle) {
        $LanguageArray = explode(',', $Subtitle['languages']);
        $IsNew = time_ago($Subtitle['upload_time']) < 60;
        $CanRM = check_perms('users_mod');
        $UserInfo = Users::user_info($Subtitle['uploader']);
        $UploaderName = $UserInfo['Username'];
        if ($UploaderName == '') {
            $UploaderName = "Unknown [" . $Subltitle['uploader'] . "]";
        }
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
            <td class="TableSubtitle-cellName Table-cell" data-tooltip="<?= t('server.torrents.upload_by_before') . $UploaderName . t('server.torrents.upload_by_after') . time_diff($Subtitle['upload_time'], 2, false)  . ' | ' . t('server.subtitles.times_of_download') . $Subtitle['download_times'] ?>">
                <span class="floatright">
                    <span>[ </span>
                    <a href="subtitles.php?action=download&id=<?= $Subtitle['id'] ?>" data-tooltip="<?= t('server.global.download') ?>">DL</a>
                    <?= $CanRM ? '| <a href="subtitles.php?action=delete&id=' . $Subtitle['id'] . '"  data-tooltip="' . t('server.global.remove') . '">RM</a> ' : '' ?>]
                </span>
                <?= $Subtitle['name'] . ' ' ?>
                <strong class="u-colorWarning"><?= ($IsNew ?  t('server.subtitles.new')  : '') ?></strong>
            </td>
            <td class="TableSubtitle-cellFormat Table-cell Table-cellRight"><?= $Subtitle['format'] ?></td>
            <td class="TableSubtitle-cellSize Table-cell Table-cellRight"><?= Format::get_size($Subtitle['size']) ?></td>
        </tr>
    <?
    }
    ?>
</table>