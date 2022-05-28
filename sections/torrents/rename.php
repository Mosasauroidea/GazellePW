<?
authorize();

$GroupID = $_POST['groupid'];
$OldGroupID = $GroupID;
$NewName = $_POST['name'];
$NewSubName = $_POST['subname'];

if (!$GroupID || !is_number($GroupID)) {
	error(404);
}

if (empty($NewName)) {
	error('Torrent groups must have a name');
}

if (!check_perms('torrents_edit')) {
	error(403);
}

$DB->query("
	SELECT Name, SubName
	FROM torrents_group
	WHERE ID = $GroupID");
list($OldName, $OldSubName) = $DB->next_record(MYSQLI_NUM, false);

$DB->query("
	UPDATE torrents_group
	SET Name = '" . db_string($NewName) . "',
	SubName = '" . db_string($NewSubName) . "'
	WHERE ID = '$GroupID'");
$Cache->delete_value("torrents_details_$GroupID");

Torrents::update_hash($GroupID);
$OldSubName = $OldSubName ? " [$OldSubName]" : "";
$NewSubName = $NewSubName ? " [$NewSubName]" : "";
Misc::write_log("Torrent Group $GroupID ($OldName)$OldSubName was renamed to \"$NewName$NewSubName\" from \"$OldName$OldSubName\" by " . $LoggedUser['Username']);
Torrents::write_group_log($GroupID, 0, $LoggedUser['ID'], "renamed to \"$NewName$NewSubName\" from \"$OldName$OldSubName\"", 0);

header("Location: torrents.php?id=$GroupID");
