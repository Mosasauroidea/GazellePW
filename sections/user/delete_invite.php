<?
authorize();

$InviteKey = db_string($_GET['invite']);
$DB->query("
	SELECT InviterID,InviteID
	FROM invites
	WHERE InviteKey = '$InviteKey'");
list($UserID, $InviteID) = $DB->next_record();
if (!$DB->has_results() || $UserID != $LoggedUser['ID']) {
	error(404);
}
if ($InviteID != 0) {
	$DB->query("UPDATE `invites_typed` set Used=0 WHERE ID = '$InviteID'");
}
$DB->query("
	DELETE FROM invites
	WHERE InviteKey = '$InviteKey'");
$DB->query("
	DELETE FROM invites_history
	WHERE InviteKey = '$InviteKey'");
if (!check_perms('site_send_unlimited_invites')) {
	$DB->query("
		SELECT Invites
		FROM users_main
		WHERE ID = $UserID
		LIMIT 1");
	list($Invites) = $DB->next_record();
	if ($Invites < 10) {
		$DB->query("
			UPDATE users_main
			SET Invites = Invites + 1
			WHERE ID = '$UserID'");
		$Cache->begin_transaction("user_info_heavy_$UserID");
		$Cache->update_row(false, array('Invites' => '+1'));
		$Cache->commit_transaction(0);
	}
}
header('Location: user.php?action=invite');
