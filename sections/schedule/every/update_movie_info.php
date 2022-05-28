<?php
$DB->query("
		select ID, IMDBID, DoubanID, Year from torrents_group where Time > '" . time_minus(3600) . "'");
$Groups = G::$DB->to_array();
foreach ($Groups as $Group) {
	Torrents::update_movie_info($Group['ID'], $Group['IMDBID'], $Group['DoubanID'], false);
}
