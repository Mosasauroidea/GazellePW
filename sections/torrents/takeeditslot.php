<?
authorize();

$GroupID = $_POST['groupid'];

$TorrentIDs = $_POST['torrents'];
$TorrentSlots = $_POST['slots'];

Torrents::update_slots($TorrentIDs, $TorrentSlots, $GroupID);

header("Location: torrents.php?id=$GroupID&view=slot#slot");
