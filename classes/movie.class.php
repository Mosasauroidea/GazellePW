<?

use Curl\MultiCurl;
use Curl\Curl;
use Gazelle\Util\Time;

class MOVIE {
    public static function get_omdb_data($IMDBID, $Refresh = false) {
        G::$DB->query("SELECT OMDBData
        FROM movie_info_cache
        WHERE IMDBID='$IMDBID'");
        if (G::$DB->has_results()) {
            list($OMDBData) = G::$DB->next_record(MYSQLI_NUM, false);
        }
        $omdb_key = CONFIG['OMDB_API_KEY'];
        if (!empty($omdb_key) && (empty($OMDBData) || $Refresh)) {
            $curl = new Curl();
            $curl->get('http://www.omdbapi.com', ['apikey' => $omdb_key, 'i' => $IMDBID]);
            if ($curl->error) {
            } else {
                $OMDBData = json_encode($curl->response, JSON_UNESCAPED_UNICODE);
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, OMDBData, OMDBTime) VALUES('$IMDBID', '" . db_string($OMDBData) . "', '" . Time::sqlTime() . "')  ON DUPLICATE KEY UPDATE OMDBData='" . db_string($OMDBData) . "', OMDBTime='" . Time::sqlTime() . "'");
                // query again? db_string escape?
                G::$DB->query("SELECT OMDBData
                FROM movie_info_cache
                WHERE IMDBID='$IMDBID'");
                if (G::$DB->has_results()) {
                    list($OMDBData) = G::$DB->next_record(MYSQLI_NUM, false);
                }
            }
        }
        if (!empty($OMDBData)) {
            return json_decode($OMDBData);
        }
        return null;
    }

    public static function get_douban_data_by_doubanid($DoubanID, $Refresh = false) {
        $DoubanData = null;
        G::$DB->query("SELECT DoubanData 
            FROM movie_info_cache
            WHERE DoubanID='$DoubanID'");
        if (G::$DB->has_results()) {
            list($DoubanData) = G::$DB->next_record(MYSQLI_NUM, false);
        }
        $douban_api_url = CONFIG['DOUBAN_API_URL'];
        if (!empty($douban_api_url) && (empty($DoubanData) || $Refresh)) {
            $curl = new Curl();
            $curl->get(CONFIG['DOUBAN_API_URL'] . '/search?douban-id=' . $DoubanID);
            if ($curl->error) {
            } else {
                $DoubanData = json_encode($curl->response, JSON_UNESCAPED_UNICODE);
                if ($curl->response && $curl->response->data && $curl->response->data->douban) {
                    $DoubanID = $curl->response->data->douban->id;
                    $IMDBID = $curl->response->data->imdb->id;
                    G::$DB->query("INSERT INTO movie_info_cache (IMDBID, DoubanData, DoubanTime, DoubanID) VALUES('$IMDBID', '" . db_string($DoubanData) . "', '" . Time::sqlTime() . "', $DoubanID)  ON DUPLICATE KEY UPDATE DoubanData=VALUES(DoubanData), DoubanTime=VALUES(DoubanTime), DoubanID=VALUES(DoubanID)");
                    G::$DB->query("SELECT DoubanData 
                    FROM movie_info_cache
                    WHERE DoubanID='$DoubanID'");
                    if (G::$DB->has_results()) {
                        list($DoubanData) = G::$DB->next_record(MYSQLI_NUM, false);
                    }
                } else {
                    $DoubanID = 'null';
                }
            }
        }
        if (!empty($DoubanData)) {
            $DoubanResult = json_decode($DoubanData);
            if ($DoubanResult && $DoubanResult->data) {
                if ($DoubanResult->data->douban) {
                    return $DoubanResult->data->douban;
                }
            }
        }
        return null;
    }


    public static function get_douban_data($IMDBID, $Refresh = false) {
        $DoubanData = null;
        G::$DB->query("SELECT DoubanData 
            FROM movie_info_cache
            WHERE IMDBID='$IMDBID'");
        if (G::$DB->has_results()) {
            list($DoubanData) = G::$DB->next_record(MYSQLI_NUM, false);
        }

        $douban_api_url = CONFIG['DOUBAN_API_URL'];
        if (!empty($douban_api_url) && (empty($DoubanData) || $Refresh)) {
            $curl = new Curl();
            $curl->get(CONFIG['DOUBAN_API_URL'] . '/search?imdb-id=' . $IMDBID);
            if ($curl->error) {
            } else {
                $DoubanData = json_encode($curl->response, JSON_UNESCAPED_UNICODE);
                if ($curl->response && $curl->response->data && $curl->response->data->douban) {
                    $DoubanID = $curl->response->data->douban->id;
                } else {
                    $DoubanID = 'null';
                }
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, DoubanData, DoubanTime, DoubanID) VALUES('$IMDBID', '" . db_string($DoubanData) . "', '" . Time::sqlTime() . "', $DoubanID)  ON DUPLICATE KEY UPDATE DoubanData=VALUES(DoubanData), DoubanTime=VALUES(DoubanTime), DoubanID=VALUES(DoubanID)");
                G::$DB->query("SELECT DoubanData 
                FROM movie_info_cache
                WHERE IMDBID='$IMDBID'");
                if (G::$DB->has_results()) {
                    list($DoubanData) = G::$DB->next_record(MYSQLI_NUM, false);
                }
            }
        }
        if (!empty($DoubanData)) {
            $DoubanResult = json_decode($DoubanData);
            if ($DoubanResult && $DoubanResult->data) {
                if ($DoubanResult->data->douban) {
                    return $DoubanResult->data->douban;
                }
            }
        }
        return null;
    }

    public static function get_default_artist($IMDBID) {
        $Info = array();
        $Info['Image'] = "";
        $Info['IMDBID'] = $IMDBID;
        $Info['Description'] = "";
        $Info['MainDescription'] = "";
        $Info['Birthday'] = "";
        $Info['PlaceOfBirth'] = "";
        $Info['Alias'] = array();
        return $Info;
    }

    public static function get_artists_seq($IMDBIDs = array(), $MovieIMDBID = null, $Refresh = false) {
        $ArtistInfos = [];
        $IMDBTrunkIDs = array_chunk($IMDBIDs, 5);
        foreach ($IMDBTrunkIDs as $IMDBID) {
            $Info = self::get_artists($IMDBID, null, null, $Refresh);
            foreach ($Info as $k => $v) {
                $ArtistInfos[$k] = $v;
            }
        }
        $IMDBImage = [];
        if ($MovieIMDBID) {
            $actors = self::get_imdb_actor_data($MovieIMDBID, $Refresh);
            $all = [$actors->Directors, $actors->Writters, $actors->Casts, $actors->Producers, $actors->Composers, $actors->Cinematographers];
            foreach ($all as $index => $actor) {
                foreach ($actor as $key => $value) {
                    $IMDBImage['nm' . $value->imdb] = $value->photo;
                }
            }
        }
        foreach ($ArtistInfos as $IMDBID => $ArtistInfo) {
            if (empty($ArtistInfo['Image']) && !empty($IMDBImage[$ArtistInfo['IMDBID']])) {
                $ArtistInfo['Image'] = $IMDBImage[$ArtistInfo['IMDBID']];
                $ArtistInfos[$IMDBID] = $ArtistInfo;
            }
        }
        return $ArtistInfos;
    }

    public static function get_artists($IMDBIDs = array(), $MovieIMDBID = null, $Limit = null, $Refresh = false) {
        $NotFoundID = array_fill_keys($IMDBIDs, false);
        $TMDBIDs = array();
        $Data = [];
        foreach ($IMDBIDs as $key => $value) {
            $info = ['IMDBID' => $value];
            $Data[$value] = $info;
        }
        if (!$Refresh) {
            G::$DB->query("SELECT IMDBID, TMDBData, MainTMDBData, TMDBID
                FROM artist_info_cache
                WHERE IMDBID in ('" . implode("','", $IMDBIDs) . "')");

            if (G::$DB->has_results()) {
                while ($Record = G::$DB->next_record(MYSQLI_ASSOC, false)) {
                    if (!empty($Record['TMDBData']) && !empty($Record['MainTMDBData'])) {
                        $Data[$Record['IMDBID']] = $Record;
                        unset($NotFoundID[$Record['IMDBID']]);
                    } else if (!empty($Record['TMDBID'])) {
                        unset($NotFoundID[$Record['IMDBID']]);
                        $TMDBIDs[] = $Record['TMDBID'];
                    }
                }
            }
        }
        $MissData = [];
        $key = CONFIG['TMDB_API_KEY'];
        if (!empty($key)) {
            $multi_curl = new MultiCurl();
            $multi_curl->setConnectTimeout(10);
            $count = 1;
            foreach ($NotFoundID as $IMDBID => $v) {
                if ($Limit && $count > $Limit) {
                    break;
                }
                $tmdb = $multi_curl->addGet('https://api.themoviedb.org/3/find/' . $IMDBID, ['api_key' => $key, 'external_source' => 'imdb_id']);
                $tmdb->myTag = $IMDBID;
                $count++;
            }
            $multi_curl->success(function ($instance)  use (&$TMDBIDs, &$MissData) {
                $r = $instance->response->person_results;
                if (count($r) > 0) {
                    $TMDBIDs[] = $r[0]->id;
                    $MissData[$instance->myTag]['TMDBID'] = $r[0]->id;
                }
            });
            $multi_curl->start();

            $multi_curl2 = new MultiCurl();
            $multi_curl2->setConnectTimeout(10);
            foreach ($TMDBIDs as $k => $TMDBID) {
                $multi_curl2->addGet('https://api.themoviedb.org/3/person/' . $TMDBID, ['api_key' => $key, 'language' => self::tmdb_lang(Lang::SUB_LANG)]);
            }
            $multi_curl2->success(function ($instance) use (&$MissData) {
                $r = $instance->response;
                $MissData[$r->imdb_id]['TMDBData'] = json_encode($r, JSON_UNESCAPED_UNICODE);
                $MissData[$r->imdb_id]['IMDBID'] = $r->imdb_id;
            });
            $multi_curl2->start();


            $multi_curl3 = new MultiCurl();
            $multi_curl3->setConnectTimeout(10);
            foreach ($TMDBIDs as $k => $TMDBID) {
                $multi_curl3->addGet('https://api.themoviedb.org/3/person/' . $TMDBID, ['api_key' => $key, 'language' => self::tmdb_lang(Lang::MAIN_LANG)]);
            }
            $multi_curl3->success(function ($instance) use (&$MissData) {
                $r = $instance->response;
                $MissData[$r->imdb_id]['MainTMDBData'] = json_encode($r, JSON_UNESCAPED_UNICODE);
            });
            $multi_curl3->start();


            foreach ($MissData as $key => $value) {
                $Data[$key] = $value;
            }
            // write to db
            if (count($MissData) > 0) {
                $SQL = [];
                foreach ($MissData as $key => $value) {
                    $IMDBID = $key;
                    $TMDBID = $value['TMDBID'] ? $value['TMDBID'] : 'null';
                    $TMDBData = $value['TMDBData'] ? $value['TMDBData'] : '';
                    $MainTMDBData = $value['MainTMDBData'] ? $value['MainTMDBData'] : '';
                    $SQL[] = "('" . $key . "', " . $TMDBID . ", '" . db_string($TMDBData) . "', '" . db_string($MainTMDBData) . "', '" . Time::sqltime() . "', " . "'" . Time::sqltime() . "')";
                }
                $FinalSQL = implode(',', $SQL);
                G::$DB->query(
                    "INSERT INTO 
                    artist_info_cache 
                    (
                        IMDBID, 
                        TMDBID, 
                        TMDBData, 
                        MainTMDBData, 
                        TMDBTime,
                        MainTMDBTime
                        ) VALUES " . $FinalSQL . " 
                        ON DUPLICATE KEY UPDATE 
                        TMDBID=VALUES(TMDBID), 
                        TMDBData=VALUES(TMDBData), 
                        MainTMDBData=VALUES(MainTMDBData), 
                        TMDBTime=VALUES(TMDBTime),
                        MainTMDBTime=VALUES(MainTMDBTime)"
                );
                G::$DB->query("SELECT IMDBID, TMDBData, MainTMDBData, TMDBID
                FROM artist_info_cache
                WHERE IMDBID in ('" . implode("','", $NotFoundID) . "')");
                $Data = array_merge($Data, G::$DB->to_array('IMDBID', MYSQLI_ASSOC));
            }
        }
        $Ret = [];
        $IMDBImage = [];
        if ($MovieIMDBID) {
            $actors = self::get_imdb_actor_data($MovieIMDBID);
            $all = [$actors->Directors, $actors->Writters, $actors->Casts, $actors->Producers, $actors->Composers, $actors->Cinematographers];
            foreach ($all as $index => $actor) {
                foreach ($actor as $key => $value) {
                    $IMDBImage['nm' . $value->imdb] = $value->photo;
                }
            }
        }
        $imageCount = 1;
        foreach ($Data as $key => $value) {
            $TMDBInfo = json_decode($value['TMDBData']);
            $MainTMDBInfo = json_decode($value['MainTMDBData']);
            $Info = self::get_default_artist($value['IMDBID']);
            if ($TMDBInfo) {
                if ($TMDBInfo->profile_path) {
                    $Info['Image'] = self::upload_artist_avatar($value['IMDBID'], "https://image.tmdb.org/t/p/w500" . $TMDBInfo->profile_path);
                }
                $Info['Description'] = $TMDBInfo->biography;
                if ($TMDBInfo->place_of_birth) {
                    $Info['PlaceOfBirth'] = $TMDBInfo->place_of_birth;
                }
                if ($TMDBInfo->birthday) {
                    $Info['Birthday'] = $TMDBInfo->birthday;
                }
            }
            if ($MainTMDBInfo) {
                $Info['MainDescription'] = $MainTMDBInfo->biography;
            }
            if ($Limit && $imageCount > $Limit) {
            } else {
                if (empty($Info['Image']) && $IMDBImage[$value['IMDBID']]) {
                    $Info['Image'] = self::upload_artist_avatar($value['IMDBID'], $IMDBImage[$value['IMDBID']]);
                }
                if (empty($Info['Image']) && $IMDBImage[$value['IMDBID']]) {
                    $Info['Image'] = $IMDBImage[$value['IMDBID']];
                }
            }

            $Ret[$value['IMDBID']] = $Info;
            $imageCount++;
        }
        return $Ret;
    }

    public static function get_imdb_actor_data($IMDBID, $Refresh = false) {
        G::$DB->query("SELECT IMDBActorData
            FROM movie_info_cache
            WHERE IMDBID='$IMDBID'");
        if (G::$DB->has_results()) {
            list($IMDBActorData) = G::$DB->next_record(MYSQLI_NUM, false);
        }
        if (!$IMDBActorData || $Refresh) {
            $IMDBResult = self::get_imdb_data($IMDBID);
            $IMDBActorInfo = [];
            $IMDBActorInfo['Directors'] = $IMDBResult->director();
            $IMDBActorInfo['Writters'] = $IMDBResult->writing();
            $IMDBActorInfo['Casts'] = $IMDBResult->cast();
            $IMDBActorInfo['Producers'] = $IMDBResult->producer();
            $IMDBActorInfo['Composers'] = $IMDBResult->composer();
            $IMDBActorInfo['Cinematographers'] = $IMDBResult->cinematographer();
            $IMDBActorData = json_encode($IMDBActorInfo, JSON_UNESCAPED_UNICODE);
            G::$DB->query("INSERT INTO movie_info_cache (IMDBID, IMDBActorData, IMDBActorTime) VALUES('$IMDBID', '" . db_string($IMDBActorData) . "', '" . Time::sqlTime() . "')  ON DUPLICATE KEY UPDATE IMDBActorData=VALUES(IMDBActorData), IMDBActorTime=VALUES(IMDBActorTime)");
            G::$DB->query("SELECT IMDBActorData
            FROM movie_info_cache
            WHERE IMDBID='$IMDBID'");
            if (G::$DB->has_results()) {
                list($IMDBActorData) = G::$DB->next_record(MYSQLI_NUM, false);
            }
        }
        return json_decode($IMDBActorData);
    }

    public static function get_imdb_data($IMDBID) {
        $IMDBConfig = new \Imdb\Config();
        $IMDBConfig->language = 'en-US';
        $IMDBConfig->cachedir = CONFIG['SERVER_ROOT'] . '/.cache/imdbphp';
        $IMDBResult = new \Imdb\Title($IMDBID, $IMDBConfig);
        return $IMDBResult;
    }

    public static function get_movie_fill_info_by_group_id($GroupID) {
        $Group = Torrents::get_group($GroupID);
        $Artists = Artists::get_artist($GroupID);
        $Info = [];
        $Info['Title'] = $Group['Name'];
        $Info['Year'] = $Group['Year'];
        $Info['Poster'] = $Group['WikiImage'];
        $Info['SubTitle'] = $Group['SubName'];
        foreach ($Artists[Artists::Director] as $Artist) {
            $Info['Directors'][$Artist['IMDBID']] = $Artist['Name'];
            $Info['SubName'][$Artist['Name']] = $Artist['SubName'];
        }
        $Info['IMDBID'] = $Group['IMDBID'];
        $Info['Type'] = 'Movie';
        $Info['Genre'] = implode(',', explode('|', $Group['TorrentTags']));
        return $Info;
    }

    public static function get_main_tmdb_info($IMDBID, $Refresh = false) {
        G::$DB->query("SELECT 
            MainTMDBData
        FROM movie_info_cache
        WHERE IMDBID='$IMDBID'");
        if (G::$DB->has_results()) {
            list($MainTMDBData) = G::$DB->next_record(MYSQLI_NUM, false);
        }
        $key = CONFIG['TMDB_API_KEY'];
        if (!empty($key) && (empty($MainTMDBData) || $Refresh)) {
            $curl = new Curl();
            $curl->get('https://api.themoviedb.org/3/find/' . $IMDBID, ['api_key' => $key, 'language' => self::tmdb_lang(Lang::MAIN_LANG), 'external_source' => 'imdb_id']);
            if ($curl->error) {
            } else {
                $OMDBData = json_encode($curl->response, JSON_UNESCAPED_UNICODE);
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, MainTMDBData, MainTMDBTime) VALUES('$IMDBID', '" . db_string($MainTMDBData) . "', '" . Time::sqlTime() . "')  ON DUPLICATE KEY UPDATE MAINTMDBData='" . db_string($MainTMDBData) . "', MAINTMDBTime='" . Time::sqlTime() . "'");
                // query again? db_string escape?
                G::$DB->query("SELECT MainTMDBData 
                FROM movie_info_cache
                WHERE IMDBID='$IMDBID'");
                if (G::$DB->has_results()) {
                    list($MainTMDBData) = G::$DB->next_record(MYSQLI_NUM, false);
                }
            }
        }
        if (!empty($MainTMDBData)) {
            return json_decode($MainTMDBData);
        }
        return null;
    }

    public static function get_movie_fill_info($IMDBID, $Refresh = false) {
        $OMDBData = null;
        $TMDBData = null;
        $DoubanActorData = null;
        $DoubanData = null;
        $IMDBActorData = null;
        $InnerActorData = null;
        G::$DB->query(
            "SELECT 
                OMDBData, 
                TMDBData, 
                MainTMDBData,
                DoubanActorData, 
                IMDBActorData, 
                DoubanData
            FROM movie_info_cache
            WHERE IMDBID='$IMDBID'"
        );
        if (G::$DB->has_results()) {
            list($OMDBData, $TMDBData, $MainTMDBData, $DoubanActorData, $IMDBActorData, $DoubanData) = G::$DB->next_record(MYSQLI_NUM, false);
        }

        $IMDBResult = self::get_imdb_data($IMDBID);
        if (empty($IMDBActorData) || $Refresh) {
            $IMDBActorInfo = [];
            $IMDBActorInfo['Directors'] = $IMDBResult->director();
            $IMDBActorInfo['Writters'] = $IMDBResult->writing();
            $IMDBActorInfo['Casts'] = $IMDBResult->cast();
            $IMDBActorInfo['Producers'] = $IMDBResult->producer();
            $IMDBActorInfo['Composers'] = $IMDBResult->composer();
            $IMDBActorInfo['Cinematographers'] = $IMDBResult->cinematographer();
            $IMDBActorData = json_encode($IMDBActorInfo, JSON_UNESCAPED_UNICODE);
            G::$DB->query("INSERT INTO 
                            movie_info_cache 
                        (
                            IMDBID, 
                            IMDBActorData, 
                            IMDBActorTime
                        ) VALUES(
                            '$IMDBID', 
                            '" . db_string($IMDBActorData) . "', 
                            '" . Time::sqlTime() . "'
                            )  
                            ON DUPLICATE KEY UPDATE 
                            IMDBActorData=VALUES(IMDBActorData), 
                            IMDBActorTime=VALUES(IMDBActorTime)");
            G::$DB->query(
                "SELECT 
                    IMDBActorData
                FROM movie_info_cache
                WHERE IMDBID='$IMDBID'"
            );
            if (G::$DB->has_results()) {
                list($IMDBActorData) = G::$DB->next_record(MYSQLI_NUM, false);
            }
        }

        $omdb_key = CONFIG['OMDB_API_KEY'];
        $key = CONFIG['TMDB_API_KEY'];
        $douban_api_url = CONFIG['DOUBAN_API_URL'];
        $Info = array();
        $multi_curl = new MultiCurl();
        $multi_curl->setConnectTimeout(10);
        if (!empty($omdb_key) && (empty($OMDBData) || $Refresh)) {
            $omdb = $multi_curl->addGet('http://www.omdbapi.com', ['apikey' => $omdb_key, 'i' => $IMDBID]);
            $omdb->myTag = 'omdb';
        }
        if (!empty($key) && (empty($TMDBData) || $Refresh)) {
            $tmdb = $multi_curl->addGet('https://api.themoviedb.org/3/find/' . $IMDBID, ['api_key' => $key, 'language' => self::tmdb_lang(Lang::SUB_LANG), 'external_source' => 'imdb_id']);
            $tmdb->myTag = 'tmdb';
        }
        if (!empty($key) && (empty($MainTMDBData) || $Refresh)) {
            $tmdb = $multi_curl->addGet('https://api.themoviedb.org/3/find/' . $IMDBID, ['api_key' => $key, 'language' => self::tmdb_lang(Lang::MAIN_LANG), 'external_source' => 'imdb_id']);
            $tmdb->myTag = 'main-tmdb';
        }
        if (!empty($douban_api_url) && (empty($DoubanActorData) || $Refresh)) {
            $douban = $multi_curl->addGet(CONFIG['DOUBAN_API_URL'] . '/actors?imdb-id=' . $IMDBID);
            $douban->myTag = 'douban-actor';
        }
        if (!empty($douban_api_url) && (empty($DoubanData) || $Refresh)) {
            $douban = $multi_curl->addGet(CONFIG['DOUBAN_API_URL'] . '/search?imdb-id=' . $IMDBID);
            $douban->myTag = 'douban';
        }


        $multi_curl->success(function ($instance) use (&$OMDBData, &$TMDBData, &$MainTMDBData, &$DoubanActorData, $IMDBID) {
            if ($instance->myTag == 'omdb') {
                $OMDBData = json_encode($instance->response, JSON_UNESCAPED_UNICODE);
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, OMDBData, OMDBTime) VALUES('$IMDBID', '" . db_string($OMDBData) . "', '" . Time::sqlTime() . "')  ON DUPLICATE KEY UPDATE OMDBData=VALUES(OMDBData), OMDBTime=VALUES(OMDBTime)");
            } else if ($instance->myTag == 'tmdb') {
                $TMDBData = json_encode($instance->response, JSON_UNESCAPED_UNICODE);
                if (count($instance->response->movie_results) > 0) {
                    $TMDBID = $instance->response->movie_results[0]->id;
                } else {
                    $TMDBID = 'null';
                }
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, TMDBData, TMDBTime, TMDBID) VALUES('$IMDBID', '" . db_string($TMDBData) . "', '" . Time::sqlTime() . "', $TMDBID)  ON DUPLICATE KEY UPDATE TMDBData=VALUES(TMDBData), TMDBTime=VALUES(TMDBTime), TMDBID=VALUES(TMDBID)");
                G::$DB->query(
                    "SELECT 
                        TMDBData
                    FROM movie_info_cache
                    WHERE IMDBID='$IMDBID'"
                );
                if (G::$DB->has_results()) {
                    list($TMDBData) = G::$DB->next_record(MYSQLI_NUM, false);
                }
            } else if ($instance->myTag == 'main-tmdb') {
                $MainTMDBData = json_encode($instance->response, JSON_UNESCAPED_UNICODE);
                if (count($instance->response->movie_results) > 0) {
                    $TMDBID = $instance->response->movie_results[0]->id;
                } else {
                    $TMDBID = 'null';
                }
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, MainTMDBData, MainTMDBTime, TMDBID) VALUES('$IMDBID', '" . db_string($MainTMDBData) . "', '" . Time::sqlTime() . "', $TMDBID)  ON DUPLICATE KEY UPDATE MainTMDBData=VALUES(MainTMDBData), MainTMDBTime=VALUES(MainTMDBTime), TMDBID=VALUES(TMDBID)");
                G::$DB->query(
                    "SELECT 
                       MainTMDBData 
                    FROM movie_info_cache
                    WHERE IMDBID='$IMDBID'"
                );
                if (G::$DB->has_results()) {
                    list($MainTMDBData) = G::$DB->next_record(MYSQLI_NUM, false);
                }
            } else if ($instance->myTag == 'douban-actor') {
                $DoubanActorData = json_encode($instance->response, JSON_UNESCAPED_UNICODE);
                if ($instance->response->data->douban) {
                    $DoubanID = $instance->response->data->douban->id;
                } else {
                    $DoubanID = 'null';
                }
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, DoubanActorData, DoubanActorTime, DoubanID) VALUES('$IMDBID', '" . db_string($DoubanActorData) . "', '" . Time::sqlTime() . "', $DoubanID)  ON DUPLICATE KEY UPDATE DoubanActorData=VALUES(DoubanActorData), DoubanActorTime=VALUES(DoubanActorTime), DoubanID=VALUES(DoubanID)");
                G::$DB->query(
                    "SELECT 
                       DoubanActorData 
                    FROM movie_info_cache
                    WHERE IMDBID='$IMDBID'"
                );
                if (G::$DB->has_results()) {
                    list($DoubanActorData) = G::$DB->next_record(MYSQLI_NUM, false);
                }
            } else if ($instance->myTag == 'douban') {
                $DoubanData = json_encode($instance->response, JSON_UNESCAPED_UNICODE);
                if ($instance->response->data->douban) {
                    $DoubanID = $instance->response->data->douban->id;
                } else {
                    $DoubanID = 'null';
                }
                G::$DB->query("INSERT INTO movie_info_cache (IMDBID, DoubanData, DoubanTime, DoubanID) VALUES('$IMDBID', '" . db_string($DoubanData) . "', '" . Time::sqlTime() . "', $DoubanID)  ON DUPLICATE KEY UPDATE DoubanData=VALUES(DoubanData), DoubanTime=VALUES(DoubanTime), DoubanID=VALUES(DoubanID)");
                G::$DB->query(
                    "SELECT 
                       DoubanData 
                    FROM movie_info_cache
                    WHERE IMDBID='$IMDBID'"
                );
                if (G::$DB->has_results()) {
                    list($DoubanData) = G::$DB->next_record(MYSQLI_NUM, false);
                }
            }
        });
        $multi_curl->start();

        $Info['Title'] = html_entity_decode($IMDBResult->title(), ENT_QUOTES);
        if (count($IMDBResult->plot()) > 0) {
            $IMDBPlot = html_entity_decode($IMDBResult->plot()[0], ENT_QUOTES);
            if ($IMDBPlot != 'N/A') {
                $Info['MainPlot'] = $IMDBPlot;
            }
        }
        $Info['Year'] = $IMDBResult->year();
        $Info['Genre'] = strtolower(implode(',', array_values(Tags::get_sub_name($IMDBResult->genres()))));
        $Info['Type'] = $IMDBResult->movieType();

        if ($OMDBData) {
            $OMDBResult = json_decode($OMDBData);
            if (empty($Info['Title'])) {
                $Info['Title'] = $OMDBResult->Title;
            }
            if (empty($Info['MainPlot']) && $OMDBResult->Plot != 'N/A') {
                $Info['MainPlot'] = $OMDBResult->Plot;
            }
            if (empty($Info['Year'])) {
                $Info['Year'] = $OMDBResult->Year;
            }
            if (empty($Info['Genre'])) {
                $Info['Genre'] = $OMDBResult->Genre;
            }
            if (empty($Info['Type'])) {
                $Info['Type'] = $OMDBResult->Type;
            }
            // TODO by qwerty 临时使用omdb的封面，需要拿更好的。
            $Info['Poster'] = self::upload_movie_poster($IMDBID, $OMDBResult->Poster);
            foreach ($OMDBResult->Ratings as $Num => $value) {
                if ($value->Source == "Rotten Tomatoes") {
                    $Info['RTRating'] = $value->Value;
                    break;
                }
            }
        }

        if ($TMDBData) {
            $TMDBResult = json_decode($TMDBData);
            if (count($TMDBResult->movie_results) > 0) {
                $TMDBSimpleInfo = $TMDBResult->movie_results[0];
                $Info['SubTitle'] = $TMDBSimpleInfo->title;
                $Info['Plot'] = $TMDBSimpleInfo->overview;
            }
        }

        if ($MainTMDBData) {
            $MainTMDBResult = json_decode($MainTMDBData);
            if (count($MainTMDBResult->movie_results) > 0) {
                $MainTMDBSimpleInfo = $MainTMDBResult->movie_results[0];
                $Info['Title'] = $MainTMDBSimpleInfo->title;
                if (empty($Info['MainPlot']) && $MainTMDBSimpleInfo->overview != 'N/A') {
                    $Info['MainPlot'] = $MainTMDBSimpleInfo->overview;
                }
            }
        }

        if ($DoubanData) {
            $DoubanResult = json_decode($DoubanData);
            if ($DoubanResult->data->douban->description) {
                $Info['Plot'] = $DoubanResult->data->douban->description;
            }
        }

        $ArtistIMDBIDs = [];
        if ($IMDBActorData) {
            $IMDBActor = json_decode($IMDBActorData);
            $Directors = $IMDBActor->Directors;
            $Writters = $IMDBActor->Writters;
            $Casts = $IMDBActor->Casts;
            $Producer = $IMDBActor->Producers;
            $Composer = $IMDBActor->Composers;
            $Cinematographer = $IMDBActor->Cinematographers;
            foreach ($Directors as $key => $value) {
                $ArtistIMDBIDs[] = $value->imdb;
            }
            foreach ($Writters as $key => $value) {
                $ArtistIMDBIDs[] = $value->imdb;
            }
            foreach ($Casts as $key => $value) {
                $ArtistIMDBIDs[] = $value->imdb;
            }
            foreach ($Producer as $key => $value) {
                $ArtistIMDBIDs[] = $value->imdb;
            }
            foreach ($Composer as $key => $value) {
                $ArtistIMDBIDs[] = $value->imdb;
            }
            foreach ($Cinematographer as $key => $value) {
                $ArtistIMDBIDs[] = $value->imdb;
            }
        }
        if (count($ArtistIMDBIDs) > 0) {
            $InnerActorData = Artists::multi_find_artist($ArtistIMDBIDs);
        }
        if (isset($Directors)) {
            foreach ($Directors as $key => $value) {
                $Info['Directors']["nm" . $value->imdb] = $value->name;
            }
        }

        if (isset($Writters)) {
            $Info['Writters'] = array();
            foreach ($Writters as $key => $value) {
                $Info['Writters']["nm" . $value->imdb] = $value->name;
            }
        }

        if (isset($Casts)) {
            $Info['Casts'] = array();
            $Info['RestCasts'] = array();
            foreach ($Casts as $key => $value) {
                if ($value->credited) {
                    $Info['Casts']["nm" . $value->imdb] = $value->name;
                } else {
                    $Info['RestCasts']["nm" . $value->imdb] = $value->name;
                }
            }
        }
        if (isset($Producer)) {
            $Info['Producers'] = array();
            foreach ($Producer as $key => $value) {
                $Info['Producers']["nm" . $value->imdb] = $value->name;
            }
        }
        if (isset($Composer)) {
            $Info['Composers'] = array();
            foreach ($Composer as $key => $value) {
                $Info['Composers']["nm" . $value->imdb] = $value->name;
            }
        }

        if (isset($Cinematographers)) {
            $Info['Cinematographers'] = array();
            foreach ($Cinematographer as $key => $value) {
                $Info['Cinematographers']["nm" . $value->imdb] = $value->name;
            }
        }

        $Info['SubName'] = array();
        if (isset($InnerActorData)) {
            foreach ($InnerActorData as $IMDBID => $Data) {
                $Info['SubName'][$Data['Name']] = $Data['SubName'];
            }
        }
        if ($DoubanActorData) {
            $DoubanResult = json_decode($DoubanActorData);
            if ($DoubanResult->data) {
                if ($DoubanResult->data->douban) {
                    $Info['DoubanID'] = $DoubanResult->data->douban->id;
                }
                foreach ($DoubanResult->data->actors as $name) {
                    if (empty($Info['SubName'][$name->nameEn])) {
                        $Info['SubName'][$name->nameEn] = $name->name;
                    }
                }
            }
        }
        return $Info;
    }
    public static function get_main_plot($IMDBID) {
        $IMDBResult = Movie::get_imdb_data($IMDBID);
        if (count($IMDBResult->plot()) > 0) {
            $IMDBPlot = html_entity_decode($IMDBResult->plot()[0], ENT_QUOTES);
            if ($IMDBPlot != 'N/A') {
                return $IMDBPlot;
            }
        }
        $OMDBResult = Movie::get_omdb_data($IMDBID);
        if (!empty($OMDBResult->Plot && $OMDBResult->Plot != 'N/A')) {
            return $OMDBResult->Plot;
        }
        $MainTMDBResult = Movie::get_main_tmdb_info($IMDBID);
        if (count($MainTMDBResult->movie_results) > 0) {
            $MainTMDBSimpleInfo = $MainTMDBResult->movie_results[0];
            if ($MainTMDBSimpleInfo->overview != 'N/A')
                return  $MainTMDBSimpleInfo->overview;
        }
        return '';
    }

    public static function upload_movie_poster($IMDBID, $url) {
        return self::upload_image(CONFIG['IMAGE_PATH_PREFIX'] . '/site/movie/imdb/' . $IMDBID . '-' . uniqid() . '.jpg', $url);
    }
    public static function upload_artist_avatar($IMDBID, $url) {
        return self::upload_image(CONFIG['IMAGE_PATH_PREFIX'] . "/site/artist/imdb/" . $IMDBID . '-' . uniqid() . ".jpg", $url);
    }

    private static function upload_image($key, $url) {
        try {
            $Addr = ImageTools::fetch_upload($key, $url);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return "";
        }
        return $Addr;
    }
    private static function tmdb_lang($Lang) {
        switch ($Lang) {
            case Lang::CHS:
                return "zh-CN";
            case Lang::EN:
                return "en-US";
        }
        return "en-US";
    }
}
