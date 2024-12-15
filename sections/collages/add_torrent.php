<?

use Gazelle\Manager\ActionTrigger;

authorize();

include(CONFIG['SERVER_ROOT'] . '/classes/validate.class.php');
$Val = new VALIDATE;
$FromAjax = isset($_POST['groupid']);
function add_torrent($CollageID, $GroupID) {
    global $Cache, $LoggedUser, $DB;
    $trigger = new ActionTrigger;

    $DB->query("
		SELECT MAX(Sort)
		FROM collages_torrents
		WHERE CollageID = '$CollageID'");
    list($Sort) = $DB->next_record();
    $Sort += 10;

    $DB->query("
		SELECT GroupID
		FROM collages_torrents
		WHERE CollageID = '$CollageID'
			AND GroupID = '$GroupID'");
    if (!$DB->has_results()) {
        $DB->query("
			INSERT IGNORE INTO collages_torrents
				(CollageID, GroupID, UserID, Sort, AddedOn)
			VALUES
				('$CollageID', '$GroupID', '$LoggedUser[ID]', '$Sort', '" . sqltime() . "')");

        $DB->query("
			UPDATE collages
			SET NumTorrents = NumTorrents + 1, Updated = '" . sqltime() . "'
			WHERE ID = '$CollageID'");

        $Cache->delete_value("collage_$CollageID");
        $Cache->delete_value("torrents_details_$GroupID");
        $Cache->delete_value("torrent_collages_$GroupID");
        $Cache->delete_value("torrent_collages_personal_$GroupID");
        $trigger->triggerFillCollage($CollageID, $GroupID);


        $DB->query("
			SELECT UserID
			FROM users_collage_subs
			WHERE CollageID = $CollageID");
        while (list($CacheUserID) = $DB->next_record()) {
            $Cache->delete_value("collage_subs_user_new_$CacheUserID");
        }
    }
}

$CollageID = $_POST['collageid'];
if (!is_number($CollageID)) {
    if ($FromAjax) {
        echo json_encode(['state' => false]);
        die();
    } else {
        error(404);
    }
}
$DB->query("
	SELECT UserID, CategoryID, Locked, NumTorrents, MaxGroups, MaxGroupsPerUser
	FROM collages
	WHERE ID = '$CollageID'");
list($UserID, $CategoryID, $Locked, $NumTorrents, $MaxGroups, $MaxGroupsPerUser) = $DB->next_record();

if (!check_perms('site_collages_delete')) {
    if ($Locked) {
        $Err = 'This collage is locked';
    }
    if ($CategoryID == $PersonalCollageCategoryCat && $UserID != $LoggedUser['ID']) {
        $Err = 'You cannot edit someone else\'s personal collage.';
    }
    if ($MaxGroups > 0 && $NumTorrents >= $MaxGroups) {
        $Err = 'This collage already holds its maximum allowed number of torrents.';
    }

    if (isset($Err)) {
        if ($FromAjax) {
            echo json_encode(['state' => false]);
            die();
        } else {
            error($Err);
        }
    }
}

if ($MaxGroupsPerUser > 0) {
    $DB->query("
		SELECT COUNT(*)
		FROM collages_torrents
		WHERE CollageID = '$CollageID'
			AND UserID = '$LoggedUser[ID]'");
    list($GroupsForUser) = $DB->next_record();
    if (!check_perms('site_collages_delete') && $GroupsForUser >= $MaxGroupsPerUser) {
        if ($FromAjax) {
            echo json_encode(['state' => false]);
            die();
        } else {
            error(403);
        }
    }
}

if ($_REQUEST['action'] == 'add_torrent') {
    if ($FromAjax && is_number($_POST['groupid'])) {
        $DB->query("
			SELECT ID
			FROM torrents_group
			WHERE ID = '" . $_POST['groupid'] . "'");
        list($GroupID) = $DB->next_record();
    } else {
        $Val->SetFields('url', '1', 'regex', t('server.collages.not_torrent_link'), array('regex' => '/^' . TORRENT_GROUP_REGEX . '/i'));
        $Err = $Val->ValidateForm($_POST);

        if ($Err) {
            error($Err);
        }

        $URL = $_POST['url'];

        // Get torrent ID
        preg_match('/^' . TORRENT_GROUP_REGEX . '/i', $URL, $Matches);
        $TorrentID = $Matches[2];
        if (!$TorrentID || (int)$TorrentID == 0) {
            error(404);
        }

        $DB->query("
			SELECT ID
			FROM torrents_group
			WHERE ID = '$TorrentID'");
        list($GroupID) = $DB->next_record();
    }
    if (!$GroupID) {
        if ($FromAjax) {
            echo json_encode(['state' => false]);
            die();
        } else {
            error('The torrent was not found in the database.');
        }
    }
    add_torrent($CollageID, $GroupID);
} else {
    $URLs = explode("\n", $_REQUEST['urls']);
    $GroupIDs = array();
    $Err = '';
    foreach ($URLs as $Key => &$URL) {
        $URL = trim($URL);
        if ($URL == '') {
            unset($URLs[$Key]);
        }
    }
    unset($URL);

    if (!check_perms('site_collages_delete')) {
        if ($MaxGroups > 0 && ($NumTorrents + count($URLs) > $MaxGroups)) {
            $Err = "This collage can only hold $MaxGroups torrents.";
        }
        if ($MaxGroupsPerUser > 0 && ($GroupsForUser + count($URLs) > $MaxGroupsPerUser)) {
            $Err = "You may only have $MaxGroupsPerUser torrents in this collage.";
        }
    }

    foreach ($URLs as $URL) {
        $Matches = array();
        if (preg_match('/^' . TORRENT_GROUP_REGEX . '/i', $URL, $Matches)) {
            $GroupIDs[] = $Matches[2];
            $GroupID = $Matches[2];
        } else {
            $Err = "One of the entered URLs ($URL) does not correspond to a torrent group on the site.";
            break;
        }

        $DB->query("
			SELECT ID
			FROM torrents_group
			WHERE ID = '$GroupID'");
        if (!$DB->has_results()) {
            $Err = "One of the entered URLs ($URL) does not correspond to a torrent group on the site.";
            break;
        }
    }

    if ($Err) {
        error($Err);
    }

    foreach ($GroupIDs as $GroupID) {
        add_torrent($CollageID, $GroupID);
    }
}
if ($FromAjax) {
    echo json_encode(['state' => true]);
} else {
    header('Location: collages.php?id=' . $CollageID);
}
