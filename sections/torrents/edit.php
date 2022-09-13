<?
//**********************************************************************//
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Edit form ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
// This page relies on the TORRENT_FORM class. All it does is call      //
// the necessary functions.                                             //
//----------------------------------------------------------------------//
// At the bottom, there are grouping functions which are off limits to  //
// most members.                                                        //
//**********************************************************************//
require(CONFIG['SERVER_ROOT'] . '/classes/torrent_form.class.php');
if (!is_number($_GET['id']) || !$_GET['id']) {
    error(0);
}

$TorrentID = $_GET['id'];

$DB->query("
	SELECT
		t.RemasterYear,
		t.RemasterTitle,
        t.RemasterCustomTitle,
		t.Scene,
		t.Jinzhuan,
		t.Diy,
		t.Buy,
		t.Allow,
		t.FreeTorrent,
		t.FreeLeechType,
		t.NotMainMovie,
		t.Source,
		t.Codec,
		t.Container,
		t.Resolution,
		t.Subtitles,
		t.Makers,
        t.Processing,
        t.SpecialSub,
        t.ChineseDubbed,
        t.MediaInfo,
        t.Note,
        t.SubtitleType,
		t.Description AS TorrentDescription,
		tg.CategoryID,
		tg.Name,
        tg.SubName,
		tg.Year,
		tg.IMDBID,
		tg.ArtistID,
		ag.Name AS ArtistName,
		t.GroupID,
		t.UserID,
        t.Size,
		bf.TorrentID AS BadFolders,
		bfi.TorrentID AS BadFiles,
		bns.TorrentID AS NoSub,
		bhs.TorrentID AS HardSub,
		tct.CustomTrumpable as CustomTrumpable,
		fttd.EndTime as FreeEndTime
	FROM torrents AS t
		LEFT JOIN torrents_group AS tg ON tg.ID = t.GroupID
		LEFT JOIN artists_group AS ag ON ag.ArtistID = tg.ArtistID
		LEFT JOIN torrents_no_sub AS bns ON bns.TorrentID = t.ID
		LEFT JOIN torrents_hard_sub AS bhs ON bhs.TorrentID = t.ID
		LEFT JOIN torrents_bad_folders AS bf ON bf.TorrentID = t.ID
		LEFT JOIN torrents_bad_files AS bfi ON bfi.TorrentID = t.ID
		LEFT JOIN torrents_custom_trumpable AS tct ON tct.TorrentID = t.ID
		LEFT JOIN freetorrents_timed as fttd on fttd.TorrentID = t.id
	WHERE t.ID = '$TorrentID'");

list($Properties) = $DB->to_array(false, MYSQLI_BOTH, false);
if (!$Properties) {
    error(404);
}

$UploadForm = $Categories[$Properties['CategoryID'] - 1];

if (($LoggedUser['ID'] != $Properties['UserID'] && !check_perms('torrents_edit')) || $LoggedUser['DisableWiki']) {
    error(403);
}

View::show_header(t('server.torrents.browser_edit_torrent'), 'torrent', 'PageTorrentEdit');

$Group = ['Name' => $Properties['Name'], 'SubName' => $Properties['SubName'], 'Year' => $Properties['Year'], 'ID' => $Properties['GroupID']];

if (check_perms('torrents_edit') && (check_perms('users_mod') || $Properties['CategoryID'] == 1)) {
    if ($Properties['CategoryID'] == 1) {
?>
        <div class=LayoutBody>
            <div class="BodyHeader">
                <div class="BodyHeader-nav">
                    <?= t('server.torrents.browser_edit_torrent')  ?>

                </div>
                <div class="BodyHeader-subNav">
                    <?= Torrents::torrent_simple_view($Group, $Properties, true, [
                        'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
                    ]) ?>
                </div>
            </div>
            <div class="BodyNavLinks">
                <a class="brackets" href="#edit_torrent"><?= t('server.torrents.browser_edit_torrent') ?></a>
                <a class="brackets" href="#change_group_id"><?= t('server.torrents.change_group') ?></a>
            </div>
        <?
    }
}

if (!$Properties['RemasterYear'] || check_perms('edit_unknowns')) {
    if (!isset($Err)) {
        $Err = false;
    }
    $TorrentForm = new TORRENT_FORM($Properties, $Err, false);

    $TorrentForm->head();
    switch ($UploadForm) {
        case 'Movies':
            $TorrentForm->movie_form();
            break;
        default:
            $TorrentForm->movie_form();
    }
    $TorrentForm->foot();
}
if (check_perms('torrents_edit') && (check_perms('users_mod') || $Properties['CategoryID'] == 1)) {
        ?>
        <div class="Form">
            <form id="change_group_id" class="edit_form FormValidation" name="torrent_group" action="torrents.php" method="post">
                <input type="hidden" name="action" value="editgroupid" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="torrentid" value="<?= $TorrentID ?>" />
                <input type="hidden" name="oldgroupid" value="<?= $Properties['GroupID'] ?>" />
                <table class="Form-rowList" variant="header">
                    <tr class="Form-rowHeader">
                        <td><?= t('server.torrents.change_group') ?></td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.torrents.group_id') ?>:</td>
                        <td class="Form-items">
                            <input class="Input is-small" type="text" name="groupid" value="<?= $Properties['GroupID'] ?>" size="10" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td colspan="2" class="center">
                            <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        </div>
    <?
}

View::show_footer([], 'upload/index.js'); ?>