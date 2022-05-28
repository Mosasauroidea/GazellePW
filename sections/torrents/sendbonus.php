<?
if (!isset($_POST['torrentid']) || !isset($_POST['bonus'])) {
    error(403);
}
$Bonuses = array(5, 30, 100, 300);

$TorrentID = intval($_POST['torrentid']);
$Bonus = intval($_POST['bonus']);
$FromUserID = $LoggedUser['ID'];
if (!in_array($Bonus, $Bonuses)) {
    error(403);
}

if ($Bonus > $LoggedUser['BonusPoints']) {
    $Send = false;
    $msg = 1;
} else {
    $Send = true;
    $msg = 0;
}
if ($Send) {
    $DB->query("select UserID from torrents where id = $TorrentID");
    list($ToUserID) = $DB->next_record();
    if (!$ToUserID || $ToUserID == $FromUserID) {
        $Send = false;
        $msg = 2;
    }
}
if ($Send) {
    $DB->query("insert ignore into torrents_send_bonus (TorrentID, FromUserID, Bonus) values ($TorrentID, $FromUserID, $Bonus)");
    if ($DB->affected_rows() != 1) {
        $Send = false;
        $msg = 3;
    }
}
if ($Send) {
    $DB->query("update users_main set BonusPoints = BonusPoints + ($Bonus * 0.9) where id = $ToUserID");
    $Cache->delete_value('user_stats_' . $ToUserID);
    $DB->query("update users_main set BonusPoints = BonusPoints - $Bonus where id = $FromUserID");
    $Cache->delete_value('user_stats_' . $FromUserID);
}
$DB->query("select sum(bonus) from torrents_send_bonus where TorrentID = $TorrentID");
list($Count) = $DB->next_record();
$DB->query("select BonusPoints from users_main where ID = $FromUserID");
list($BonusPoints) = $DB->next_record();
$LoggedUser['BonusPoints'] = $BonusPoints;
$BP = number_format($BonusPoints);
// $BP = number_format($LoggedUser['BonusPoints']);
echo json_encode(array('bonus' => $BP, 'count' => $Count, 'send' => $Send, 'msg' => $msg));
