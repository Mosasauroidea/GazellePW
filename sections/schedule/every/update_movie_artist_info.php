<?php
$DB->query("
		select ID, IMDBID, DoubanID, Year from torrents_group where Time > '" . time_minus(1000) . "'");
$Groups = G::$DB->to_array();
foreach ($Groups as $Group) {
	Torrents::update_movie_artist_info($Group['ID'], $Group['IMDBID']);
}
