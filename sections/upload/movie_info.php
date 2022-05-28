<?
if (isset($_GET['imdbid'])) {
    $IMDBID = $_GET['imdbid'];
    $CheckOnly = false;
    if (isset($_GET['check_only']) && $_GET['check_only']) {
        $CheckOnly = true;
    }

    if (preg_match('/^tt\d+$/', $IMDBID)) {
        G::$DB->query("select ID from torrents_group where IMDBID='$IMDBID'");
        list($GroupID) = G::$DB->next_record();
        if ($GroupID) {
            if ($CheckOnly) {
                ajax_json_success(["Dupe" => true, "GroupID" => $GroupID]);
            } else {
                ajax_json_error_with_code(2, ["Dupe" => true, "GroupID" => $GroupID]);
            }
        } else {
            if ($CheckOnly) {
                ajax_json_success(["Dupe" => false]);
            } else {
                try {
                    $Ret = MOVIE::get_movie_fill_info($IMDBID, false);
                    if (!$Ret) {
                        ajax_json_error_with_code(3, "internal-error");
                    }
                    ajax_json_success($Ret, JSON_UNESCAPED_UNICODE);
                } catch (Exception $e) {
                    ajax_json_error_with_code(3, $e->getMessage());
                }
            }
        }
    } else {
        ajax_json_error("invalid-imdb-id", 1);
    }
} else {
    error(403);
}
