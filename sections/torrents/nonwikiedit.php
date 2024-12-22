<?

authorize();

//Set by system
if (!$_POST['groupid'] || !is_number($_POST['groupid'])) {
    error(404);
}
$GroupID = $_POST['groupid'];

//Usual perm checks
if (!check_perms('torrents_edit')) {
    $DB->query("
		SELECT UserID
		FROM torrents
		WHERE GroupID = $GroupID");
    if (!in_array($LoggedUser['ID'], $DB->collect('UserID'))) {
        error(403);
    }
}


if (!check_perms('torrents_freeleech')) {
    error(403);
}


$Free = (int)$_POST['freeleech'];
if (!in_array($Free, array(0, 1, 2, 11, 12, 13))) {
    error(404);
}
$Properties['FreeLeech'] = $Free;

if ($Free == 0) {
    $FreeType = 0;
} else {
    $FreeType = (int)$_POST['freeleechtype'];
    if (!in_array($Free, array(0, 1, 2, 3, 11, 12, 13))) {
        error(404);
    }
}
$Properties['FreeLeechType'] = $FreeType;
$LimitFree = isset($_POST['limit-time']) ? 1 : 0;
if (in_array($Free, array(1, 11, 12, 13)) && $LimitFree) {
    $FreeDate = db_string($_POST['free-date']);
    $FreeTime = db_string(substr($_POST['free-time'], 0, 2));
}

if (isset($_POST['freeleechtype']) && in_array($_POST['freeleechtype'], array(0, 1, 2, 3))) {
    $FreeType = $_POST['freeleechtype'];
} else {
    error(404);
}

Torrents::freeleech_groups($GroupID, $Free, $FreeType, $FreeDate . ' ' . $FreeTime . ':00');

Torrents::update_hash($GroupID);
$Cache->delete_value("torrents_details_$GroupID");

header("Location: torrents.php?id=$GroupID");
