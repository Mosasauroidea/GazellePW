<?php

require(__DIR__ . '/../classes/includes.php');

use Gazelle\Torrent\TorrentSlot;

$DB->query("SELECT 
	ID, Processing, Resolution, Codec, SpecialSub, ChineseDubbed, SubtitleType, Subtitles
	FROM 
	torrents where Slot <> 1");
foreach ($DB->to_array('ID', MYSQLI_ASSOC) as $ID => $Torrent) {
    $Slot = TorrentSlot::CalSlot($Torrent);
    $DB->query("UPDATE torrents SET Slot=$Slot WHERE ID=$ID");
}
