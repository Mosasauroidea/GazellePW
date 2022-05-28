<?php

$DB->query("
		select ID, IMDBID, DoubanID, Year from torrents_group");
$Groups = G::$DB->to_array();
foreach ($Groups as $Group) {
    if (date('Y') - $Group['Year'] < 1) {
        Torrents::update_movie_info($Group['ID'], $Group['IMDBID'], $Group['DoubanID']);
    }
}
