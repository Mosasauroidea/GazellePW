<?php

use Gazelle\Torrent\TorrentSlot;

require_once(__DIR__ . '/../classes/classloader.php');
require_once(__DIR__ . '/../classes/config.php');
require_once(__DIR__ . '/../classes/const.php');
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../classes/util.php');
require_once(__DIR__ . '/../classes/mysql.class.php');
require_once(__DIR__ . '/../classes/cache.class.php');
require_once(__DIR__ . '/../classes/time.class.php');

$DB = new DB_MYSQL;
$Cache = new CACHE($CONFIG['MemcachedServers']);
$Debug = new DEBUG;
$Debug->handle_errors();

G::$Cache = $Cache;
G::$DB = $DB;
G::$Debug = $Debug;


$DB->query("SELECT 
	ID, Processing, Resolution, Codec, SpecialSub, ChineseDubbed, SubtitleType, Subtitles
	FROM 
	torrents where Slot <> 1");
foreach ($DB->to_array('ID', MYSQLI_ASSOC) as $ID => $Torrent) {
    $Slot = TorrentSlot::CalSlot($Torrent);
    $DB->query("UPDATE torrents SET Slot=$Slot WHERE ID=$ID");
}
