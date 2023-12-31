<?php
// Update douban info daily
$DB->query("SELECT ID, IMDBID, DoubanID, Year from torrents_group where Time > '" . time_minus(3600) . "'");
$Groups = G::$DB->to_array();
foreach ($Groups as $Group) {
    updateMovieInfo($Group);
}

function updateMovieInfo($Group) {
    $IMDBID = $Group['IMDBID'];
    $GroupID = $Group['ID'];
    $DoubanID = $Group['DoubanID'];
    $Year = $Group['Year'];
    if (empty($IMDBID)) {
        return;
    }
    if (empty($DoubanID)) {
        $DoubanData = MOVIE::get_douban_data($IMDBID, true);
        $DoubanID = $DoubanData->id;
    }
    echo "Update group $GroupID ($Year) movie info success.\n";
    Torrents::update_movie_info($GroupID, $IMDBID, $DoubanID, true);
}
