<?

use Gazelle\Torrent\Subtitle;

$TorrentID = isset($_GET['torrentid']) ? $_GET['torrentid'] : null;
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
        <td class="Table-cellLeft TableSubtitle-cellLanguage Table-cell"><?= t('server.common.language') ?></td>
        <td class="TableSubtitle-cellName Table-cell"><?= t('server.subtitles.subtitle_names') ?></td>
        <td class="TableSubtitle-celllFormat Table-cell Table-cellRight"><?= t('server.common.format') ?></td>
        <td class="TableSubtitle-cellSize Table-cell Table-cellRight"><?= t('server.common.size') ?></td>
    </tr>
    <?

    $Labels = Subtitle::allItem();
    foreach ($AllSubtitles as $Subtitle) {
        $LanguageArray = explode(',', $Subtitle['languages']);
        $IsNew = time_ago($Subtitle['upload_time']) < 600;
        $CanRM = check_perms('users_mod') || $Subtitle['uploader'] == $LoggedUser['ID'];
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
                    <?= Subtitle::icon($Language) ?>
                <?
                }
                ?>
            </td>
            <td class="TableSubtitle-cellName Table-cell" data-tooltip="<?= t('server.torrents.upload_by', ['Values' => [$UploaderName]])  . time_diff($Subtitle['upload_time'], 2, false)  . ' | ' . t('server.subtitles.times_of_download') . $Subtitle['download_times'] ?>">
                <span class="floatright">
                    <span>[ </span>
                    <a href="subtitles.php?action=download&id=<?= $Subtitle['id'] ?>" data-tooltip="<?= t('server.common.download') ?>">DL</a>
                    <?= $CanRM ? '| <a href="subtitles.php?action=delete&id=' . $Subtitle['id'] . '"  data-tooltip="' . t('server.common.remove') . '">RM</a> ' : '' ?>]
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