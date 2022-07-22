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

View::show_header(Lang::get('upload.upload'), '', 'PageUploadHome');

if (empty($Properties) && !empty($_GET['groupid']) && is_number($_GET['groupid'])) {
    $DB->query('
		SELECT
			tg.ID as GroupID,
			tg.CategoryID,
			tg.Name,
			tg.SubName,
			tg.Year,
			tg.WikiImage AS Image,
			tg.WikiBody AS GroupDescription,
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

$GenreTags = $Cache->get_value('genre_tags');
if (!$GenreTags) {
    $DB->query("
		SELECT Name
		FROM tags
		WHERE TagType = 'genre'
		ORDER BY Name");
    $GenreTags = $DB->collect('Name');
    $Cache->cache_value('genre_tags', $GenreTags, 3600 * 6);
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
            <?= Lang::get('upload.upload') ?>
        </div>
        <div id="dnu_container" class="<?= (check_perms('torrents_hide_dnu') ? 'BoxBody' : '') ?>">
            <h3 id="dnu_header"><?= Lang::get('upload.torrent_diff') ?></h3>
            <p><?= $NewDNU ? '<strong class="u-colorWarning">' : '' ?><?= Lang::get('upload.last_update') ?>: <?= time_diff($Updated) ?><?= $NewDNU ? '</strong>' : '' ?></p>
            <p><?= Lang::get('upload.upload_note') ?>
                <? if ($HideDNU) { ?>
                    <span id="showdnu"><a href="#" onclick="$('#dnulist').gtoggle(); this.innerHTML = (this.innerHTML == 'Hide' ? 'Show' : 'Hide'); return false;" class="brackets"><?= Lang::get('global.show') ?></a></span>
                <?  } ?>
            </p>
            <div id="dnulist" class="TableContainer" class="<?= ($HideDNU ? 'hidden' : '') ?>">
                <table class="TableUploadRule Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell" width="50%"><strong><?= Lang::get('upload.name') ?></strong></td>
                        <td class="Table-cell"><strong><?= Lang::get('upload.explain') ?></strong></td>
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
        <?= ($HideDNU ? '<br />' : '') ?>
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
