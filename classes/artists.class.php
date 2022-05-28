<?
class Artists {

    public static function get_first_directors($Artists) {
        if (empty($Artists[1])) {
            return null;
        }
        return $Artists[1][0];
    }
    /**
     * Given an array of GroupIDs, return their associated artists.
     *
     * @param array $GroupIDs
     * @return an array of the following form:
     *  GroupID => {
     *      [ArtistType] => {
     *          id, name, aliasid
     *      }
     *  }
     * ArtistType is an int. It can be:
     * 1 => Main artist
     * 2 => Guest artist
     * 4 => Composer
     * 5 => Conductor
     * 6 => DJ
     */
    public static function get_artists($GroupIDs) {
        $Results = array();
        $DBs = array();
        foreach ($GroupIDs as $GroupID) {
            if (!is_number($GroupID)) {
                continue;
            }
            $Artists = G::$Cache->get_value('groups_artists_' . $GroupID);
            if (is_array($Artists)) {
                $Results[$GroupID] = $Artists;
            } else {
                $DBs[] = $GroupID;
            }
        }
        if (count($DBs) > 0) {
            $IDs = implode(',', $DBs);
            if (empty($IDs)) {
                $IDs = "null";
            }
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT ta.GroupID,
					ta.ArtistID,
					aa.Name,
					ta.Importance,
					ta.AliasID,
					wa.Image,
                    wa.ChineseName,
                    wa.IMDBID
				FROM torrents_artists AS ta
					JOIN artists_alias AS aa ON ta.AliasID = aa.AliasID
				LEFT JOIN artists_group as ag ON ag.ArtistID = ta.ArtistID
				LEFT JOIN wiki_artists as wa ON ag.RevisionID = wa.RevisionID
				WHERE ta.GroupID IN ($IDs)
				ORDER BY ta.GroupID ASC,
					ta.Importance ASC,
					ta.Order ASC,
					aa.Name ASC;");
            while (list($GroupID, $ArtistID, $ArtistName, $ArtistImportance, $AliasID, $Image, $CName, $IMDBID) = G::$DB->next_record(MYSQLI_BOTH, false)) {
                $Results[$GroupID][$ArtistImportance][] = array('id' => $ArtistID, 'name' => $ArtistName, 'aliasid' => $AliasID, 'image' => $Image, 'cname' => $CName, 'imdbid' => $IMDBID);
                $New[$GroupID][$ArtistImportance][] = array('id' => $ArtistID, 'name' => $ArtistName, 'aliasid' => $AliasID, 'image' => $Image, 'cname' => $CName, 'imdbid' => $IMDBID);
            }
            G::$DB->set_query_id($QueryID);
            foreach ($DBs as $GroupID) {
                if (isset($New[$GroupID])) {
                    G::$Cache->cache_value('groups_artists_' . $GroupID, $New[$GroupID], 86400);
                } else {
                    G::$Cache->cache_value('groups_artists_' . $GroupID, array(), 86400);
                }
            }
            $Missing = array_diff($GroupIDs, array_keys($Results));
            if (!empty($Missing)) {
                $Results += array_fill_keys($Missing, array());
            }
        }
        return $Results;
    }

    public static function get_artist_by_id($ArtistID) {
        G::$DB->query("
	SELECT
        ArtistID as id,
		Name as name,
		Image,
		Body,
        ChineseName as cname,
        IMDBID
	FROM artists_group AS a
		LEFT JOIN wiki_artists ON wiki_artists.RevisionID = a.RevisionID
	WHERE a.ArtistID = '$ArtistID'");

        if (!G::$DB->has_results()) {
            error(404);
        }
        return G::$DB->next_record(MYSQLI_ASSOC);
    }


    /**
     * Convenience function for get_artists, when you just need one group.
     *
     * @param int $GroupID
     * @return array - see get_artists
     */
    public static function get_artist($GroupID) {
        $Results = Artists::get_artists(array($GroupID));
        return $Results[$GroupID];
    }

    /**
     * Format an array of artists for display.
     * TODO: Revisit the logic of this, see if we can helper-function the copypasta.
     *
     * @param array Artists an array of the form output by get_artists
     * @param boolean $MakeLink if true, the artists will be links, if false, they will be text.
     * @param boolean $IncludeHyphen if true, appends " - " to the end.
     * @param $Escape if true, output will be escaped. Think carefully before setting it false.
     */
    public static function display_artists($Artists, $MakeLink = true, $IncludeHyphen = true, $Escape = true, $UserID = null) {
        if (!empty($Artists)) {
            $ampersand = ($Escape) ? ' &amp; ' : ' & ';
            $link = '';

            $Directors = isset($Artists[1]) ? $Artists[1] : array();

            if (count($Directors) == 0) {
                return '';
            }

            // Various Composers is not needed and is ugly and should die
            switch (count($Directors)) {
                case 0:
                    break;
                case 1:
                    $link = Artists::display_artist($Directors[0], $MakeLink, $Escape, $UserID);
                    break;
                case 2:
                    $link = Artists::display_artist($Directors[0], $MakeLink, $Escape, $UserID) . $ampersand . Artists::display_artist($Directors[1], $MakeLink, $Escape, $UserID);
                    break;
                default:
                    $link = 'Various Direcotrs';
            }
            return $link . ($IncludeHyphen ? ' - ' : '');
        } else {
            return '';
        }
    }


    /**
     * Formats a single artist name.
     *
     * @param array $Artist an array of the form ('id'=>ID, 'name'=>Name)
     * @param boolean $MakeLink If true, links to the artist page.
     * @param boolean $Escape If false and $MakeLink is false, returns the unescaped, unadorned artist name.
     * @return string Formatted artist name.
     */
    public static function display_artist($Artist, $MakeLink = true, $Escape = true, $UserID = null) {
        if (empty($UserID)) {
            global $LoggedUser;
            $UserID = $LoggedUser['ID'];
        }
        if (!empty($Artist['cname']) && Lang::getUserLang($UserID) == Lang::CHS) {
            $name = $Artist['cname'];
        } else {
            $name = $Artist['name'];
        }
        if ($MakeLink && !$Escape) {
            error('Invalid parameters to Artists::display_artist()');
        } elseif ($MakeLink) {
            return '<a href="artist.php?id=' . $Artist['id'] . '" dir="ltr">' . display_str($name) . '</a>';
        } elseif ($Escape) {
            return display_str($name);
        } else {
            return $name;
        }
    }

    /**
     * Deletes an artist and their requests, wiki, and tags.
     * Does NOT delete their torrents.
     *
     * @param int $ArtistID
     */
    public static function delete_artist($ArtistID) {
        $QueryID = G::$DB->get_query_id();
        G::$DB->query("
			SELECT Name
			FROM artists_group
			WHERE ArtistID = " . $ArtistID);
        list($Name) = G::$DB->next_record(MYSQLI_NUM, false);

        // Delete requests
        G::$DB->query("
			SELECT RequestID
			FROM requests_artists
			WHERE ArtistID = $ArtistID
				AND ArtistID != 0");
        $Requests = G::$DB->to_array();
        foreach ($Requests as $Request) {
            list($RequestID) = $Request;
            G::$DB->query('DELETE FROM requests WHERE ID=' . $RequestID);
            G::$DB->query('DELETE FROM requests_votes WHERE RequestID=' . $RequestID);
            G::$DB->query('DELETE FROM requests_tags WHERE RequestID=' . $RequestID);
            G::$DB->query('DELETE FROM requests_artists WHERE RequestID=' . $RequestID);
        }

        // Delete artist
        G::$DB->query('DELETE FROM artists_group WHERE ArtistID=' . $ArtistID);
        G::$DB->query('DELETE FROM artists_alias WHERE ArtistID=' . $ArtistID);
        G::$Cache->decrement('stats_artist_count');

        // Delete wiki revisions
        G::$DB->query('DELETE FROM wiki_artists WHERE PageID=' . $ArtistID);

        // Delete tags
        G::$DB->query('DELETE FROM artists_tags WHERE ArtistID=' . $ArtistID);

        // Delete artist comments, subscriptions and quote notifications
        Comments::delete_page('artist', $ArtistID);

        G::$Cache->delete_value('artist_' . $ArtistID);
        G::$Cache->delete_value('artist_groups_' . $ArtistID);
        // Record in log

        if (!empty(G::$LoggedUser['Username'])) {
            $Username = G::$LoggedUser['Username'];
        } else {
            $Username = 'System';
        }
        Misc::write_log("Artist $ArtistID ($Name) was deleted by $Username");
        G::$DB->set_query_id($QueryID);
    }


    /**
     * Remove LRM (left-right-marker) and trims, because people copypaste carelessly.
     * If we don't do this, we get seemingly duplicate artist names.
     * TODO: make stricter, e.g. on all whitespace characters or Unicode normalisation
     *
     * @param string $ArtistName
     */
    public static function normalise_artist_name($ArtistName) {
        // \u200e is &lrm;
        $ArtistName = trim($ArtistName);
        $ArtistName = preg_replace('/^(\xE2\x80\x8E)+/', '', $ArtistName);
        $ArtistName = preg_replace('/(\xE2\x80\x8E)+$/', '', $ArtistName);
        return trim(preg_replace('/ +/', ' ', $ArtistName));
    }
}
