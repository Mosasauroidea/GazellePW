<?php
$DB->query("
		select ID, IMDBID, DoubanID, Year from torrents_group where Time > '" . time_minus(3600) . "'");
$Groups = G::$DB->to_array();
foreach ($Groups as $Group) {
    if (empty($Group['IMDBID'])) {
        continue;
    }
    $DoubanID = $Group['DoubanID'];
    if (empty($DoubanID)) {
        $DoubanData = MOVIE::get_douban_data($IMDBID, $Force);
        $DoubanID = $DoubanData->id;
    }
    Torrents::update_movie_info($Group['ID'], $Group['IMDBID'], $DoubanID, false);
}
