<?php

namespace Gazelle\API;

class MovieInfo extends AbstractAPI {
    public function run() {
        $response = array();
        if (isset($_GET['imdbid'])) {
            $IMDBID = $_GET['imdbid'];

            if (preg_match('/^tt\d+$/', $IMDBID)) {
                $this->db->query("select ID from torrents_group where IMDBID='$IMDBID'");
                list($GroupID) = $this->db->next_record();
                if ($GroupID) {
                    $response["status"] = "success";
                    $response["message"] = array("Dupe" => true, "GroupID" => $GroupID);
                } else {
                    try {
                        $Ret = \MOVIE::get_movie_fill_info($IMDBID, false);
                        if (!$Ret) {
                            $response["status"] = "error";
                            $response["message"] = "Internal Error";
                        } else {
                            $response["status"] = "success";
                            $response["response"] = $Ret;
                        }
                    } catch (\Exception $e) {
                        $response["status"] = "error";
                        $response["message"] = $e->getMessage();
                    }
                }
            } else {
                $response["message"] = "Invalid IMDb ID";
                $response["status"] = "error";
            }
        } else {
            $response["message"] = "No imdb id provided";
            $response["status"] = "error";
        }
        return $response;
    }
}
