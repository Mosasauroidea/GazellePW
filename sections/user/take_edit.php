<?php

use Gazelle\Manager\Donation;
use Gazelle\Util\Crypto;

authorize();

if (!empty($_REQUEST['userid'])) {
    $UserID = $_REQUEST['userid'];
} else {
    $UserID = $LoggedUser['ID'];
}

if (!is_number($UserID)) {
    error(404);
}

//For this entire page, we should generally be using $UserID not $LoggedUser['ID'] and $U[] not $LoggedUser[]
$U = Users::user_info($UserID);
$UH = Users::user_heavy_info($UserID);

if (!$U) {
    error(404);
}

$Permissions = Permissions::get_permissions($U['PermissionID']);
if ($UserID != $LoggedUser['ID'] && !check_perms('users_edit_profiles', $Permissions['Class'])) {
    send_irc('PRIVMSG ' . CONFIG['ADMIN_CHAN'] . ' :User ' . $LoggedUser['Username'] . ' (' . site_url() . 'user.php?id=' . $LoggedUser['ID'] . ') just tried to edit the profile of ' . site_url() . 'user.php?id=' . $_REQUEST['userid']);
    error(403);
}

$Val->SetFields('stylesheet', 1, "number", "You forgot to select a stylesheet.");
$Val->SetFields('styleurl', 0, "regex", "You did not enter a valid stylesheet URL.", array('regex' => '/^' . CSS_REGEX . '$/i'));

// The next two are commented out because the drop-down menus were replaced with a check box and radio buttons
//$Val->SetFields('disablegrouping', 0, "number", "You forgot to select your torrent grouping option.");
//$Val->SetFields('torrentgrouping', 0, "number", "You forgot to select your torrent grouping option.");
$Val->SetFields('discogview', 1, "number", "You forgot to select your discography view option.", array('minlength' => 0, 'maxlength' => 1));
$Val->SetFields('postsperpage', 1, "number", "You forgot to select your posts per page option.", array('inarray' => array(25, 50, 100)));
//$Val->SetFields('hidecollage', 1, "number", "You forgot to select your collage option.", array('minlength' => 0, 'maxlength' => 1));
$Val->SetFields('collagecovers', 1, "number", "You forgot to select your collage option.");
$Val->SetFields('avatar', 0, "regex", "You did not enter a valid avatar URL.", array('regex' => "/^" . IMAGE_REGEX . "$/i"));
$Val->SetFields('email', 0, "email", "You did not enter a valid email address.");
$Val->SetFields('irckey', 0, "string", "You did not enter a valid IRC key. An IRC key must be between 6 and 32 characters long.", array('minlength' => 6, 'maxlength' => 32));
$Val->SetFields('new_pass_1', 0, "regex", "You did not enter a valid password. A strong password is 8 characters or longer, contains at least 1 lowercase and uppercase letter, and contains at least a number or symbol.", array('regex' => '/(?=^.{8,}$)(?=.*[^a-zA-Z])(?=.*[A-Z])(?=.*[a-z]).*$|.{20,}/'));
$Val->SetFields('new_pass_2', 1, "compare", "Your passwords do not match.", array('comparefield' => 'new_pass_1'));
if (check_perms('site_advanced_search')) {
    $Val->SetFields('searchtype', 1, "number", "You forgot to select your default search preference.", array('minlength' => 0, 'maxlength' => 1));
}

$Err = $Val->ValidateForm($_POST);
if ($Err) {
    error($Err);
    header("Location: user.php?action=edit&userid=$UserID");
    die();
}

// SettingTorrentTitle
$SettingTorrentTitle = [];
$SettingTorrentTitle['ReleaseGroup'] = (bool) $_POST['settingTorrentTitleReleaseGroup'];
$SettingTorrentTitle['Alternative'] = (bool) $_POST['settingTorrentTitleAlternative'];
$SettingTorrentTitle['Items'] = $_POST['settingTorrentTitleItems'] ? explode(',', $_POST['settingTorrentTitleItems']) : null;

// Begin building $Paranoia
// Reduce the user's input paranoia until it becomes consistent
if (isset($_POST['p_uniquegroups_l'])) {
    $_POST['p_uploads_l'] = 'on';
    $_POST['p_uploads_c'] = 'on';
}

if (isset($_POST['p_uploads_l'])) {
    $_POST['p_uniquegroups_l'] = 'on';
    $_POST['p_uniquegroups_c'] = 'on';
    $_POST['p_originals_l'] = 'on';
    $_POST['p_originals_c'] = 'on';
    $_POST['p_artistsadded'] = 'on';
    $_POST['p_badgedisplay'] = 'on';
}

if (isset($_POST['p_collagecontribs_l'])) {
    $_POST['p_collages_l'] = 'on';
    $_POST['p_collages_c'] = 'on';
}

if (isset($_POST['p_snatched_c']) && isset($_POST['p_seeding_c']) && isset($_POST['p_downloaded'])) {
    $_POST['p_requiredratio'] = 'on';
}

// if showing exactly 2 of stats, show all 3 of stats
$StatsShown = 0;
$Stats = array('downloaded', 'uploaded', 'ratio');
foreach ($Stats as $S) {
    if (isset($_POST["p_$S"])) {
        $StatsShown++;
    }
}

if ($StatsShown == 2) {
    foreach ($Stats as $S) {
        $_POST["p_$S"] = 'on';
    }
}

$Paranoia = array();
$Checkboxes = array('downloaded', 'uploaded', 'ratio', 'bonuspoints', 'lastseen', 'requiredratio', 'invitedcount', 'artistsadded', 'notifications', 'badgedisplay');
foreach ($Checkboxes as $C) {
    if (!isset($_POST["p_$C"])) {
        $Paranoia[] = $C;
    }
}
if (isset($_POST["p_emailshowtotc"])) {
    $Paranoia[] = "emailshowtotc";
}
$SimpleSelects = array('torrentcomments', 'collages', 'collagecontribs', 'uploads', 'uniquegroups', 'seeding', 'leeching', 'snatched', 'lossless', 'originals');
foreach ($SimpleSelects as $S) {
    if (!isset($_POST["p_$S" . '_c']) && !isset($_POST["p_$S" . '_l'])) {
        // Very paranoid - don't show count or list
        $Paranoia[] = "$S+";
    } elseif (!isset($_POST["p_$S" . '_l'])) {
        // A little paranoid - show count, don't show list
        $Paranoia[] = $S;
    }
}

$Bounties = array('requestsfilled', 'requestsvoted');
foreach ($Bounties as $B) {
    if (isset($_POST["p_$B" . '_list'])) {
        $_POST["p_$B" . '_count'] = 'on';
        $_POST["p_$B" . '_bounty'] = 'on';
    }
    if (!isset($_POST["p_$B" . '_list'])) {
        $Paranoia[] = $B . '_list';
    }
    if (!isset($_POST["p_$B" . '_count'])) {
        $Paranoia[] = $B . '_count';
    }
    if (!isset($_POST["p_$B" . '_bounty'])) {
        $Paranoia[] = $B . '_bounty';
    }
}

if (!isset($_POST['p_donor_heart'])) {
    $Paranoia[] = 'hide_donor_heart';
}

$donation = new Donation();

if (isset($_POST['p_donor_stats'])) {
    $donation->show($UserID);
} else {
    $donation->hide($UserID);
}

// End building $Paranoia


// API Key Helpers

function doesUserHasToken(int $UserID): bool {
    return G::$DB->scalar(
        "
        SELECT 1
        FROM api_applications
        WHERE UserID = $UserID"
    ) === 1;
}

function hasApiToken(int $userId, string $token): bool {
    return G::$DB->scalar(
        "
        SELECT 1
        FROM api_applications
        WHERE UserID = $userId
            AND Token = '$token'"
    ) === 1;
}

function revokeApiTokenById(int $UserID): int {
    G::$DB->prepared_query(
        "
        DELETE FROM api_applications
        WHERE UserID = ? ",
        $UserID
    );
    return G::$DB->affected_rows();
}

function createApiToken(int $UserID, string $key): string {
    $suffix = sprintf('%014d', $UserID);
    $name = "API_TOKEN";

    while (true) {
        // prevent collisions with an existing token name
        $token = base64UrlEncode(Crypto::encrypt(random_bytes(32) . $suffix, $key));
        if (!hasApiToken($UserID, $token))
            break;
    }

    G::$DB->prepared_query(
        "
        INSERT INTO api_applications
               (UserID, Name, Token)
        VALUES (?,       ?,    ?)",
        $UserID,
        $name,
        $token
    );
    return $token;
}

function base64UrlEncode($data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
// API Key Helpers

// Reset API Key
if (isset($_POST["resetApiKey"])) {
    G::$DB->begin_transaction();
    if (doesUserHasToken($UserID)) {
        // User has already created a token. We'll delete the entry here.
        revokeApiTokenById($UserID);
    }
    createApiToken($UserID, CONFIG['ENCKEY']);
    G::$DB->commit();
}

// Email change
$DB->query("
	SELECT Email
	FROM users_main
	WHERE ID = $UserID");
list($CurEmail) = $DB->next_record();
if ($CurEmail != $_POST['email']) {
    if (!check_perms('users_edit_profiles')) { // Non-admins have to authenticate to change email
        $Err = 403;
        /*
        $DB->query("
            SELECT PassHash, Secret
            FROM users_main
            WHERE ID = '".db_string($UserID)."'");
        list($PassHash,$Secret)=$DB->next_record();
        if (!Users::check_password($_POST['cur_pass'], $PassHash)) {
            $Err = 'You did not enter the correct password.';
        }
        */
    }
    if (!$Err) {
        $NewEmail = db_string($_POST['email']);


        //This piece of code will update the time of their last email change to the current time *not* the current change.
        $ChangerIP = db_string($LoggedUser['IP']);
        $DB->query("
			UPDATE users_history_emails
			SET Time = '" . sqltime() . "'
			WHERE UserID = '$UserID'
				AND Time = '0000-00-00 00:00:00'");
        $DB->query("
			INSERT INTO users_history_emails
				(UserID, Email, Time, IP)
			VALUES
				('$UserID', '$NewEmail', '0000-00-00 00:00:00', '" . db_string($_SERVER['REMOTE_ADDR']) . "')");
    } else {
        error($Err);
        header("Location: user.php?action=edit&userid=$UserID");
        die();
    }
}
//End email change

if (!$Err && !empty($_POST['cur_pass']) && !empty($_POST['new_pass_1']) && !empty($_POST['new_pass_2'])) {
    $DB->query("
		SELECT PassHash, Secret
		FROM users_main
		WHERE ID = '" . db_string($UserID) . "'");
    list($PassHash, $Secret) = $DB->next_record();

    if (Users::check_password($_POST['cur_pass'], $PassHash)) {
        if ($_POST['cur_pass'] == $_POST['new_pass_1']) {
            $Err = 'Your new password cannot be the same as your old password.';
        } else if ($_POST['new_pass_1'] !== $_POST['new_pass_2']) {
            $Err = 'You did not enter the same password twice.';
        } else {
            $ResetPassword = true;
        }
    } else {
        $Err = 'You did not enter the correct password.';
    }
}

if ($LoggedUser['DisableAvatar'] && $_POST['avatar'] != $U['Avatar']) {
    $Err = 'Your avatar privileges have been revoked.';
}

if ($Err) {
    error($Err);
    header("Location: user.php?action=edit&userid=$UserID");
    die();
}
$DB->query("
	SELECT SiteOptions
	FROM users_info
	WHERE UserID = $UserID");
list($SiteOptions) = $DB->next_record();

$Options = unserialize_array($SiteOptions);

if (!empty($LoggedUser['DefaultSearch'])) {
    $Options['DefaultSearch'] = $LoggedUser['DefaultSearch'];
}
$Options['DisableGrouping2']    = (!empty($_POST['disablegrouping']) ? 0 : 1);
$Options['TorrentGrouping']     = (!empty($_POST['torrentgrouping']) ? 1 : 0);
$Options['DiscogView']          = (!empty($_POST['discogview']) ? 1 : 0);
$Options['PostsPerPage']        = (int)$_POST['postsperpage'];
//$Options['HideCollage']         = (!empty($_POST['hidecollage']) ? 1 : 0);
$Options['CollageCovers']       = (empty($_POST['collagecovers']) ? 0 : $_POST['collagecovers']);
$Options['ShowTorFilter']       = (empty($_POST['showtfilter']) ? 0 : 1);
$Options['ShowHotMovieOnHomePage']       = (empty($_POST['showhotmovie']) ? 0 : 1);
$Options['AutoSubscribe']       = (!empty($_POST['autosubscribe']) ? 1 : 0);
$Options['DisableSmileys']      = (!empty($_POST['disablesmileys']) ? 1 : 0);
$Options['EnableMatureContent'] = (!empty($_POST['enablematurecontent']) ? 1 : 0);
$Options['AutoloadCommStats']   = (check_perms('users_mod') && !empty($_POST['autoload_comm_stats']) ? 1 : 0);
$Options['DisableAvatars']      = db_string($_POST['disableavatars']);
$Options['Identicons']          = (!empty($_POST['identicons']) ? (int)$_POST['identicons'] : 0);
$Options['DisablePMAvatars']    = (!empty($_POST['disablepmavatars']) ? 1 : 0);
$Options['NotifyOnQuote']       = (!empty($_POST['notifications_Quotes_popup']) ? 1 : 0);
$Options['ListUnreadPMsFirst']  = (!empty($_POST['list_unread_pms_first']) ? 1 : 0);
$Options['ShowSnatched']        = (!empty($_POST['showsnatched']) ? 1 : 0);
$Options['DisableAutoSave']     = (!empty($_POST['disableautosave']) ? 1 : 0);
$Options['NoVoteLinks']         = (!empty($_POST['novotelinks']) ? 1 : 0);
$Options['CoverArt']            = (int)!empty($_POST['coverart']);
$Options['Polt']            = (int)!empty($_POST['polt']);
$Options['Sub']            = (int)!empty($_POST['sub']);
$Options['Makers']            = (int)!empty($_POST['makers']);
$Options['ShowExtraCovers']     = (int)!empty($_POST['show_extra_covers']);
$Options['AutoComplete']        = (int)$_POST['autocomplete'];
// TODO: who is 24?
if ($LoggedUser['PermissionID'] == CONFIG['USER_CLASS']['SYSOP'] || $LoggedUser['PermissionID'] == 24) {
    $Options['SSPAccess']         = (!empty($_POST['ssp_access']) ? 1 : 0);
} else if ($UH['SSPAccess']) {
    $Options['SSPAccess'] = 1;
}
if (isset($LoggedUser['DisableFreeTorrentTop10'])) {
    $Options['DisableFreeTorrentTop10'] = $LoggedUser['DisableFreeTorrentTop10'];
}

if (!empty($_POST['sorthide'])) {
    $JSON = json_decode($_POST['sorthide']);
    foreach ($JSON as $J) {
        $E = explode('_', $J);
        $Options['SortHide'][$E[0]] = $E[1];
    }
} else {
    $Options['SortHide'] = array();
}

if (check_perms('site_advanced_search')) {
    $Options['SearchType'] = $_POST['searchtype'];
} else {
    unset($Options['SearchType']);
}

//TODO: Remove the following after a significant amount of time
unset($Options['ArtistNoRedirect']);
unset($Options['ShowQueryList']);
unset($Options['ShowCacheList']);

$DownloadAlt = isset($_POST['downloadalt']) ? 1 : 0;
$UnseededAlerts = isset($_POST['unseededalerts']) ? 1 : 0;
$ReportedAlerts = isset($_POST['reportedalerts']) ? 1 : 0;
$RequestsAlerts = isset($_POST['requestsalerts']) ? 1 : 0;
$NotifyOnDeleteSeeding = (!empty($_POST['notifyondeleteseeding']) ? 1 : 0);
$NotifyOnDeleteSnatched = (!empty($_POST['notifyondeletesnatched']) ? 1 : 0);
$NotifyOnDeleteDownloaded = (!empty($_POST['notifyondeletedownloaded']) ? 1 : 0);

$donation = new Donation;
$donation->updateReward($UserID);
NotificationsManager::save_settings($UserID);

// Information on how the user likes to download torrents is stored in cache
if ($DownloadAlt != $UH['DownloadAlt']) {
    $Cache->delete_value('user_' . $UH['torrent_pass']);
}

$Cache->begin_transaction("user_info_$UserID");
$Cache->update_row(false, array(
    'Avatar' => display_str($_POST['avatar']),
    'Paranoia' => $Paranoia
));
$Cache->commit_transaction(0);

$Cache->begin_transaction("user_info_heavy_$UserID");
$Cache->update_row(false, array(
    'StyleID' => $_POST['stylesheet'],
    'StyleURL' => display_str($_POST['styleurl']),
    'StyleTheme' => $_POST['style_theme'],
    'DownloadAlt' => $DownloadAlt,
    'SettingTorrentTitle' => $SettingTorrentTitle,
));
$Cache->update_row(false, $Options);
$Cache->commit_transaction(0);

$SQL = "
	UPDATE users_main AS m
		JOIN users_info AS i ON m.ID = i.UserID
	SET
		i.StyleID = '" . db_string($_POST['stylesheet']) . "',
		i.StyleURL = '" . db_string($_POST['styleurl']) . "',
		i.StyleTheme = '" . db_string($_POST['style_theme']) . "',
		i.Avatar = '" . db_string($_POST['avatar']) . "',
		i.SiteOptions = '" . db_string(serialize($Options)) . "',
		i.NotifyOnQuote = '" . db_string($Options['NotifyOnQuote']) . "',
		i.Info = '" . db_string($_POST['info']) . "',
		i.InfoTitle = '" . db_string($_POST['profile_title']) . "',
		i.DownloadAlt = '$DownloadAlt',
		i.UnseededAlerts = '$UnseededAlerts',
		i.ReportedAlerts = '$ReportedAlerts',
		i.RequestsAlerts = '$RequestsAlerts',
		i.NotifyOnDeleteSeeding = '$NotifyOnDeleteSeeding',
		i.NotifyOnDeleteSnatched = '$NotifyOnDeleteSnatched',
		i.NotifyOnDeleteDownloaded = '$NotifyOnDeleteDownloaded',
        i.SettingTorrentTitle = '" . db_string(json_encode($SettingTorrentTitle)) . "',
		m.Email = '" . db_string($_POST['email']) . "',
		m.IRCKey = '" . db_string($_POST['irckey']) . "',
		m.Paranoia = '" . db_string(serialize($Paranoia)) . "'";
$Cache->begin_transaction("user_info_heavy_$UserID");
$Cache->update_row(false, array('RequestsAlerts' => $RequestsAlerts));
$Cache->commit_transaction(0);
if ($ResetPassword) {
    $ChangerIP = db_string($LoggedUser['IP']);
    $PassHash = Users::make_password_hash($_POST['new_pass_1']);
    $SQL .= ",m.PassHash = '" . db_string($PassHash) . "'";
    $DB->query("
		INSERT INTO users_history_passwords
			(UserID, ChangerIP, ChangeTime)
		VALUES
			('$UserID', '$ChangerIP', '" . sqltime() . "')");
}

if (isset($_POST['resetpasskey'])) {
    $UserInfo = Users::user_heavy_info($UserID);
    $OldPassKey = db_string($UserInfo['torrent_pass']);
    $NewPassKey = db_string(Users::make_secret());
    $ChangerIP = db_string($LoggedUser['IP']);
    $SQL .= ",m.torrent_pass = '$NewPassKey'";
    $DB->query("
		INSERT INTO users_history_passkeys
			(UserID, OldPassKey, NewPassKey, ChangerIP, ChangeTime)
		VALUES
			('$UserID', '$OldPassKey', '$NewPassKey', '$ChangerIP', '" . sqltime() . "')");
    $Cache->begin_transaction("user_info_heavy_$UserID");
    $Cache->update_row(false, array('torrent_pass' => $NewPassKey));
    $Cache->commit_transaction(0);
    $Cache->delete_value("user_$OldPassKey");

    Tracker::update_tracker('change_passkey', array('oldpasskey' => $OldPassKey, 'newpasskey' => $NewPassKey));
}

$SQL .= "WHERE m.ID = '" . db_string($UserID) . "'";
$DB->query($SQL);

if ($ResetPassword) {
    logout_all_sessions();
}

header("Location: user.php?action=edit&userid=$UserID");
