<?php

use Gazelle\Manager\ActionTrigger;

if (empty($_GET['torrentid'])) {
    error(403);
}

if (check_perms('torrents_check')) {
    $CheckAllTorrents = !$LoggedUser['DisableCheckAll'];
} else {
    $CheckAllTorrents = false;
}
if (check_perms('self_torrents_check')) {
    $CheckSelfTorrents = !$LoggedUser['DisableCheckSelf'];
} else {
    $CheckSelfTorrents = false;
}
function canCheckTorrent($TorrentID) {
    global $CheckAllTorrents, $CheckSelfTorrents, $LoggedUser;
    if ($CheckAllTorrents) {
        return true;
    } else if ($CheckSelfTorrents) {
        G::$DB->query("select 1 from torrents where userid=" . $LoggedUser['ID'] . " and id=$TorrentID");
        return G::$DB->has_results();
    } else {
        return false;
    }
}

$TorrentID = intval($_GET['torrentid']);

if (!canCheckTorrent($TorrentID)) {
    error(403);
}

$Checked = intval($_GET['checked']);
$Cache->cache_value("torrent_checked_$TorrentID", $Checked ? $LoggedUser['ID'] : 0);
$DB->query("UPDATE `torrents` SET `Checked`=" . ($Checked ? $LoggedUser['ID'] : 0) . " WHERE `ID`=$TorrentID");
$GroupID = Torrents::torrentid_to_groupid($TorrentID);
Torrents::update_hash($GroupID);
G::$Cache->delete_value("torrent_group_$GroupID");
G::$Cache->delete_value("torrents_details_$GroupID");
$DB->query("insert into torrents_check (UserID, TorrentID, Type) values (" . $LoggedUser['ID'] . ", $TorrentID, " . ($Checked ? "1" : "0") . ")");
Misc::write_log("Torrent $TorrentID was " . ($Checked ? "" : "un") . "checked by " . $LoggedUser['Username']);
$trigger =  new ActionTrigger;
$trigger->triggerTorrentCheck($TorrentID);
echo json_encode(array('ret' => 'success'));
