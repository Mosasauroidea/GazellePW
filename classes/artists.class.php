<?

use Gazelle\Util\Time;

class Artists {
    const Director = 1;
    const Writter = 2;
    const Producer = 3;
    const Composer = 4;
    const Cinematographer = 5;
    const Actor = 6;
    const Importances = [
        self::Director,
        self::Writter,
        self::Producer,
        self::Composer,
        self::Cinematographer,
        self::Actor
    ];

    public static function update_artist_alias($OldName, $Name, $ArtistID) {
        G::$DB->prepared_query("SELECT AliasID FROM artists_alias WHERE ArtistID = $ArtistID and Name = '$OldName'");
        list($AliasID) = G::$DB->next_record(MYSQLI_NUM);
        if ($AliasID) {
            if (empty($Name)) {
                G::$DB->prepared_query("DELETE FROM artists_alias WHERE AliasID = $AliasID");
            } else {
                G::$DB->prepared_query("UPDATE artists_alias SET Name = '$Name' WHERE AliasID = $AliasID");
            }
        } else {
            G::$DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $ArtistID, $Name);
        }
    }

    public static function get_artist_name($Artist) {
        return Lang::choose_content($Artist['Name'], $Artist['SubName']);
    }
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
            G::$DB->query(
                "SELECT ta.GroupID,
					ta.ArtistID,
					ag.Name,
					ta.Importance,
					ag.Image,
                    ag.SubName,
                    ag.IMDBID
				FROM torrents_artists AS ta
				LEFT JOIN artists_group as ag ON ag.ArtistID = ta.ArtistID
				WHERE ta.GroupID IN ($IDs)
				ORDER BY ta.GroupID ASC,
					ta.Importance ASC,
					ta.Order ASC;"
            );
            while (list($GroupID, $ArtistID, $ArtistName, $ArtistImportance, $Image, $SubName, $IMDBID) = G::$DB->next_record(MYSQLI_BOTH)) {
                $Results[$GroupID][$ArtistImportance][] = array('ArtistID' => $ArtistID, 'Name' => $ArtistName, 'Image' => $Image, 'SubName' => $SubName, 'IMDBID' => $IMDBID);
                $New[$GroupID][$ArtistImportance][] = array('ArtistID' => $ArtistID, 'Name' => $ArtistName, 'Image' => $Image, 'SubName' => $SubName, 'IMDBID' => $IMDBID);
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
        G::$DB->query("SELECT
            ArtistID,
    		Name,
    		Image,
    		Body,
            MainBody,
            SubName,
            IMDBID
    	FROM artists_group AS a
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

    public static function multi_find_artist(array $IMDBIDs) {
        $IDs = implode(',', $IMDBIDs);
        G::$DB->query(
            "SELECT ag.Name,
					ag.SubName,
					ag.Image,
                    ag.IMDBID,
                    ag.PlaceOfBirth,
                    ag.Birthday,
                    ag.Body
				FROM artists_group AS ag
				WHERE IMDBID IN ($IDs)"
        );
        return G::$DB->to_array('IMDBID', MYSQLI_ASSOC);
    }

    public static function update_artist_info($IMDBIDs) {
        $FullArtistDetails = MOVIE::get_artists($IMDBIDs);
        foreach ($IMDBIDs as $IMDBID) {
            $ArtistDetail = MOVIE::get_default_artist($IMDBID);
            $Detail = $FullArtistDetails[$IMDBID];
            if ($Detail) {
                $ArtistDetail = $Detail;
            }
            $Artist['IMDBID'] = $IMDBID;
            $Artist['Image'] = $ArtistDetail['Image'];
            $Artist['Description'] = $ArtistDetail['Description'];
            $Artist['MainDescription'] = $ArtistDetail['MainDescription'];
            $Artist['Birthday'] = $ArtistDetail['Birthday'];
            $Artist['PlaceOfBirth'] = $ArtistDetail['PlaceOfBirth'];
            Artists::add_artist($Artist);
        }
    }

    public static function new_artist($ArtistForm, $MovieIMDNBID, $Limit = 10) {
        foreach ($ArtistForm[Artists::Actor] as $Num => $Artist) {
            if ($Artist['IMDBID']) {
                $IMDBIDs[] = $Artist['IMDBID'];
            }
        }
        foreach ($ArtistForm as $key => $value) {
            if ($key == Artists::Actor) {
                continue;
            }
            foreach ($value as $Num => $Artist) {
                if ($Artist['IMDBID']) {
                    $IMDBIDs[] = $Artist['IMDBID'];
                }
            }
        }
        $FullArtistDetails = MOVIE::get_artists($IMDBIDs, $MovieIMDNBID, $Limit);
        foreach ($ArtistForm as $Importance => $Artists) {
            foreach ($Artists as $Num => $Artist) {
                $Artist['Name'] = $Artist['Name'];
                $Artist['SubName'] = $Artist['SubName'];
                $ArtistDetail = MOVIE::get_default_artist($Artist['IMDBID']);
                if ($Artist['IMDBID']) {
                    $Detail = $FullArtistDetails[$Artist['IMDBID']];
                    if ($Detail) {
                        $ArtistDetail = $Detail;
                    }
                }

                $Artist['Image'] = $ArtistDetail['Image'];
                $Artist['Description'] = $ArtistDetail['Description'];
                $Artist['MainDescription'] = $ArtistDetail['MainDescription'];
                $Artist['Birthday'] = $ArtistDetail['Birthday'];
                $Artist['PlaceOfBirth'] = $ArtistDetail['PlaceOfBirth'];
                $Artist = Artists::add_artist($Artist);
                $ArtistForm[$Importance][$Num] = $Artist;
            }
        }
        return $ArtistForm;
    }

    public static function add_artist($Artist, $Summary = "Auto load") {
        $UserID = G::$LoggedUser['ID'];
        if (empty($UserID)) {
            $UserID = 0;
        }
        G::$DB->begin_transaction();
        $IMDBID = $Artist['IMDBID'];
        $Name = $Artist['Name'];
        $SubName = $Artist['SubName'];
        $Image = $Artist['Image'];
        $Body = $Artist['Description'];
        $MainBody = $Artist['MainDescription'];
        $Birth = $Artist['Birthday'];
        $Place = $Artist['PlaceOfBirth'];
        $ArtistAliasList = $Artist['Alias'];

        $New = false;
        $Change = false;

        if (!empty($IMDBID)) {
            G::$DB->prepared_query("SELECT * FROM artists_group WHERE IMDBID = ? FOR UPDATE", $IMDBID);
            $OldArtist = G::$DB->next_record(MYSQLI_ASSOC);
            if ($OldArtist) {
                $OldID = $OldArtist['ArtistID'];
                $OldName = $OldArtist['Name'];
                $OldSubName = $OldArtist['SubName'];
                $Updates = [];
                if (!empty($Name) && empty($OldName)) {
                    G::$DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $OldID, $Name);
                    $Updates[] = "Name = '" . db_string($Name) . "'";
                } else if (!empty($OldArtist['Name'])) {
                    $Name = $OldArtist['Name'];
                } else {
                    $Name = '';
                }
                if (!empty($SubName) && empty($OldSubName)) {
                    G::$DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $OldID, $SubName);
                    $Updates[] = "SubName = '" . db_string($SubName) . "'";
                } else if (!empty($OldArtist['SubName'])) {
                    $SubName = $OldArtist['SubName'];
                } else {
                    $SubName = '';
                }
                if (empty($OldArtist['Image']) && !empty($Image)) {
                    $Updates[] = "Image = '" . db_string($Image) . "'";
                } else if (!empty($OldArtist['Image'])) {
                    $Image = $OldArtist['Image'];
                }
                if (empty($OldArtist['Body']) && !empty($Body)) {
                    $Updates[] = "Body = '" . db_string($Body) . "'";
                } else if (!empty($OldArtist['Body'])) {
                    $Body = $OldArtist['Body'];
                }
                if (empty($OldArtist['MainBody']) && !empty($MainBody)) {
                    $Updates[] = "MainBody = '" . db_string($MainBody) . "'";
                } else if (!empty($OldArtist['MainBody'])) {
                    $MainBody = $OldArtist['MainBody'];
                }
                if (!empty($Birth)) {
                    $Updates[] = "Birthday = '" . db_string($Birth) . "'";
                }
                if (!empty($Place)) {
                    $Updates[] = "PlaceOfBirth = '" . db_string($Place) . "'";
                }
                $Artist['ArtistID'] = $OldID;
                if (count($Updates) > 0) {
                    G::$DB->query("UPDATE artists_group SET " . implode(' , ', $Updates) . " WHERE ArtistID = $OldID");
                    $Change = true;
                }
            } else {
                G::$DB->prepared_query(
                    "INSERT INTO artists_group (Name, Body, MainBody, Image, IMDBID, SubName, Birthday, PlaceOfBirth) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    $Name,
                    $Body,
                    $MainBody,
                    $Image,
                    $IMDBID,
                    $SubName,
                    $Birth,
                    $Place
                );
                $Artist['ArtistID'] = G::$DB->inserted_id();
                $New = true;
            }
        } else {
            G::$DB->prepared_query("INSERT INTO artists_group (Name, SubName) VALUES (?, ?)", $Name, $SubName);
            $Artist['ArtistID'] = G::$DB->inserted_id();
            $New = true;
        }

        $ArtistID = $Artist['ArtistID'];
        if ($Change || $New) {
            G::$DB->prepared_query("INSERT INTO wiki_artists
							(PageID, Body, MainBody, Image, UserID, Summary, Time, IMDBID, Name, SubName)
						VALUES
							(?,?,?,?,?,?,?,?,?,?)", $ArtistID, $Body, $MainBody, $Image, $UserID, $Summary, Time::sqltime(), $IMDBID, $Name, $SubName);
            $RevisionID = G::$DB->inserted_id();
            G::$DB->prepared_query("UPDATE artists_group SET RevisionID = ? WHERE ArtistID = ?", $RevisionID, $ArtistID);
            if ($New) {
                G::$DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $ArtistID, $Name);
                foreach ($ArtistAliasList as $key => $value) {
                    G::$DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $ArtistID, $value);
                }
                if ($SubName) {
                    G::$DB->prepared_query("INSERT INTO artists_alias (ArtistID, Name)
						VALUES (?, ?)", $ArtistID, $SubName);
                }
            }
        }

        G::$DB->commit();
        if ($New) {
            G::$Cache->increment('stats_artist_count');
        }
        return $Artist;
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
        $name = Lang::choose_content($Artist['Name'], $Artist['SubName']);
        if ($MakeLink && !$Escape) {
            error('Invalid parameters to Artists::display_artist()');
        } elseif ($MakeLink) {
            return '<a href="artist.php?id=' . $Artist['ArtistID'] . '" dir="ltr">' . display_str($name) . '</a>';
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
			SELECT Name, SubName
			FROM artists_group
			WHERE ArtistID = " . $ArtistID);
        $Artist = G::$DB->next_record(MYSQLI_ASSOC, false);
        $Name = Artists::display_artist($Artist, false);

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
