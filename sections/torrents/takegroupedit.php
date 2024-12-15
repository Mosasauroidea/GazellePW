<?

use Gazelle\Manager\ActionTrigger;

authorize();

// Quick SQL injection check
if (!$_REQUEST['groupid'] || !is_number($_REQUEST['groupid'])) {
    error(404);
}
// End injection check

if (!check_perms('site_edit_wiki')) {
    error(403);
}

// Variables for database input
$UserID = $LoggedUser['ID'];
$GroupID = $_REQUEST['groupid'];

if (!empty($_GET['action']) && $_GET['action'] == 'revert') { // if we're reverting to a previous revision
    $RevisionID = $_GET['revisionid'];
    if (!is_number($RevisionID)) {
        error(0);
    }

    // to cite from merge: "Everything is legit, let's just confim they're not retarded"
    if (empty($_GET['confirm'])) {
        View::show_header('', '', 'PageTorrentTakeGroupEdit');
        $TorrentCache = Torrents::get_group($GroupID, true);
        $TorrentDetails = $TorrentCache;
        $Name = Torrents::group_name($TorrentDetails);
?>
        <div class="LayoutBody">
            <div class="BodyHeader">
                <h2 class="BodyHeader-nav"><?= t('server.torrents.viewhistory') ?></h2>
                <div class="BodyHeader-subNav"><?= $Name ?></div>
            </div>
            <div class="Form-rowList" variant="header">
                <div class="Form-rowHeader">
                    <div class="Form-title">
                        <?= t('server.torrents.revert_confirm') ?>
                    </div>
                </div>
                <div class="Form-row"><?= t('server.torrents.revert_confirm_body') ?><a href='torrents.php?id=<?= $GroupID ?>&amp;revisionid=<?= $RevisionID ?>'><?= $RevisionID ?></a>
                </div>
                <form class="confirm_form" name="torrent_group" action="torrents.php" method="get">
                    <input type="hidden" name="action" value="revert" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <input type="hidden" name="confirm" value="true" />
                    <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                    <input type="hidden" name="revisionid" value="<?= $RevisionID ?>" />
                    <div class="Form-row">
                        <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                    </div>
                </form>
            </div>
        </div>
<?
        View::show_footer();
        die();
    }
} else { // with edit, the variables are passed with POST
    $Body = db_string(preg_replace("/\r|\n/", "", trim($_POST['body'])));
    $MainBody = db_string(preg_replace("/\r|\n/", "", trim($_POST['mainbody'])));
    $Image = db_string($_POST['image']);
    $IMDBID = db_string($_POST['imdbid']);
    $DoubanID = db_string($_POST['doubanid']);
    $RTTitle = db_string($_POST['rttitle']);
    $Year = db_string(intval($_POST['year']));
    $ReleaseType = db_string((int)$_POST['releasetype']);
    $Name = db_string($_POST['name']);
    $SubName = db_string($_POST['subname']);
    // Trickery
    if (!preg_match("/^" . IMAGE_REGEX . "$/i", $Image)) {
        $Image = '';
    }
    ImageTools::blacklisted($Image);
    $Summary = db_string($_POST['summary']);
}

// Insert revision
if (empty($RevisionID)) { // edit
    $DB->query(
        "INSERT INTO wiki_torrents
			(
                PageID, 
                Body, 
                MainBody,
                Image, 
                UserID, 
                Summary, 
                Time, 
                IMDBID, 
                DoubanID, 
                RTTitle, 
                Year, 
                Name, 
                SubName,
                ReleaseType)
		VALUES
			(
                '$GroupID', 
                '" . $Body . "', 
                '" . $MainBody . "', 
                '" . $Image . "', 
                '$UserID', 
                '$Summary', 
                '" . sqltime() . "', 
                '" . $IMDBID . "', 
                '" . $DoubanID . "', 
                '" . $RTTitle . "',
                '$Year',
                '" . $Name . "',
                '" . $SubName . "',
                '" . $ReleaseType . "'
                )"
    );
} else { // revert
    $DB->query("
		SELECT PageID, Body, MainBody, Image, IMDBID, DoubanID, RTTitle, Year, Name, SubName, ReleaseType
		FROM wiki_torrents
		WHERE RevisionID = '$RevisionID'");
    list($PossibleGroupID, $Body, $MainBody, $Image, $IMDBID, $DoubanID, $RTTitle, $Year, $Name, $SubName, $ReleaseType) = $DB->next_record();
    if ($PossibleGroupID != $GroupID) {
        error(404);
    }

    $DB->query("
		INSERT INTO wiki_torrents
			(PageID, Body, MainBody, Image, UserID, Summary, Time, IMDBID, DoubanID, RTTitle, Year, Name, SubName, ReleaseType)
		SELECT '$GroupID', Body, MainBody, Image, '$UserID', 'Reverted to revision $RevisionID', '" . sqltime() . "', IMDBID, DoubanID, RTTitle, Year, Name, SubName, ReleaseType
		FROM wiki_torrents
		WHERE RevisionID = '$RevisionID'");
}

$RevisionID = $DB->inserted_id();

// Update torrents table (technically, we don't need the RevisionID column, but we can use it for a join which is nice and fast)
// TODO by qwerty write group log
$DB->query(
    "UPDATE torrents_group
	SET
		RevisionID = '$RevisionID',
		WikiBody = '$Body',
        MainWikiBody = '$MainBody',
		WikiImage = '$Image',
        IMDBID = '$IMDBID',
        DoubanID = '" . (empty($DoubanID) ? 'null' : $DoubanID) . "',
        RTTitle = '$RTTitle',
		ReleaseType = '$ReleaseType',
        Year = $Year,
        Name = '$Name',
        SubName = '$SubName'
	WHERE ID='$GroupID'"
);
$trigger = new ActionTrigger;
$trigger->triggerMovieEdit($GroupID);
Torrents::update_movie_info($GroupID, $IMDBID, $DoubanID, true);
Torrents::update_hash($GroupID);

$DB->query("
	SELECT CollageID
	FROM collages_torrents
	WHERE GroupID = '$GroupID'");
if ($DB->has_results()) {
    while (list($CollageID) = $DB->next_record()) {
        $Cache->delete_value('collage_' . $CollageID);
    }
}

//Fix Recent Uploads/Downloads for image change
$DB->query("
	SELECT DISTINCT UserID
	FROM torrents AS t
		LEFT JOIN torrents_group AS tg ON t.GroupID=tg.ID
	WHERE tg.ID = $GroupID");

$UserIDs = $DB->collect('UserID');
foreach ($UserIDs as $UserID) {
    $RecentUploads = $Cache->get_value('recent_uploads_' . $UserID);
    if (is_array($RecentUploads)) {
        foreach ($RecentUploads as $Key => $Recent) {
            if ($Recent['ID'] == $GroupID) {
                if ($Recent['WikiImage'] != $Image) {
                    $Recent['WikiImage'] = $Image;
                    $Cache->begin_transaction('recent_uploads_' . $UserID);
                    $Cache->update_row($Key, $Recent);
                    $Cache->commit_transaction(0);
                }
            }
        }
    }
}

$DB->query("
	SELECT ID
	FROM torrents
	WHERE GroupID = $GroupID");
if ($DB->has_results()) {
    $TorrentIDs = implode(',', $DB->collect('ID'));
    $DB->query("
		SELECT DISTINCT uid
		FROM xbt_snatched
		WHERE fid IN ($TorrentIDs)");
    $Snatchers = $DB->collect('uid');
    foreach ($Snatchers as $UserID) {
        $RecentSnatches = $Cache->get_value('recent_snatches_' . $UserID);
        if (is_array($RecentSnatches)) {
            foreach ($RecentSnatches as $Key => $Recent) {
                if ($Recent['ID'] == $GroupID) {
                    if ($Recent['WikiImage'] != $Image) {
                        $Recent['WikiImage'] = $Image;
                        $Cache->begin_transaction('recent_snatches_' . $UserID);
                        $Cache->update_row($Key, $Recent);
                        $Cache->commit_transaction(0);
                    }
                }
            }
        }
    }
}

header("Location: torrents.php?id=$GroupID");
?>