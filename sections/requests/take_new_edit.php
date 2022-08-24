<?

//******************************************************************************//
//----------------- Take request -----------------------------------------------//
authorize();


if ($_POST['action'] !== 'takenew' && $_POST['action'] !== 'takeedit') {
    error(0);
}

$NewRequest = ($_POST['action'] === 'takenew');

if (!$NewRequest) {
    $ReturnEdit = true;
}

if ($NewRequest) {
    if (!check_perms('site_submit_requests') || $LoggedUser['BytesUploaded'] < 250 * 1024 * 1024) {
        error(403);
    }
} else {
    $RequestID = $_POST['requestid'];
    if (!is_number($RequestID)) {
        error(0);
    }

    $Request = Requests::get_request($RequestID);
    if ($Request === false) {
        error(404);
    }
    $VoteArray = Requests::get_votes_array($RequestID);
    $VoteCount = count($VoteArray['Voters']);
    $IsFilled = !empty($Request['TorrentID']);
    $CategoryName = $Categories[$Request['CategoryID'] - 1];
    $ProjectCanEdit = check_perms('project_team') && !$IsFilled;
    $CanEdit = ((!$IsFilled && $LoggedUser['ID'] === $Request['UserID'] && $VoteCount < 2) || $ProjectCanEdit || check_perms('site_moderate_requests'));

    if (!$CanEdit) {
        error(403);
    }
}

// Validate
if (empty($_POST['type'])) {
    error(0);
}

$CategoryName = $_POST['type'];
$CategoryID = (array_search($CategoryName, $Categories) + 1);

if (empty($CategoryID)) {
    error(0);
}

if (empty($_POST['name'])) {
    $Err = t('server.requests.forgot_enter_title');
} else {
    $Title = trim($_POST['name']);
}

if (!empty($_POST['subname'])) {
    $Subtitle = trim($_POST['subname']);
}


if (!empty($_POST['source_torrent'])) {
    $SourceTorrent = trim($_POST['source_torrent']);
}

if (!empty($_POST['purchasable_at'])) {
    $PurchasableAt = trim($_POST['purchasable_at']);
}
if (!empty($_POST['imdb'])) {
    preg_match('/(tt\\d+)/', $_POST['imdb'], $IMDBMatch);
    if ($IMDBMatch[1]) {
        $IMDBID = $IMDBMatch[1];
    } else {
        die("invalid imdb");
    }
}
if (empty($_POST['tags'])) {
    $Err = t('server.requests.forgot_enter_tags');
} else {
    $Tags = trim($_POST['tags']);
}

if ($NewRequest) {
    if (empty($_POST['amount'])) {
        $Err = t('server.requests.forgot_enter_bounty');
    } else {
        $Bounty = trim($_POST['amount']);
        if (!is_number($Bounty)) {
            $Err = t('server.requests.entered_bounty_not_number');
        } elseif ($Bounty < 100 * 1024 * 1024) {
            $Err = t('server.requests.min_bounty');
        }
        $Bytes = $Bounty; //From MB to B
    }
}

if (empty($_POST['image'])) {
    $Image = '';
} else {
    ImageTools::blacklisted($_POST['image']);
    if (preg_match('/' . IMAGE_REGEX . '/', trim($_POST['image'])) > 0) {
        $Image = trim($_POST['image']);
    } else {
        $Err = display_str($_POST['image']) . t('server.requests.image_link_invalid');
    }
}

if (empty($_POST['description'])) {
    $Err = t('server.requests.forgot_enter_description');
} else {
    $Description = trim($_POST['description']);
}

if (empty($_POST['artist_ids'])) {
    $Err = t('server.requests.forgot_enter_artists');
} else {
    $Artists = $_POST['artists'];
    $ArtistIDs = $_POST['artist_ids'];
    $ArtistsSubName = $_POST['artists_sub'];
    $Importance = $_POST['importance'];
}

if (!is_number($_POST['releasetype']) || !in_array($_POST['releasetype'], $ReleaseTypes)) {
    $Err = t('server.requests.forgot_pick_release_type');
}

$ReleaseType = $_POST['releasetype'];

if (empty($_POST['all_codecs']) && count($_POST['codecs']) !== count($Codecs)) {
    $CodecArray = $_POST['codecs'];
    if (count($CodecArray) < 1) {
        $Err = t('server.requests.require_one_codec');
    }
} else {
    $AllCodec = true;
}

if (empty($_POST['all_sources']) && count($_POST['sources']) !== count($Sources)) {
    $SourceArray = $_POST['sources'];
    if (count($SourceArray) < 1) {
        $Err = t('server.requests.require_one_source');
    }
} else {
    $AllSources = true;
}

if (empty($_POST['all_containers']) && count($_POST['containers']) !== count($Containers)) {
    $ContainerArray = $_POST['containers'];
    if (count($ContainerArray) < 1) {
        $Err = t('server.requests.require_one_container');
    }
} else {
    $AllContainer = true;
}
if (empty($_POST['all_resolutions']) && count($_POST['resolutions']) !== count($Resolutions)) {
    $ResolutionArray = $_POST['resolutions'];
    if (count($ResolutionArray) < 1) {
        $Err = t('server.requests.require_one_resolution');
    }
} else {
    $AllResolution = true;
}

// GroupID
if (!empty($_POST['group'])) {
    $GroupID = trim($_POST['group']);
    if (preg_match('/^' . TORRENT_GROUP_REGEX . '/i', $GroupID, $Matches)) {
        $GroupID = $Matches[2];
    }
    if (is_number($GroupID)) {
        $DB->query("
				SELECT 1
				FROM torrents_group
				WHERE ID = '$GroupID'
					AND CategoryID = 1");
        if (!$DB->has_results()) {
            $Err = t('server.requests.torrent_group_must_correspond_site');
        }
    } else {
        $Err = t('server.requests.torrent_group_must_correspond_site');
    }
} else {
    $GroupID = 0;
}

if (empty($_POST['year'])) {
    $Err = t('server.requests.forgot_enter_year');
} else {
    $Year = trim($_POST['year']);
    if (!is_number($Year)) {
        $Err = t('server.requests.entered_year_not_number');
    }
}

$Director = [];
for ($i = 0, $il = count($Artists); $i < $il; $i++) {
    if (trim($ArtistIDs[$i]) !== '') {
        $Director[] = array('IMDBID' => trim($ArtistIDs[$i]), 'Name' => db_string(trim($Artists[$i])), 'SubName' => db_string(trim($ArtistsSubName[$i])));
    }
}
if (count($Director) < 1) {
    $Err = t('server.requests.at_least_one_director');
}


if (!empty($Err)) {
    error($Err);
    $Div = $_POST['unit'] === 'mb' ? 1024 * 1024 : 1024 * 1024 * 1024;
    $Bounty /= $Div;
    include(CONFIG['SERVER_ROOT'] . '/sections/requests/new_edit.php');
    die();
}

//Databasify the input
if (empty($AllCodec)) {
    foreach ($CodecArray as $Index => $MasterIndex) {
        if (array_key_exists($Index, $Codecs)) {
            $CodecArray[$Index] = $Codecs[$MasterIndex];
        } else {
            //Hax
            error(0);
        }
    }
    $CodecList = implode('|', $CodecArray);
} else {
    $CodecList = 'Any';
}

if (empty($AllSources)) {
    foreach ($SourceArray as $Index => $MasterIndex) {
        if (array_key_exists($Index, $Sources)) {
            $SourceArray[$Index] = $Sources[$MasterIndex];
        } else {
            //Hax
            error(0);
        }
    }
    $SourceList = implode('|', $SourceArray);
} else {
    $SourceList = 'Any';
}

if (empty($AllContainer)) {
    foreach ($ContainerArray as $Index => $MasterIndex) {
        if (array_key_exists($Index, $Containers)) {
            $ContainerArray[$Index] = $Containers[$MasterIndex];
        } else {
            //Hax
            error(0);
        }
    }
    $ContainerList = implode('|', $ContainerArray);
} else {
    $ContainerList = 'Any';
}

if (empty($AllResolution)) {
    foreach ($ResolutionArray as $Index => $MasterIndex) {
        if (array_key_exists($Index, $Resolutions)) {
            $ResolutionArray[$Index] = $Resolutions[$MasterIndex];
        } else {
            //Hax
            error(0);
        }
    }
    $ResolutionList = implode('|', $ResolutionArray);
} else {
    $ResolutionList = 'Any';
}

//Query time!
if ($NewRequest) {
    $DB->query('
			INSERT INTO requests (
				UserID, TimeAdded, LastVote, CategoryID, Title, Year, Image, Description, 
                ReleaseType, CodecList, SourceList, ContainerList, ResolutionList, IMDBID, PurchasableAt, Subtitle, SourceTorrent, Visible, GroupID)
			VALUES
				(' . $LoggedUser['ID'] . ", '" . sqltime() . "', '" . sqltime() . "', $CategoryID, '" . db_string($Title) . "', $Year, '" . db_string($Image) . "', '" . db_string($Description) . "', " .
        $ReleaseType . ", '$CodecList','$SourceList', '$ContainerList', '$ResolutionList', '$IMDBID', '" . db_string($PurchasableAt) . "', '" . db_string($Subtitle) . "', '" . db_string($SourceTorrent) . "', 1, " . $GroupID . ")");

    $RequestID = $DB->inserted_id();
} else {
    $DB->query("
			UPDATE requests
			SET CategoryID = $CategoryID,
				Title = '" . db_string($Title) . "',
				Subtitle = '" . db_string($Subtitle) . "',
				Year = $Year,
				Image = '" . db_string($Image) . "',
				Description = '" . db_string($Description) . "',
				ReleaseType = $ReleaseType,
				SourceList = '$SourceList',
				ResolutionList = '$ResolutionList',
				CodecList = '$CodecList',
				ContainerList = '$ContainerList',
				IMDBID = '$IMDBID',
				PurchasableAt = '" . db_string($PurchasableAt) . "',
				SourceTorrent = '" . db_string($SourceTorrent) . "',
				GroupID = '$GroupID'
			WHERE ID = $RequestID");

    // We need to be able to delete artists / tags
    $DB->query("
			SELECT ArtistID
			FROM requests_artists
			WHERE RequestID = $RequestID");
    $RequestArtists = $DB->to_array();
    foreach ($RequestArtists as $RequestArtist) {
        $Cache->delete_value("artists_requests_$RequestArtist");
    }
    $DB->query("
			DELETE FROM requests_artists
			WHERE RequestID = $RequestID");
    $Cache->delete_value("request_artists_$RequestID");
}

if ($GroupID) {
    $Cache->delete_value("requests_group_$GroupID");
}

/*
     * Multiple Artists!
     * For the multiple artists system, we have 3 steps:
     * 1. See if each artist given already exists and if it does, grab the ID.
     * 2. For each artist that didn't exist, create an artist.
     * 3. Create a row in the requests_artists table for each artist, based on the ID.
*/
$Director = Artists::new_artist([Artists::Director => $Director], $IMDBID)[Artists::Director];

//3. Create a row in the requests_artists table for each artist, based on the ID.
foreach ($Director as $Num => $Artist) {
    $Importance = Artists::Director;
    $DB->query("
				INSERT IGNORE INTO requests_artists
					(RequestID, ArtistID, Importance)
				VALUES
					($RequestID, " . $Artist['ArtistID'] .  ", '$Importance')");
    // $Cache->increment('stats_album_count');
    $Cache->delete_value('artists_requests_' . $Artist['id']);
}
$UserHeavyInfo = Users::user_heavy_info($LoggedUser['ID']);
if ($UserHeavyInfo['RequestsAlerts']) {
    Subscriptions::subscribe_comments('requests', $RequestID);
}
//Tags
if (!$NewRequest) {
    $DB->query("
		DELETE FROM requests_tags
		WHERE RequestID = $RequestID");
}

$Tags = array_unique(explode(',', $Tags));
$Tags = Tags::main_name($Tags);
$tagMan = new \Gazelle\Manager\Tag;
foreach ($Tags as $Index => $Tag) {
    $Tags[$Index] = $Tag; //For announce
    $TagID = $tagMan->create($Tag, $LoggedUser['ID']);
    if ($TagID) {
        $DB->query("
		INSERT IGNORE INTO requests_tags
			(TagID, RequestID)
		VALUES
			($TagID, $RequestID)");
    }
}

if ($NewRequest) {
    //Remove the bounty and create the vote
    $DB->query("
		INSERT INTO requests_votes
			(RequestID, UserID, Bounty)
		VALUES
			($RequestID, " . $LoggedUser['ID'] . ', ' . ($Bytes * (1 - $RequestTax)) . ')');

    $DB->query("
		UPDATE users_main
		SET Uploaded = (Uploaded - $Bytes)
		WHERE ID = " . $LoggedUser['ID']);
    $Cache->delete_value('user_stats_' . $LoggedUser['ID']);
    $Announce = "\"$Title\" - " . site_url() . "requests.php?action=view&id=$RequestID - " . implode(' ', $Tags);
    send_irc("PRIVMSG #requests :{$Announce}");
} else {
    $Cache->delete_value("request_$RequestID");
    $Cache->delete_value("request_artists_$RequestID");
}

Requests::update_sphinx_requests($RequestID);

header("Location: requests.php?action=view&id=$RequestID");
