<?php

sleep(6);

$DB->query("SELECT TorrentID FROM `freetorrents_timed` WHERE EndTime < NOW()");
if ($DB->has_results()) {
    $Torrents = $DB->collect("TorrentID");
    Torrents::freeleech_torrents($Torrents, 0, 0, true);
    $DB->query("DELETE FROM `freetorrents_timed` WHERE TorrentID IN (" . implode(', ', $Torrents) . ")");
}
