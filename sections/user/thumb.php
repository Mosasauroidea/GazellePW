<?

if (!isset($_POST['type']) || !isset($_POST['userid']) || !isset($_POST['itemid'])) {
    error(403);
}
$Types = array('wiki', 'post', 'torrent');

$Type = $_POST['type'];
$FromUserID = $LoggedUser['ID'];
$ToUserID = intval($_POST['userid']);
$ItemID = intval($_POST['itemid']);

if (!in_array($Type, $Types) || $ToUserID <= 0 || $ItemID <= 0 || $FromUserID == $ToUserID) {
    error(403);
}

switch ($Type) {
    case "post":
        $DB->query("select AuthorID from forums_posts where ID = $ItemID");
        break;
    case "torrent":
        $DB->query("select UserID from torrents where ID = $ItemID");
        break;
}
list($touserid) = $DB->next_record();
if ($touserid != $ToUserID) {
    error(403);
}
if ($Thumb) {
    $DB->query("INSERT IGNORE INTO `thumb`(`ItemID`, `Type`, `FromUserID`, `ToUserID`) VALUES ($ItemID, '$Type', $FromUserID, $ToUserID)");
} else {
    $DB->query("delete from thumb where ItemId = $ItemID and Type = '$Type' and FromUserID = $FromUserID and ToUserID = $ToUserID");
}
$DB->query("select count(1) from thumb where ItemId = $ItemID and Type = '$Type' and ToUserID = $ToUserID");
list($Count) = $DB->next_record();
echo json_encode(array('count' => $Count));
