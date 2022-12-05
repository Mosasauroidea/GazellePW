<?
//**********************************************************************//
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~ Upload form ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
// This page relies on the TORRENT_FORM class. All it does is call      //
// the necessary functions.                                             //
//----------------------------------------------------------------------//
// $Properties, $Err and $UploadForm are set in takeupload.php, and     //
// are only used when the form doesn't validate and this page must be   //
// called again.                                                        //
//**********************************************************************//

require(CONFIG['SERVER_ROOT'] . '/classes/torrent_form.class.php');

ini_set('max_file_uploads', '100');

View::show_header(t('server.upload.upload'), '', 'PageUploadHome');

if (empty($Properties) && !empty($_GET['groupid']) && is_number($_GET['groupid'])) {
    $DB->query('
		SELECT
			tg.ID as GroupID,
            tg.ID,
			tg.CategoryID,
			tg.Name,
			tg.SubName,
			tg.Year,
			tg.WikiImage AS Image,
			tg.WikiBody AS GroupDescription,
			tg.MainWikiBody AS GroupMainDescription,
			tg.IMDBID,
			tg.ReleaseType
		FROM torrents_group AS tg
			LEFT JOIN torrents AS t ON t.GroupID = tg.ID
		WHERE tg.ID = ' . $_GET['groupid'] . '
		GROUP BY tg.ID');
    if ($DB->has_results()) {
        list($Properties) = $DB->to_array(false, MYSQLI_BOTH);
        $UploadForm = $Categories[$Properties['CategoryID'] - 1];
        $Properties['CategoryName'] = $Categories[$Properties['CategoryID'] - 1];
        $Properties['Artists'] = Artists::get_artist($_GET['groupid']);

        $DB->query("
			SELECT
				GROUP_CONCAT(tags.Name SEPARATOR ', ') AS TagList
			FROM torrents_tags AS tt
				JOIN tags ON tags.ID = tt.TagID
			WHERE tt.GroupID = '$_GET[groupid]'");

        list($Properties['TagList']) = $DB->next_record();
    } else {
        unset($_GET['groupid']);
    }
    if (!empty($_GET['requestid']) && is_number($_GET['requestid'])) {
        $Properties['RequestID'] = $_GET['requestid'];
    }
} elseif (empty($Properties) && !empty($_GET['requestid']) && is_number($_GET['requestid'])) {
    $DB->query('
		SELECT
			ID AS RequestID,
            IMDBID
		FROM requests
		WHERE ID = ' . $_GET['requestid']);

    list($Properties) = $DB->to_array(false, MYSQLI_BOTH);
    $UploadForm = $Categories[$Properties['CategoryID'] - 1];
}

if (!empty($ArtistForm)) {
    $Properties['Artists'] = $ArtistForm;
}

if (empty($Properties)) {
    $Properties = null;
}
if (empty($Err)) {
    $Err = null;
}
$TorrentForm = new TORRENT_FORM($Properties, $Err);
if (!empty($_GET['groupid'])) {
    $Name = Torrents::group_name($Properties, true);
}


$DB->query('
	SELECT
		Name,
		Comment,
		Time
	FROM do_not_upload
	ORDER BY Sequence');
$DNU = $DB->to_array();
$DB->query('SELECT MAX(Time) FROM do_not_upload');
list($Updated) = $DB->next_record();
$DB->query("
	SELECT IF(MAX(Time) IS NULL OR MAX(Time) < '$Updated', 1, 0)
	FROM torrents
	WHERE UserID = " . $LoggedUser['ID']);
list($NewDNU) = $DB->next_record();
$HideDNU = check_perms('torrents_hide_dnu') && !$NewDNU;
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.upload.upload') ?>
        </div>
        <? if ($Name) { ?>
            <div class="BodyHeader-subNav">
                <?= $Name ?>
            </div>
        <? } ?>
        <? if (!$Name) { ?>
            <div id="dnu_container" class="Group">
                <div class="Group-header">
                    <div class="Group-headerTitle"><?= t('server.upload.torrent_diff') ?></div>
                    <? if ($HideDNU) { ?>
                        <div class="Group-headerActions" id="showdnu">
                            <a href="#" onclick="globalapp.toggleAny(event, '#dnulist')">
                                <span class="u-toggleAny-show <?= $HideDNU ? '' : 'u-hidden' ?>"><?= t('server.common.show') ?></span>
                                <span class="u-toggleAny-hide <?= $HideDNU ? 'u-hidden' : '' ?>"><?= t('server.common.hide') ?></span>
                            </a>
                        </div>
                    <?  } ?>
                </div>
                <div id="dnulist" class="TableContainer Group-body <?= ($HideDNU ? 'u-hidden' : '') ?>">
                    <div><?= $NewDNU ? '<strong class="u-colorWarning">' : '' ?><?= t('server.upload.last_update') ?>: <?= time_diff($Updated) ?><?= $NewDNU ? '</strong>' : '' ?></div>
                    <div><?= t('server.upload.upload_note') ?>
                    </div>
                    <table class="TableUploadRule Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" width="50%"><strong><?= t('server.upload.name') ?></strong></td>
                            <td class="Table-cell"><strong><?= t('server.upload.explain') ?></strong></td>
                        </tr>
                        <? $TimeDiff = strtotime('-1 month', strtotime('now'));
                        foreach ($DNU as $BadUpload) {
                            list($Name, $Comment, $Updated) = $BadUpload;
                        ?>
                            <tr class="Table-row">
                                <td class="Table-cell">
                                    <div class="HtmlText">
                                        <?= Text::full_format($Name) . "\n" ?>
                                        <? if ($TimeDiff < strtotime($Updated)) { ?>
                                            <strong class="u-colorWarning">(New!)</strong>
                                        <? } ?>
                                    </div>
                                </td>
                                <td class="Table-cell">
                                    <div class="HtmlText">
                                        <?= Text::full_format($Comment) ?>
                                    </div>
                                </td>
                            </tr>
                        <? } ?>
                    </table>
                </div>
            </div>
        <? } ?>
    </div>

    <div class="BodyContent">
        <?
        $TorrentForm->head();
        switch ($UploadForm) {
            case 'Movies':
                $TorrentForm->movie_form($GenreTags);
                break;
            default:
                $TorrentForm->movie_form($GenreTags);
        }
        $TorrentForm->foot();
        ?>
    </div>
</div>
<?
View::show_footer([], 'upload/index.js');
