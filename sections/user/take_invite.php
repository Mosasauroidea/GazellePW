<?

/**
 * Hello there. If you are refactoring this code, please note that this functionality also sort of exists in /classes/referral.class.php
 * Super sorry for doing that, but this is totally not reusable.
 */

if (!$UserCount = $Cache->get_value('stats_user_count')) {
    $DB->query("
		SELECT COUNT(ID)
		FROM users_main
		WHERE Enabled = '1'");
    list($UserCount) = $DB->next_record();
    $Cache->cache_value('stats_user_count', $UserCount, 0);
}

$UserID = $LoggedUser['ID'];

//This is where we handle things passed to us
authorize();

$DB->query("
	SELECT can_leech
	FROM users_main
	WHERE ID = $UserID");
list($CanLeech) = $DB->next_record();
//SELECT count(1) FROM `invites_history` WHERE `UserID` = 198 and `Time` > date_sub(now(), INTERVAL 72 HOUR)
$TimeSpace = array();

if (isset($TimeSpace[$LoggedUser['Class']])) {
    $DB->query("
		SELECT count(1) , date_add(`Time`,INTERVAL " . $TimeSpace[$LoggedUser['Class']] . " HOUR)
		FROM `invites_history` 
		WHERE `UserID` = $UserID and 
			`Time` > date_sub(now(), INTERVAL " . $TimeSpace[$LoggedUser['Class']] . " HOUR)");
    list($HasInvinte, $Time) = $DB->next_record();
} else {
    $HasInvinte = false;
}
if (
    $LoggedUser['RatioWatch']
    || !$CanLeech
    || $LoggedUser['DisableInvites'] == '1'
    || $LoggedUser['Invites'] == 0
    && !check_perms('site_send_unlimited_invites')
    || ($UserCount >= CONFIG['USER_LIMIT']
        && CONFIG['USER_LIMIT'] != 0
        && !check_perms('site_can_invite_always'))
) {
    error(403);
}
if ($HasInvinte) {
    error("You could invite others after $Time.", false, false, "Warning!");
}
$Email = $_POST['email'];
$Username = $LoggedUser['Username'];
$SiteName = CONFIG['SITE_NAME'];
$TGDisableChannel = CONFIG['TG_DISBALE_CHANNEL'];
$TGDisableChannelName = CONFIG['TG_DISABLE_CHANNEL_NAME'];
$SiteURL = site_url();
$InviteExpires = time_plus(60 * 60 * 24 * 3); // 3 days
$InviteReason = check_perms('users_invite_notes') ? db_string($_POST['reason']) : '';

//MultiInvite
if (strpos($Email, '|') !== false && check_perms('site_send_unlimited_invites')) {
    $Emails = explode('|', $Email);
} else {
    $Emails = array($Email);
}
foreach ($Emails as $CurEmail) {
    if (!preg_match("/^" . EMAIL_REGEX . "$/i", $CurEmail)) {
        if (count($Emails) > 1) {
            continue;
        } else {
            error('Invalid email.');
            header('Location: user.php?action=invite');
            die();
        }
    }
    $DB->query("
		SELECT Expires, InviterID
		FROM invites
		WHERE InviterID = " . $LoggedUser['ID'] . "
			AND Email LIKE '$CurEmail'");
    if ($DB->has_results()) {
        list($Expires, $InviterID) = $DB->next_record();
        if ($InviterID == $LoggedUser['ID']) {
            error('You already have a pending invite to that address!');
            header('Location: user.php?action=invite');
        } else {
            error('This email has already had an account at our site, <a href="/rules.php">do not create more than one account</a>.');
            header('Location: user.php?action=invite');
        }
        die();
    }
    $InviteKey = db_string(Users::make_secret());

    $DB->query("
		INSERT INTO invites
			(InviterID, InviteKey, Email, Expires, Reason)
		VALUES
			('$LoggedUser[ID]', '$InviteKey', '" . db_string($CurEmail) . "', '$InviteExpires', '$InviteReason')");
    $DB->query("
		INSERT INTO invites_history
			(UserID, Email, InviteKey)
		VALUES
			('$LoggedUser[ID]', '" . db_string($CurEmail) . "', '$InviteKey')");

    if (!check_perms('site_send_unlimited_invites')) {
        $DB->query("
			UPDATE users_main
			SET Invites = GREATEST(Invites, 1) - 1
			WHERE ID = '$LoggedUser[ID]'");
        $DB->query(
            "select ID from (SELECT ID FROM `invites_typed` WHERE UserID='$LoggedUser[ID]' and Type='time' and Used = 0 ORDER BY `EndTime`) a
			union all
			select ID from (select ID FROM `invites_typed` WHERE UserID='$LoggedUser[ID]' and Type='count' and Used = 0) b 
			limit 1"
        );
        $UsedInviteID = $DB->collect('ID');
        if (count($UsedInviteID) > 0) {
            $DB->query("UPDATE `invites_typed` set Used=1 WHERE ID = '$UsedInviteID[0]'");
            $DB->query("UPDATE `invites` set InviteID='$UsedInviteID[0]' WHERE InviteKey = '$InviteKey'");
        }
        $Cache->begin_transaction('user_info_heavy_' . $LoggedUser['ID']);
        $Cache->update_row(false, array('Invites' => '-1'));
        if (count($UsedInviteID) > 0) {
            $Cache->update_row(false, array('TimedInvites' => '-1'));
        }
        $Cache->commit_transaction(0);
    }
    Misc::send_email_with_tpl($CurEmail, 'invite', [
        'SiteName' => CONFIG['SITE_NAME'],
        'SiteURL' => CONFIG['SITE_URL'],
        'CurEmail' => $CurEmail,
        'InviteKey' => $InviteKey,
        'UserName' => $Username,
        'TGDisableChannel' => $TGDisableChannel,
        'TGDisableChannelName' => $TGDisableChannelName,
    ], 'text/html');
}

header('Location: user.php?action=invite');
