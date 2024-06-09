<?php
updateYearMovie(date("Y") - 1, date("Y"), 30);
updateYearMovie(date("Y") - 3, date("Y") - 1, 180);

function updateYearMovie($fromYear, $toYear, $interval) {
    // Force update some movie rating
    global $DB;
    $DB->query("SELECT ID, IMDBID, DoubanID, Year from torrents_group where MOD(UNIX_TIMESTAMP(Time),$interval) = " . (date('z') + 1) % $interval . " and Year > " . $fromYear . " and Year <= " . $toYear);
    $Groups = G::$DB->to_array();
    foreach ($Groups as $Group) {
        updateMovieInfo2($Group);
    }
}

function updateMovieInfo2($Group) {
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
