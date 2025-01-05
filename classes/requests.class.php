<?
class Requests {
    /**
     * Update the sphinx requests delta table for a request.
     *
     * @param $RequestID
     */
    public static function update_sphinx_requests($RequestID) {
        $QueryID = G::$DB->get_query_id();

        G::$DB->query("
			SELECT REPLACE(t.Name, '.', '_')
			FROM tags AS t
				JOIN requests_tags AS rt ON t.ID = rt.TagID
			WHERE rt.RequestID = $RequestID");
        $TagList = G::$DB->collect(0, false);
        $TagList = db_string(implode(' ', $TagList));

        G::$DB->query("
			REPLACE INTO sphinx_requests_delta (
				ID, UserID, TimeAdded, LastVote, CategoryID, Title, TagList,
				Year, ReleaseType, CodecList, SourceList, ContainerList, ResolutionList, FillerID, TorrentID,
				TimeFilled, Visible, Votes, Bounty, RequestType)
			SELECT
				ID, r.UserID, UNIX_TIMESTAMP(TimeAdded) AS TimeAdded,
				UNIX_TIMESTAMP(LastVote) AS LastVote, CategoryID, Title, '$TagList',
				Year, ReleaseType, CodecList, SourceList, ContainerList, ResolutionList, FillerID, TorrentID,
				UNIX_TIMESTAMP(TimeFilled) AS TimeFilled, Visible,
				COUNT(rv.UserID) AS Votes, SUM(rv.Bounty) >> 20 AS Bounty, RequestType
			FROM requests AS r
				LEFT JOIN requests_votes AS rv ON rv.RequestID = r.ID
			WHERE ID = $RequestID
			GROUP BY r.ID");
        G::$DB->query("
			UPDATE sphinx_requests_delta
			SET ArtistList = (
					SELECT GROUP_CONCAT(aa.Name SEPARATOR ' ')
					FROM requests_artists AS ra
						JOIN artists_alias AS aa ON aa.ArtistID = ra.ArtistID
					WHERE ra.RequestID = $RequestID
					GROUP BY NULL
					)
			WHERE ID = $RequestID");
        G::$DB->set_query_id($QueryID);

        G::$Cache->delete_value("request_$RequestID");
    }



    /**
     * Function to get data from an array of $RequestIDs. Order of keys doesn't matter (let's keep it that way).
     *
     * @param array $RequestIDs
     * @param boolean $Return if set to false, data won't be returned (ie. if we just want to prime the cache.)
     * @return The array of requests.
     * Format: array(RequestID => Associative array)
     * To see what's exactly inside each associate array, peek inside the function. It won't bite.
     */
    //
    //In places where the output from this is merged with sphinx filters, it will be in a different order.
    public static function get_requests($RequestIDs, $Return = true) {
        $Found = $NotFound = array_fill_keys($RequestIDs, false);
        // Try to fetch the requests from the cache first.
        foreach ($RequestIDs as $i => $RequestID) {
            if (!is_number($RequestID)) {
                unset($RequestIDs[$i], $Found[$RequestID], $NotFound[$RequestID]);
                continue;
            }
            $Data = G::$Cache->get_value("request_$RequestID");
            if (!empty($Data)) {
                unset($NotFound[$RequestID]);
                $Found[$RequestID] = $Data;
            }
        }
        // Make sure there's something in $RequestIDs, otherwise the SQL will break
        if (count($RequestIDs) === 0) {
            return array();
        }
        $IDs = implode(',', array_keys($NotFound));

        /*
            Don't change without ensuring you change everything else that uses get_requests()
        */

        if (count($NotFound) > 0) {
            $QueryID = G::$DB->get_query_id();

            G::$DB->query("
				SELECT
					ID,
					UserID,
					TimeAdded,
					LastVote,
					CategoryID,
					Title,
                    Title as Name,
                    Subtitle as SubName,
                    Subtitle,
					Year,
					Image,
					Description,
					ReleaseType,
					CodecList,
					ResolutionList,
					ContainerList,
					SourceList,
                    IMDBID,
                    SourceTorrent,
                    PurchasableAt,
					FillerID,
					TorrentID,
					TimeFilled,
					GroupID,
                    RequestType
				FROM requests
				WHERE ID IN ($IDs)
				ORDER BY ID");
            $Requests = G::$DB->to_array(false, MYSQLI_ASSOC, false);
            $Tags = self::get_tags(G::$DB->collect('ID', false));
            foreach ($Requests as $Request) {
                unset($NotFound[$Request['ID']]);
                $Request['Tags'] = isset($Tags[$Request['ID']]) ? $Tags[$Request['ID']] : array();
                $Found[$Request['ID']] = $Request;
                G::$Cache->cache_value('request_' . $Request['ID'], $Request, 0);
            }
            G::$DB->set_query_id($QueryID);

            // Orphan requests. There shouldn't ever be any
            if (count($NotFound) > 0) {
                foreach (array_keys($NotFound) as $GroupID) {
                    unset($Found[$GroupID]);
                }
            }
        }

        if ($Return) { // If we're interested in the data, and not just caching it
            return $Found;
        }
    }

    /**
     * Return a single request. Wrapper for get_requests
     *
     * @param int $RequestID
     * @return request array or false if request doesn't exist. See get_requests for a description of the format
     */
    public static function get_request($RequestID) {
        $Request = self::get_requests(array($RequestID));
        if (isset($Request[$RequestID])) {
            return $Request[$RequestID];
        }
        return false;
    }

    public static function get_artists($RequestID) {
        $Artists = G::$Cache->get_value("request_artists_$RequestID");
        if (is_array($Artists)) {
            $Results = $Artists;
        } else {
            $Results = array();
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT
					ra.ArtistID,
					ag.Name,
					ra.Importance,
                    ag.SubName,
                    ag.IMDBID
				FROM requests_artists AS ra
                    JOIN artists_group AS ag ON ag.ArtistID = ra.ArtistID
				WHERE ra.RequestID = $RequestID
				ORDER BY ra.Importance ASC, ag.Name ASC;");
            $ArtistRaw = G::$DB->to_array();
            G::$DB->set_query_id($QueryID);
            foreach ($ArtistRaw as $ArtistRow) {
                list($ArtistID, $ArtistName, $ArtistImportance, $ArtistSubName, $ArtistIMDBID) = $ArtistRow;
                $Results[$ArtistImportance][] = array('ArtistID' => $ArtistID, 'Name' => $ArtistName, 'IMDBID' => $ArtistIMDBID, 'SubName' => $ArtistSubName);
            }
            G::$Cache->cache_value("request_artists_$RequestID", $Results);
        }
        return $Results;
    }

    public static function get_tags($RequestIDs) {
        if (empty($RequestIDs)) {
            return array();
        }
        if (is_array($RequestIDs)) {
            $RequestIDs = implode(',', $RequestIDs);
        }
        $QueryID = G::$DB->get_query_id();
        G::$DB->query("
			SELECT
				rt.RequestID,
				rt.TagID,
				t.Name
			FROM requests_tags AS rt
				JOIN tags AS t ON rt.TagID = t.ID
			WHERE rt.RequestID IN ($RequestIDs)
			ORDER BY rt.TagID ASC");
        $Tags = G::$DB->to_array(false, MYSQLI_NUM, false);
        G::$DB->set_query_id($QueryID);
        $Results = array();
        foreach ($Tags as $TagsRow) {
            list($RequestID, $TagID, $TagName) = $TagsRow;
            $Results[$RequestID][$TagID] = $TagName;
        }
        return $Results;
    }

    public static function get_votes_array($RequestID) {
        $RequestVotes = G::$Cache->get_value("request_votes_$RequestID");
        if (!is_array($RequestVotes)) {
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT
					rv.UserID,
					rv.Bounty,
					u.Username
				FROM requests_votes AS rv
					LEFT JOIN users_main AS u ON u.ID = rv.UserID
				WHERE rv.RequestID = $RequestID
				ORDER BY rv.Bounty DESC");
            if (!G::$DB->has_results()) {
                return array(
                    'TotalBounty' => 0,
                    'Voters' => array()
                );
            }
            $Votes = G::$DB->to_array();

            $RequestVotes = array();
            $RequestVotes['TotalBounty'] = array_sum(G::$DB->collect('Bounty'));

            foreach ($Votes as $Vote) {
                list($UserID, $Bounty, $Username) = $Vote;
                $VoteArray = array();
                $VotesArray[] = array('UserID' => $UserID, 'Username' => $Username, 'Bounty' => $Bounty);
            }

            $RequestVotes['Voters'] = $VotesArray;
            G::$Cache->cache_value("request_votes_$RequestID", $RequestVotes);
            G::$DB->set_query_id($QueryID);
        }
        return $RequestVotes;
    }

    public static function get_group_requests($GroupID) {
        if (empty($GroupID) || !is_number($GroupID)) {
            return array();
        }
        global $DB, $Cache;

        $Requests = $Cache->get_value("requests_group_$GroupID");
        if ($Requests === false) {
            $DB->query("
			SELECT ID
			FROM requests
			WHERE GroupID = $GroupID
				AND TimeFilled = '0000-00-00 00:00:00'");
            $Requests = $DB->collect('ID');
            $Cache->cache_value("requests_group_$GroupID", $Requests, 0);
        }
        return self::get_requests($Requests);
    }

    public static function get_torrent_request_id($TorrentID, $RequestType) {
        G::$DB->query(
            "SELECT
				    r.ID,
                    SourceTorrent
			FROM requests AS r
            JOIN torrents AS t ON t.ID =$TorrentID and t.GroupID = r.GroupID
			WHERE r.RequestType = $RequestType and r.FillerID = '0'"
        );

        $Requests = G::$DB->to_array('ID', MYSQLI_ASSOC, false);
        foreach ($Requests as $ID => $Request) {
            if (!preg_match('/' . TORRENT_REGEX . '/i', $Request['SourceTorrent'], $Matches)) {
                continue;
            }
            if ($Matches[2] == $TorrentID) {
                return $ID;
            }
        }
        return null;
    }

    public static function get_artist_requests($ArtistID) {
        $DB = G::$DB;
        $Cache = G::$Cache;
        $Requests = $Cache->get_value("artists_requests_$ArtistID");
        if (!is_array($Requests)) {
            $DB->query(
                "SELECT
				    r.ID
			FROM requests AS r
				LEFT JOIN requests_votes AS rv ON rv.RequestID = r.ID
				LEFT JOIN requests_artists AS ra ON r.ID = ra.RequestID
			WHERE ra.ArtistID = $ArtistID
				AND r.TorrentID = 0
			GROUP BY r.ID DESC"
            );
            $Requests = $DB->collect('ID');
            $Cache->cache_value("artists_requests_$ArtistID", $Requests);
        }
        return self::get_requests($Requests);
    }

    public static function delete_request($RequestID, $OperatorID = 0, $OperatorName, $Reason) {
        //Do we need to get artists?
        G::$DB->query(
            "SELECT
	        	UserID,
	        	Title,
	        	GroupID
	        FROM requests
	        WHERE ID = $RequestID"
        );
        list($UserID, $Title, $GroupID) = G::$DB->next_record();
        if ($OperatorID != 0 && $OperatorID != $UserID && !check_perms('site_moderate_requests')) {
            error(403);
        }
        $FullName = $Title;
        // Delete request, votes and tags
        G::$DB->query("DELETE FROM requests WHERE ID = '$RequestID'");
        G::$DB->query("DELETE FROM requests_votes WHERE RequestID = '$RequestID'");
        G::$DB->query("DELETE FROM requests_tags WHERE RequestID = '$RequestID'");
        Comments::delete_page('requests', $RequestID);

        G::$DB->query(
            "SELECT ArtistID
	            FROM requests_artists
	            WHERE RequestID = $RequestID"
        );
        $RequestArtists = G::$DB->to_array();
        foreach ($RequestArtists as $RequestArtist) {
            G::$Cache->delete_value("artists_requests_$RequestArtist");
        }
        G::$DB->query(
            "DELETE FROM requests_artists
	            WHERE RequestID = '$RequestID'"
        );
        G::$Cache->delete_value("request_artists_$RequestID");

        G::$DB->query(
            "REPLACE INTO sphinx_requests_delta
	        	(ID, TimeAdded)
	        VALUES
		($RequestID, Unix_TIMESTAMP())"
        );

        if ($UserID != $OperatorID) {
            Misc::send_pm_with_tpl($UserID, 'request_deleted', [
                'FullName' => $FullName,
                'LoggedUserID' => $OperatorID,
                'LoggedUserUsername' => $OperatorName,
                'Reason' => $Reason,
            ]);
        }

        Misc::write_log("Request $RequestID ($FullName) was deleted by user " . $OperatorID . ' (' . $OperatorName . ') for the reason: ' . $Reason);

        G::$Cache->delete_value("request_$RequestID");
        G::$Cache->delete_value("request_votes_$RequestID");
        if ($GroupID) {
            G::$Cache->delete_value("requests_group_$GroupID");
        }
    }
}
