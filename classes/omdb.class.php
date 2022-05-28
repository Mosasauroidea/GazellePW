<?
class OMDB {
    public static function get($IMDBID, $Online = false) {
        if (preg_match('/^tt\d+$/', $IMDBID)) {
            if ($Online || ($Json = self::db_get($IMDBID)) === false) {
                $Json = self::online_get($IMDBID);
            }
        } else {
            $Json = false;
        }
        return $Json ? $Json : '{"Response":"False"}';
    }
    private static function online_get($IMDBID) {
        $Json = file_get_contents("http://www.omdbapi.com/?apikey=cbcdf914&i=$IMDBID");
        if (strpos($Json, '"Response":"False"') === false) {
            G::$DB->query("INSERT INTO movie_info_cache (IMDBID, OMDBData) VALUES ('$IMDBID', '" . db_string($Json) . "') ON DUPLICATE KEY UPDATE OMDBData=VALUES(OMDBData);");
            return $Json;
        } else {
            return false;
        }
    }
    private static function db_get($IMDBID) {
        G::$DB->query("SELECT OMDBData
            FROM movie_info_cache
            WHERE IMDBID='$IMDBID'");
        if (G::$DB->has_results()) {
            list($Json) = G::$DB->next_record(MYSQLI_NUM, false);
            return $Json;
        } else {
            return false;
        }
    }
}
