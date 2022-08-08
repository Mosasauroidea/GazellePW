<?
if (!isset($_GET['imdb']) && !isset($_GET['group'])) {
    error(403);
}
$IMDB = $_GET['imdb'];
$Group = $_GET['group'];
$IMDBID = null;
$GroupID = null;
if (preg_match('/^(tt\d+)$/', $IMDB, $IMDBMatch)) {
    $IMDBID = $IMDBMatch[1];
}

if (preg_match('/^' . TORRENT_GROUP_REGEX . '/i', $Group, $Matches)) {
    $GroupID = $Matches[2];
}
if (empty($IMDBID) && empty($GroupID)) {
    ajax_json_error("invalid-imdb-id", 1);
}
if (!empty($IMDBID)) {
    G::$DB->query("select ID from torrents_group where IMDBID='$IMDBID'");
    list($GroupID) = G::$DB->next_record();
}
if (!empty($GroupID)) {
    try {
        $Ret = MOVIE::get_movie_fill_info_by_group_id($GroupID, false);
        $Ret['FillSource'] = 'group';
        if (!$Ret) {
            ajax_json_error_with_code(3, "internal-error");
        }
        $Ret['GroupLink'] = CONFIG['SITE_URL'] . '/torrents.php?id=' . $GroupID;

        ajax_json_success($Ret, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        ajax_json_error_with_code(3, $e->getMessage());
    }
}
if (!empty($IMDBID)) {
    try {
        $Ret = MOVIE::get_movie_fill_info($IMDBID, false);
        if (!$Ret) {
            ajax_json_error_with_code(3, "internal-error");
        }
        if ($GroupID) {
            $Ret['GroupID'] = $GroupID;
            $Ret['GroupLink'] = CONFIG['SITE_URL'] . '/torrents.php?id=' . $GroupID;
        }
        ajax_json_success($Ret, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        ajax_json_error_with_code(3, $e->getMessage());
    }
}

ajax_json_error_with_code(3, "internal-error");
