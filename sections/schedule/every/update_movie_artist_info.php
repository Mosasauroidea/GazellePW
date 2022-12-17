<?php
$DB->query("
		select ID, IMDBID, DoubanID, Year from torrents_group where Time > '" . time_minus(1000) . "'");
$Groups = G::$DB->to_array();
foreach ($Groups as $Group) {
    $GroupID = $Group['ID'];
    Torrents::update_movie_artist_info($GroupID, $Group['IMDBID']);
    echo "Update group $GroupID artist info success.\n";
}
