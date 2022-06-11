<?
//******************************************************************************//
//--------------- Take upload --------------------------------------------------//
// This pages handles the backend of the torrent upload function. It checks     //
// the data, and if it all validates, it builds the torrent file, then writes   //
// the data to the database and the torrent to the disk.                        //
//******************************************************************************//

// Maximum allowed size for uploaded files.
// http://php.net/upload-max-filesize
//ini_set('upload_max_filesize', 2097152); // 2 Mibibytes


ini_set('max_file_uploads', 100);
define('MAX_FILENAME_LENGTH', 255);

use Gazelle\Torrent\EditionInfo;
use Gazelle\Torrent\TorrentSlot;

include(SERVER_ROOT . '/classes/validate.class.php');
include(SERVER_ROOT . '/classes/feed.class.php');
include(SERVER_ROOT . '/sections/torrents/functions.php');
include(SERVER_ROOT . '/classes/file_checker.class.php');

enforce_login();
authorize();

$Validate = new VALIDATE;
$Feed = new FEED;

define('QUERY_EXCEPTION', true); // Shut up debugging

//******************************************************************************//
//--------------- Set $Properties array ----------------------------------------//
// This is used if the form doesn't validate, and when the time comes to enter  //
// it into the database.                                                        //

$Properties = array();
$Type = $Categories[(int)$_POST['type']];
$TypeID = $_POST['type'] + 1;
if (!empty($_POST['imdb'])) {
    preg_match('/(tt\\d+)/', $_POST['imdb'], $IMDBMatch);
    if ($IMDBMatch[1]) {
        $Properties['IMDBID'] = $IMDBMatch[1];
    } else {
        die("invalid imdb");
    }
}
$Properties['TrailerLink'] = $_POST['trailer_link'];
$Properties['NotMainMovie'] = isset($_POST['not_main_movie']) ? 1 : 0;
$Properties['Source'] = $_POST['source'];
if ($Properties['Source'] == 'Other') {
    $Properties['Source'] = $_POST['source_other'];
}
$Properties['Codec'] = $_POST['codec'];
if ($Properties['Codec'] == 'Other') {
    $Properties['Codec'] = $_POST['codec_other'];
}
$Properties['Container'] = $_POST['container'];
if ($Properties['Container'] == 'Other') {
    $Properties['Container'] = $_POST['container_other'];
}
$Properties['Resolution'] = $_POST['resolution'];
if ($Properties['Resolution'] == 'Other' && $_POST['resolution_width'] && $_POST['resolution_height']) {
    $Properties['Resolution'] = $_POST['resolution_width'] . '×' . $_POST['resolution_height'];
}
$Properties['NoSub'] = isset($_POST['no_sub']) ? 1 : 0;
$Properties['Subtitles'] = implode(',', $_POST['subtitles']);
$Properties['Makers'] = isset($POST['makers']) ? $_POST['makers'] : "";
$Properties['Processing'] = $_POST['processing'];
$Properties['SubtitleType'] = $_POST['subtitle_type'];
if ($_POST['processing_other']) {
    $Properties['Processing'] = $_POST['processing_other'];
}
$Properties['SpecialSub'] = isset($_POST['special_effects_subtitles']) ? 1 : 0;
$Properties['ChineseDubbed'] = isset($_POST['chinese_dubbed']) ? 1 : 0;

$Properties['CategoryName'] = $Type;
$Properties['Name'] = html_entity_decode($_POST['name'], ENT_QUOTES);

$Properties['SubName'] = isset($_POST['subname']) ? html_entity_decode($_POST['subname'], ENT_QUOTES) : '';

$Properties['NoSub'] = (isset($_POST['no_sub'])) ? 1 : 0;
$Properties['HardSub'] = (isset($_POST['hardcode_sub'])) ? 1 : 0;
if ($Properties['SubtitleType'] == 2) {
    $Properties['HardSub'] = 1;
} else if ($Properties['SubtitleType'] == 3) {
    $Properties['NoSub'] = 1;
}
$Properties['BadFoldes'] = (isset($_POST['bad_folders'])) ? 1 : 0;

$Properties['RemasterYear'] = trim($_POST['remaster_year']);
$Properties['Year'] = trim($_POST['year']);
$Properties['ReleaseType'] = $_POST['releasetype'];
$Properties['Scene'] = isset($_POST['scene']) ? 1 : 0;
$Properties['Jinzhuan'] = isset($_POST['jinzhuan']) ? 1 : 0;
$Properties['Diy'] = isset($_POST['diy']) ? 1 : 0;
$Properties['Buy'] = isset($_POST['buy']) ? 1 : 0;
$Properties['Allow'] = isset($_POST['allow']) ? 1 : 0;
$Properties['TagList'] = $_POST['tags'];
$Properties['Image'] = $_POST['image'];
$Properties['GroupDescription'] = trim($_POST['desc']);

$Properties['RemasterTitle'] = trim($_POST['remaster_title']);
if ($Properties['RemasterTitle']) {
    $RemasterTitles = explode(' / ', $Properties['RemasterTitle']);
    $AllTitles = EditionInfo::allEditionKey();
    foreach ($RemasterTitles as $Title) {
        if (!in_array($Title, $AllTitles)) {
            die("invalid remaster_title");
        }
    }
}

$Properties['RemasterCustomTitle'] = html_entity_decode($_POST['remaster_custom_title'], ENT_QUOTES);

$Properties['TorrentDescription'] = $_POST['release_desc'];

$Properties['GroupID'] = $_POST['groupid'];
if (empty($_POST['artists'])) {
    $Err = "You didn't enter any artists";
} else {
    $Artists = $_POST['artists'];
    $Importance = $_POST['importance'];
    $ArtistIMDBIDs = $_POST['artist_ids'];
    $ArtistChineseName = $_POST['artists_chinese'];
}
if (!empty($_POST['requestid'])) {
    $RequestID = $_POST['requestid'];
    $Properties['RequestID'] = $RequestID;
}
$Properties['MediaInfo']  = $_POST['mediainfo'] ? json_encode($_POST['mediainfo']) : null;
$Properties['Note'] = isset($POST['staff_note']) ? trim($_POST['staff_note']) : "";
//******************************************************************************//
//--------------- Validate data in upload form ---------------------------------//

// $Validate->SetFields('type', '1', 'inarray', Lang::get('upload', 'select_a_type'), array('inarray' => array_keys($Categories)));

$Validate->SetFields(
    'codec',
    '1',
    'string',
    Lang::get('upload', 'select_valid_format')
);
$Validate->SetFields(
    'resolution',
    '1',
    'string',
    Lang::get('upload', 'select_valid_format')
);
$Validate->SetFields(
    'container',
    '1',
    'string',
    Lang::get('upload', 'select_valid_format')
);
$Validate->SetFields(
    'source',
    '1',
    'string',
    Lang::get('upload', 'select_valid_format')
);

$Validate->SetFields(
    'name',
    '1',
    'string',
    Lang::get('upload', 'title_length_limit')
);

$Err = $Validate->ValidateForm($_POST); // Validate the form

$File = $_FILES['file_input']; // This is our torrent file
$TorrentName = $File['tmp_name'];

if (!is_uploaded_file($TorrentName) || !filesize($TorrentName)) {
    $Err = Lang::get('upload', 'no_torrent_uploaded');
} elseif (substr(strtolower($File['name']), strlen($File['name']) - strlen('.torrent')) !== '.torrent') {
    $Err = Lang::get('upload', 'not_torrent_file') . "(" . $File['name'] . ")" . Lang::get('upload', 'period');
}

if ($Type == 'Movies') {
    //extra torrent files
    $ExtraTorrents = array();
    $DupeNames = array();
    $DupeNames[] = $_FILES['file_input']['name'];
}

//Multiple artists!
if (empty($Properties['GroupID'])) {
    $MainArtistCount = 0;
    $ArtistNames = array(
        1 => array(),
        2 => array(),
        3 => array(),
        4 => array(),
        5 => array(),
        6 => array(),
    );
    $ArtistForm = array(
        1 => array(),
        2 => array(),
        3 => array(),
        4 => array(),
        5 => array(),
        6 => array(),
    );
    for ($i = 0, $il = count($Artists); $i < $il; $i++) {
        if (trim($Artists[$i]) != '') {
            if ($Importance[$i] == 1) {
                $MainArtistCount++;
            }
            if (!in_array(trim($Artists[$i]), $ArtistNames[$Importance[$i]])) {
                $ArtistForm[$Importance[$i]][] = array('name' => Artists::normalise_artist_name($Artists[$i]), 'imdbid' => isset($ArtistIMDBIDs[$i]) ? $ArtistIMDBIDs[$i] : null, 'chinese_name' => $ArtistChineseName[$i]);
                $ArtistNames[$Importance[$i]][] = trim($Artists[$i]);
            }
        }
    }
    if ($MainArtistCount < 1) {
        $Err = Lang::get('upload', 'enter_at_least_one_artist');
        $ArtistForm = array();
    }
} else {
    $DB->query("
		SELECT ta.ArtistID, aa.Name, ta.Importance
		FROM torrents_artists AS ta
			JOIN artists_alias AS aa ON ta.AliasID = aa.AliasID
		WHERE ta.GroupID = " . $Properties['GroupID'] . "
		ORDER BY ta.Importance ASC, aa.Name ASC;");
    while (list($ArtistID, $ArtistName, $ArtistImportance) = $DB->next_record(MYSQLI_BOTH, false)) {
        $ArtistForm[$ArtistImportance][] = array('id' => $ArtistID, 'name' => display_str($ArtistName));
        $ArtistsUnescaped[$ArtistImportance][] = array('name' => $ArtistName);
    }
}

if ($Err) { // Show the upload form, with the data the user entered
    $UploadForm = $Type;
    include(SERVER_ROOT . '/sections/upload/upload.php');
    die();
}

// Strip out Amazon's padding
ImageTools::blacklisted($Properties['Image']);

//******************************************************************************//
//--------------- Make variables ready for database input ----------------------//

// Shorten and escape $Properties for database input
$T = array();
foreach ($Properties as $Key => $Value) {
    $T[$Key] = "'" . db_string(trim($Value)) . "'";
    if (!$T[$Key]) {
        $T[$Key] = null;
    }
}


//******************************************************************************//
//--------------- Generate torrent file ----------------------------------------//

$Tor = new BencodeTorrent($TorrentName, true);
$PublicTorrent = $Tor->make_private(); // The torrent is now private.
$UnsourcedTorrent = $Tor->set_source(); // The source is now TORRENT_SOURCE
$TorEnc = db_string($Tor->encode());
$InfoHash = pack('H*', $Tor->info_hash());

$DB->query("
	SELECT ID
	FROM torrents
	WHERE info_hash = '" . db_string($InfoHash) . "'");
if ($DB->has_results()) {
    list($ID) = $DB->next_record();
    $DB->query("
		SELECT TorrentID
		FROM torrents_files
		WHERE TorrentID = $ID");
    if ($DB->has_results()) {
        $Err = '<a class="u-colorWarning" href="torrents.php?torrentid=' . $ID . '">' . Lang::get('upload', 'same_torrent_exists') . '</a>';
    } else {
        // A lost torrent
        $DB->query("
			INSERT INTO torrents_files (TorrentID, File)
			VALUES ($ID, '$TorEnc')");
        $Err = '<a href="torrents.php?torrentid=' . $ID . '">' . Lang::get('upload', 'thank_you_fix_torrent') . '</a>';
    }
}

if (isset($Tor->Dec['encrypted_files'])) {
    $Err = Lang::get('upload', 'not_supported_encrypted_file_list');
}

// File list and size
list($TotalSize, $FileList) = $Tor->file_list();
$NumFiles = count($FileList);
$TmpFileList = array();
$TooLongPaths = array();
$DirName = (isset($Tor->Dec['info']['files']) ? Format::make_utf8($Tor->get_name()) : '');
$IgnoredLogFileNames = array('audiochecker.log', 'sox.log');
check_name($DirName); // check the folder name against the blacklist
foreach ($FileList as $File) {
    list($Size, $Name) = $File;

    // Check file name and extension against blacklist/whitelist
    check_file($Type, $Name);
    // Make sure the filename is not too long
    if (mb_strlen($Name, 'UTF-8') + mb_strlen($DirName, 'UTF-8') + 1 > MAX_FILENAME_LENGTH) {
        $TooLongPaths[] = "$DirName/$Name";
    }
    // Add file info to array
    $TmpFileList[] = Torrents::filelist_format_file($File);
}
if (count($TooLongPaths) > 0) {
    $Names = implode(' <br />', $TooLongPaths);
    $Err = Lang::get('upload', 'name_too_long') . "$Names";
}
$FilePath = db_string($DirName);
$FileString = db_string(implode("\n", $TmpFileList));
$Debug->set_flag('upload: torrent decoded');


if (!empty($Err)) { // Show the upload form, with the data the user entered
    $UploadForm = $Type;
    include(SERVER_ROOT . '/sections/upload/upload.php');
    die();
}

//******************************************************************************//
//--------------- Start database stuff -----------------------------------------//

$Body = $Properties['GroupDescription'];

// Trickery
if (!preg_match('/^' . IMAGE_REGEX . '$/i', $Properties['Image'])) {
    $Properties['Image'] = '';
    $T['Image'] = "''";
}

// Does it belong in a group?
if ($Properties['GroupID']) {
    $DB->query("
			SELECT
				ID,
				WikiImage,
				WikiBody,
				RevisionID,
				Name,
				Year,
				ReleaseType,
				TagList,
				SubName
			FROM torrents_group
			WHERE id = " . $Properties['GroupID']);
    if ($DB->has_results()) {
        // Don't escape tg.Name. It's written directly to the log table
        list($GroupID, $WikiImage, $WikiBody, $RevisionID, $Properties['Name'], $GroupYear, $Properties['ReleaseType'], $Properties['TagList'], $GroupSubName) = $DB->next_record(MYSQLI_NUM, array(4));
        $Properties['TagList'] = str_replace(array(' ', '.', '_'), array(', ', '.', '.'), $Properties['TagList']);
        if (!$Properties['Image'] && $WikiImage) {
            $Properties['Image'] = $WikiImage;
            $T['Image'] = "'" . db_string($WikiImage) . "'";
        }
        if (strlen($WikiBody) > strlen($Body)) {
            $Body = $WikiBody;
            if (!$Properties['Image'] || $Properties['Image'] == $WikiImage) {
                $NoRevision = true;
            }
        }
        $ArtistForm = Artists::get_artist($GroupID);
    }
} else {
    foreach ($ArtistForm as $Importance => $Artists) {
        foreach ($Artists as $Num => $Artist) {
            $RedirectName = $Artist['name'];
            $DB->query("
				SELECT
					aa2.Name
				FROM artists_alias aa1 INNER JOIN artists_alias aa2 on aa1.Redirect=aa2.AliasID
				WHERE
					aa1.Name = lower('" . db_string($Artist['name']) . "')");
            if ($DB->has_results()) {
                list($RedirectName) = $DB->next_record(MYSQLI_NUM, false);
            }
            $DB->query("
					SELECT
						tg.id,
						tg.WikiImage,
						tg.WikiBody,
						tg.RevisionID,
						tg.Year,
						SubName
					FROM torrents_group AS tg
						LEFT JOIN torrents_artists AS ta ON ta.GroupID = tg.ID
						LEFT JOIN artists_group AS ag ON ta.ArtistID = ag.ArtistID
					WHERE ag.Name = '" . db_string($RedirectName) . "'
						AND lower(tg.Name) = lower(" . $T['Name'] . ")
						AND tg.ReleaseType = " . $T['ReleaseType'] . "
						AND tg.Year = " . $T['Year']);

            if ($DB->has_results()) {
                list($GroupID, $WikiImage, $WikiBody, $RevisionID, $GroupYear, $GroupSubName) = $DB->next_record();
                if (!$Properties['Image'] && $WikiImage) {
                    $Properties['Image'] = $WikiImage;
                    $T['Image'] = "'" . db_string($WikiImage) . "'";
                }
                if (strlen($WikiBody) > strlen($Body)) {
                    $Body = $WikiBody;
                    if (!$Properties['Image'] || $Properties['Image'] == $WikiImage) {
                        $NoRevision = true;
                    }
                }
                $ArtistForm = Artists::get_artist($GroupID);
                //This torrent belongs in a group
                break;
            } else {
                // The album hasn't been uploaded. Try to get the artist IDs
                $DB->query("
						SELECT
							ArtistID,
							AliasID,
							Name,
							Redirect
						FROM artists_alias
						WHERE Name = '" . db_string($Artist['name']) . "'");
                if ($DB->has_results()) {
                    while (list($ArtistID, $AliasID, $AliasName, $Redirect) = $DB->next_record(MYSQLI_NUM, false)) {
                        if (!strcasecmp($Artist['name'], $AliasName)) {
                            if ($Redirect) {
                                $AliasID = $Redirect;
                            }
                            $ArtistForm[$Importance][$Num] = array('id' => $ArtistID, 'aliasid' => $AliasID, 'name' => $AliasName);
                            break;
                        }
                    }
                }
            }
        }
    }
}

//Needs to be here as it isn't set for add format until now
$Properties['Size'] = $TotalSize;
$Properties['Group'] = ['SubName' => $Properties['SubName'], 'Name' => $Properties['Name'], 'Year' => $Properties['Year']];
$LogName = Torrents::torrent_name($Properties, false);
//For notifications--take note now whether it's a new group
$IsNewGroup = !$GroupID;

//----- Start inserts
if (!$GroupID) {
    //array to store which artists we have added already, to prevent adding an artist twice
    $ArtistsAdded = array();
    $IMDBIDs = array();
    if ($Properties['IMDBID']) {
        // 优先处理演员
        foreach ($ArtistForm[6] as $Num => $Artist) {
            if (!$Artist['id'] && $Artist['imdbid']) {
                $IMDBIDs[] = $Artist['imdbid'];
            }
        }
        foreach ($ArtistForm as $key => $value) {
            if ($key == 6) {
                continue;
            }
            foreach ($value as $Num => $Artist) {
                if (!$Artist['id'] && $Artist['imdbid']) {
                    $IMDBIDs[] = $Artist['imdbid'];
                }
            }
        }
    }
    $FullArtistDetails = MOVIE::get_artists($IMDBIDs, $Properties['IMDBID'], 10);
    foreach ($ArtistForm as $Importance => $Artists) {
        foreach ($Artists as $Num => $Artist) {
            $Artist['name'] = html_entity_decode($Artist['name'], ENT_QUOTES);
            if ($Artist['id']) {
                continue;
            }
            if (isset($ArtistsAdded[strtolower($Artist['name'])])) {
                $ArtistForm[$Importance][$Num] = $ArtistsAdded[strtolower($Artist['name'])];
            } else {
                // Create artist
                $DB->query("
						INSERT INTO artists_group (Name)
						VALUES ('" . db_string($Artist['name']) . "')");
                $ArtistID = $DB->inserted_id();

                $ArtistDetail = MOVIE::get_default_artist($Artist['imdbid']);
                if ($Artist['imdbid']) {
                    $Detail = $FullArtistDetails[$Artist['imdbid']];
                    if ($Detail) {
                        $ArtistDetail = $Detail;
                    }
                }

                $ArtistImage = $ArtistDetail['Image'];
                $ArtistBody = $ArtistDetail['Description'];
                $ArtistIMDBID = $Artist['imdbid'];
                $ArtistCN = $Artist['chinese_name'];
                $ArtistBirth = $ArtistDetail['Birthday'];
                $ArtistPlace = $ArtistDetail['PlaceOfBirth'];
                $DB->query("
						INSERT INTO wiki_artists
							(PageID, Body, Image, UserID, Summary, Time, IMDBID, ChineseName, Birthday, PlaceOfBirth)
						VALUES
							('$ArtistID', '" . db_string($ArtistBody) . "', '$ArtistImage', '$UserID', 'Auto load from tmdb', '" . sqltime() . "', '$ArtistIMDBID', '" . db_string($ArtistCN) . "', '$ArtistBirth', '" . db_string($ArtistPlace) . "')");
                $RevisionID = $DB->inserted_id();
                $DB->query("
						UPDATE artists_group SET RevisionID = '$RevisionID' WHERE ArtistID = '$ArtistID'
				");

                $Cache->increment('stats_artist_count');
                $DB->query("
						INSERT INTO artists_alias (ArtistID, Name)
						VALUES ($ArtistID, '" . db_string($Artist['name']) . "')");
                $AliasID = $DB->inserted_id();
                $ArtistAliasList = $ArtistDetail['Alias'];
                foreach ($ArtistAliasList as $key => $value) {
                    $DB->query("
						INSERT INTO artists_alias (ArtistID, Name, Redirect)
						VALUES ($ArtistID, '" . db_string($value) . "', '" . $AliasID . "')");
                }
                if ($ArtistCN) {
                    $DB->query("
						INSERT INTO artists_alias (ArtistID, Name, Redirect)
						VALUES ($ArtistID, '" . db_string($ArtistCN) . "', '" . $AliasID . "')");
                }
                $ArtistForm[$Importance][$Num] = array('id' => $ArtistID, 'aliasid' => $AliasID, 'name' => $Artist['name']);
                $ArtistsAdded[strtolower($Artist['name'])] = $ArtistForm[$Importance][$Num];
            }
        }
    }
    unset($ArtistsAdded);
}
$RTRating = null;
$DoubanRating = 'null';
$DoubanID = 'null';
$DoubanVote = 'null';
$IMDBVote = 'null';
$IMDBID = '';
$RTRating = '';
$IMDBRating = 'null';
$Runtime = '';
$Released = '';
$Country = '';
$Language = '';
if ($Properties['IMDBID']) {
    $IMDBID = $Properties['IMDBID'];
    $OMDBData = MOVIE::get_omdb_data($Properties['IMDBID']);
    $IMDBRating = $OMDBData->imdbRating && $OMDBData->imdbRating != 'N/A' ? $OMDBData->imdbRating : 'null';
    $Runtime = $OMDBData->Runtime && $OMDBData->Runtime != 'N/A' ? $OMDBData->Runtime : '';
    $Released = $OMDBData->Released && $OMDBData->Released != 'N/A' ? $OMDBData->Released : '';
    $Country = $OMDBData->Country && $OMDBData->Country != 'N/A' ? $OMDBData->Country : '';
    $Lanuage = $OMDBData->Language && $OMDBData->Language != 'N/A' ? $OMDBData->Language : '';
    foreach ($OMDBData->Ratings as $key => $value) {
        if ($value->Source == "Rotten Tomatoes") {
            $RTRating = $value->Value;
        }
    }
    $DoubanData = MOVIE::get_douban_data($Properties['IMDBID']);
    $DoubanRating = $DoubanData->rating ? $DoubanData->rating : 'null';
    $DoubanID = $DoubanData->id ? $DoubanData->id : 'null';
    $DoubanVote = $DoubanData->votes ? $DoubanData->votes : 'null';
    $IMDBVote = $OMDBData->imdbVotes && $OMDBData->imdbVotes != 'N/A' ? str_replace(',', '', $OMDBData->imdbVotes) : 'null';
    $RTRating = $RTRating ? $RTRating : '';
}

if (!$GroupID) {
    // Create torrent group
    $DB->query("
		INSERT INTO torrents_group
			(ArtistID, CategoryID, Name, SubName, Year,  Time, WikiBody, WikiImage, ReleaseType, IMDBID, TrailerLink, IMDBRating, Duration, ReleaseDate, Region, Language, RTRating, DoubanRating, DoubanID, DoubanVote, IMDBVote)
		VALUES
			(0, $TypeID, " . $T['Name'] . ", " . $T['SubName'] . ", $T[Year],'" . sqltime() . "', '" . db_string($Body) . "', $T[Image], $T[ReleaseType], '" . $IMDBID . "', $T[TrailerLink], '" . $IMDBRating . "', '" . $Runtime . "', '" . $Released . "', '" . $Country . "', '" . $OMDBData->Language . "', '" . $RTRating . "', " . $DoubanRating . ", " . $DoubanID . ", " . $DoubanVote . ", " . $IMDBVote . ")");

    $GroupID = $DB->inserted_id();
    if ($Type == 'Movies') {
        foreach ($ArtistForm as $Importance => $Artists) {
            foreach ($Artists as $Num => $Artist) {
                $DB->query("
					INSERT IGNORE INTO torrents_artists (GroupID, ArtistID, AliasID, UserID, Importance, Credit, `Order`)
					VALUES ($GroupID, " . $Artist['id'] . ', ' . $Artist['aliasid'] . ', ' . $LoggedUser['ID'] . ", '$Importance', true, $Num)");
            }
        }
        $Cache->increment('stats_album_count');
    }
    $Cache->increment('stats_group_count');
} else {
    $UpdateTG = '';
    if (!$GroupSubName && $Properties['SubName']) {
        $UpdateTG = ", SubName = $T[SubName]";
    }
    $DB->query("
		UPDATE torrents_group
		SET Time = '" . sqltime() . "' $UpdateTG
		WHERE ID = $GroupID");
    $Cache->delete_value("torrent_group_$GroupID");
    $Cache->delete_value("torrents_details_$GroupID");
    $Cache->delete_value("detail_files_$GroupID");
    if ($Type == 'Movies') {
        $DB->query("
			SELECT ReleaseType
			FROM torrents_group
			WHERE ID = '$GroupID'");
        list($Properties['ReleaseType']) = $DB->next_record();
    }
}

// Description
if (!$NoRevision) {
    $DB->query("
		INSERT INTO wiki_torrents
			(PageID, Body, UserID, Summary, Time, Image, IMDBID, IMDBRating, Duration, ReleaseDate, Region, Language, RTRating, DoubanRating, DoubanID, DoubanVote, IMDBVote)
		VALUES
			($GroupID, $T[GroupDescription], $LoggedUser[ID], 'Uploaded new torrent', '" . sqltime() . "', $T[Image], '" . $IMDBID . "', " . $IMDBRating . ", '" . $Runtime . "', '" . $Released . "', '" . $Country . "', '" . $Language . "', '" . $RTRating . "', " . $DoubanRating . ", " . $DoubanID . ", " . $DoubanVote . ", " . $IMDBVote . ")");
    $RevisionID = $DB->inserted_id();

    // Revision ID
    $DB->query("
		UPDATE torrents_group
		SET RevisionID = '$RevisionID'
		WHERE ID = $GroupID");
}

// Tags
$Tags = explode(',', $Properties['TagList']);
if (!$Properties['GroupID']) {
    foreach ($Tags as $Tag) {
        $Tag = Misc::sanitize_tag($Tag);
        if (!empty($Tag)) {
            $Tag = Misc::get_alias_tag($Tag);
            $DB->query("
				INSERT INTO tags
					(Name, UserID)
				VALUES
					('$Tag', $LoggedUser[ID])
				ON DUPLICATE KEY UPDATE
					Uses = Uses + 1;
			");
            $TagID = $DB->inserted_id();

            $DB->query("
				INSERT INTO torrents_tags
					(TagID, GroupID, UserID, PositiveVotes)
				VALUES
					($TagID, $GroupID, $LoggedUser[ID], 10)
				ON DUPLICATE KEY UPDATE
					PositiveVotes = PositiveVotes + 1;
			");
        }
    }
}

//******************************************************************************//
//--------------- Add the log scores to the DB ---------------------------------//

// Use this section to control freeleeches
$T['FreeLeech'] = 0;
$T['FreeLeechType'] = 1;
if (in_array($Properties['Processing'], ['Untouched', 'DIY', 'Remux', 'BD25', 'BD66', 'BD50', 'BD100', 'DVD9', 'DVD5'])) {
    $T['FreeLeech'] = 11;
} else {
    $T['FreeLeech'] = 12;
}
// 20% 几率免费 
$isFree = random_int(0, 100);
if ($isFree < 20) {
    $T['FreeLeech'] = 1;
}
// 自制或者自购直接免费
if ($Properties['Diy'] || $Properties['Buy']) {
    $T['FreeLeech'] = 1;
}
// limit free
$FreeEndTime = time_plus(3600 * 48);



$Checked = 0;
// Torrent

if ($Type == 'Movies') {
    $Slot = TorrentSlot::CalSlot($Properties);
    $DB->query("
	INSERT INTO torrents
		(GroupID, UserID,
		RemasterYear, RemasterTitle,
		Scene, Jinzhuan, Diy, Buy, Allow, info_hash, FileCount, FileList,
		FilePath, Size, Time, Description, FreeTorrent, FreeLeechType, Checked, NotMainMovie, Source, Codec, Container, Resolution, Subtitles, Makers, Processing, RemasterCustomTitle, ChineseDubbed, SpecialSub, MediaInfo, Note, SubtitleType, Slot)
	VALUES
		($GroupID, $LoggedUser[ID], 
		$T[RemasterYear], $T[RemasterTitle],
		$T[Scene], $T[Jinzhuan],  $T[Diy],  $T[Buy],  $T[Allow], '" . db_string($InfoHash) . "', $NumFiles, '$FileString',
		'$FilePath', $TotalSize, '" . sqltime() . "', $T[TorrentDescription], '$T[FreeLeech]', '$T[FreeLeechType]', $Checked, $T[NotMainMovie], $T[Source], $T[Codec], $T[Container], $T[Resolution], $T[Subtitles], $T[Makers], $T[Processing], $T[RemasterCustomTitle], $T[ChineseDubbed], $T[SpecialSub], $T[MediaInfo], $T[Note], $T[SubtitleType], $Slot)");
}


$Cache->increment('stats_torrent_count');
$TorrentID = $DB->inserted_id();

$DB->query("INSERT INTO `freetorrents_timed`(`TorrentID`, `EndTime`) VALUES ($TorrentID,'$FreeEndTime') ON DUPLICATE KEY UPDATE EndTime=VALUES(EndTime)");


Tracker::update_tracker('add_torrent', array('id' => $TorrentID, 'info_hash' => rawurlencode($InfoHash), 'freetorrent' => $T['FreeLeech']));
$Debug->set_flag('upload: ocelot updated');
// Prevent deletion of this torrent until the rest of the upload process is done
// (expire the key after 10 minutes to prevent locking it for too long in case there's a fatal error below)
$Cache->cache_value("torrent_{$TorrentID}_lock", true, 600);

//******************************************************************************//
//--------------- Write FirstTorrent       -------------------------------------------//

$FirstTorrent = $TotalSize > 2 * 1024 * 1024 * 1024 ? 1 : $TorrentID;
$DB->query("update users_main set firsttorrent=IF(firsttorrent = 0, $FirstTorrent, firsttorrent) ,TotalUploads=TotalUploads+1 where id=" . $LoggedUser['ID']);

//******************************************************************************//
//--------------- Write torrent file -------------------------------------------//

$DB->query("
	INSERT INTO torrents_files (TorrentID, File)
	VALUES ($TorrentID, '$TorEnc')");
Misc::write_log("Torrent $TorrentID ($LogName) was uploaded by " . $LoggedUser['Username']);
if ($Checked) {
    Misc::write_log("Torrent $TorrentID was auto checked");
}
Torrents::write_group_log($GroupID, $TorrentID, $LoggedUser['ID'], 'uploaded (' . number_format($TotalSize / (1024 * 1024), 2) . ' MB)', 0);

Torrents::update_hash($GroupID);
$Debug->set_flag('upload: sphinx updated');
$Properties['ID'] = $TorrentID;
$Properties['FreeTorrent'] = $T['FreeLeech'];
$IRCMessage = Torrents::build_irc_msg($LoggedUser['Username'], $Properties);
// ENT_QUOTES is needed to decode single quotes/apostrophes
send_irc('PRIVMSG ' . BOT_ANNOUNCE_CHAN . ' :' . html_entity_decode($IRCMessage, ENT_QUOTES));
$Debug->set_flag('upload: announced on irc');


//******************************************************************************//
//--------------- Give Bonus Points  -------------------------------------------//

if (G::$LoggedUser['DisablePoints'] == 0) {
    $BonusPoints = 300;
    $DB->query("UPDATE users_main SET BonusPoints = BonusPoints + {$BonusPoints} WHERE ID=" . $LoggedUser['ID']);
    $Cache->delete_value('user_stats_' . $LoggedUser['ID']);
}

//******************************************************************************//
//--------------- Stupid Recent Uploads ----------------------------------------//

if (trim($Properties['Image']) != '') {
    $RecentUploads = $Cache->get_value("recent_uploads_$UserID");
    if (is_array($RecentUploads)) {
        do {
            foreach ($RecentUploads as $Item) {
                if ($Item['ID'] == $GroupID) {
                    break 2;
                }
            }

            // Only reached if no matching GroupIDs in the cache already.
            if (count($RecentUploads) === 5) {
                array_pop($RecentUploads);
            }
            array_unshift($RecentUploads, array(
                'ID' => $GroupID,
                'Name' => trim($Properties['Name']),
                'SubName' => trim($Properties['SubName']),
                'Year' => trim($Properties['Year']),
                'WikiImage' => trim($Properties['Image'])
            ));
            $Cache->cache_value("recent_uploads_$UserID", $RecentUploads, 0);
        } while (0);
    }
}

if ($Properties['NoSub']) {
    $DB->query("
        INSERT INTO torrents_no_sub
        VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "')");
}

if ($Properties['HardSub']) {
    $DB->query("
        INSERT INTO torrents_hard_sub
        VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "')");
}

if (isset($Properties['BadFolders'])) {
    $DB->query("
        INSERT INTO torrents_bad_folders
        VALUES ($TorrentID, $LoggedUser[ID], '" . sqltime() . "')");
}

//******************************************************************************//
//--------------- Post-processing ----------------------------------------------//
/* Because tracker updates and notifications can be slow, we're
 * redirecting the user to the destination page and flushing the buffers
 * to make it seem like the PHP process is working in the background.
 */

if ($PublicTorrent || $UnsourcedTorrent) {
    View::show_header(Lang::get('upload', 'header_warning'), '', 'PageUploadHandle');
?>
    <h1><?= Lang::get('upload', 'upload_handle_warning') ?></h1>
    <p><?= Lang::get('upload', 'need_download_new_torrent1') ?><a href="torrents.php?id=<?= $GroupID ?>&torrentid=<?= $TorrentID ?>"><?= Lang::get('upload', 'here') ?></a><?= Lang::get('upload', 'need_download_new_torrent2') ?></p>
<? View::show_footer();
} elseif ($RequestID) {
    header("Location: requests.php?action=takefill&requestid=$RequestID&torrentid=$TorrentID&auth=" . $LoggedUser['AuthKey']);
} else {
    header("Location: torrents.php?id=$GroupID");
}
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    ignore_user_abort(true);
    ob_flush();
    flush();
    ob_start(); // So we don't keep sending data to the client
}



// Manage notifications
$Title = Torrents::display_simple_group_name($Properties, null, false);
if ($Properties['ReleaseType'] > 0) {
    $Title .= ' [' . Lang::get('torrents', 'release_types')[$Properties['ReleaseType']] . ']';
}
$Details = '';
$Details .= trim($Properties['Codec']) . ' / ' . trim($Properties['Source']) . ' / ' . trim($Properties['Resolution']) . ' / ' . trim($Properties['Container']) . ' / ' . trim($Properties['Processing']);
if ($Properties['Scene'] == '1') {
    $Details .= ' / Scene';
}

$RemasterTitleRSS = explode(' / ', $Properties['RemasterTitle']);
foreach ($RemasterTitleRSS as $RT) {
    if ($RT) {
        $Details .= " / " . EditionInfo::text(trim($RT));
    }
}

if ($T['FreeLeech'] == '1') {
    $Details .= ' / Freeleech!';
} else if ($T['FreeLeech'] == '2') {
    $Details .= ' / Neutral Leech!';
} else if ($T['FreeLeech'] == '11') {
    $Details .= ' / 25% off!';
} else if ($T['FreeLeech'] == '12') {
    $Details .= ' / 50% off!';
} else if ($T['FreeLeech'] == '13') {
    $Details .= ' / 75% off!';
}

if ($Details !== "") {
    $Title .= " - " . $Details;
}


// For RSS
$Item = $Feed->item($Title, html_entity_decode(Text::strip_bbcode($Body)), 'torrents.php?action=download&amp;authkey=[[AUTHKEY]]&amp;torrent_pass=[[PASSKEY]]&amp;id=' . $TorrentID, $LoggedUser['Username'], 'torrents.php?id=' . $GroupID, trim($Properties['TagList']));


//Notifications
$SQL = "
	SELECT unf.ID, unf.UserID, torrent_pass
	FROM users_notify_filters AS unf
		JOIN users_main AS um ON um.ID = unf.UserID
	WHERE um.Enabled = '1'";
if (empty($ArtistsUnescaped)) {
    $ArtistsUnescaped = $ArtistForm;
}
if (!empty($ArtistsUnescaped)) {
    $ArtistNameList = array();
    foreach ($ArtistsUnescaped as $Importance => $Artists) {
        foreach ($Artists as $Artist) {
            $ArtistNameList[] = "Artists LIKE '%|" . db_string(str_replace('\\', '\\\\', $Artist['name']), true) . "|%'";
            $ArtistNameList[] = "Artists LIKE '%|" . db_string(str_replace('\\', '\\\\', $Artist['cname']), true) . "|%'";
        }
    }
    // Don't add notification if >2 main artists or if tracked artist isn't a main artist
    $SQL .= " AND (";
    $SQL .= implode(' OR ', $ArtistNameList);
    $SQL .= " OR Artists = '') AND (";
} else {
    $SQL .= "AND (Artists = '') AND (";
}

reset($Tags);
$TagSQL = array();
$NotTagSQL = array();
foreach ($Tags as $Tag) {
    $TagSQL[] = " Tags LIKE '%|" . db_string(trim($Tag)) . "|%' ";
    $NotTagSQL[] = " NotTags LIKE '%|" . db_string(trim($Tag)) . "|%' ";
}
$TagSQL[] = "Tags = ''";
$SQL .= implode(' OR ', $TagSQL);

$SQL .= ") AND !(" . implode(' OR ', $NotTagSQL) . ')';

$SQL .= " AND (Categories LIKE '%|" . db_string(trim($Type)) . "|%' OR Categories = '') ";


if ($Properties['ReleaseType']) {
    $SQL .= " AND (ReleaseTypes LIKE '%|" . db_string($Properties['ReleaseType']) . "|%' OR ReleaseTypes = '') ";
} else {
    $SQL .= " AND (ReleaseTypes = '') ";
}

/*
    Notify based on the following:
        1. The torrent must match the formatbitrate filter on the notification
        2. If they set NewGroupsOnly to 1, it must also be the first torrent in the group to match the formatbitrate filter on the notification
*/


if ($Properties['Codec']) {
    $NotifyCodec = Torrents::parse_codec($Properties['Codec']);
    $SQL .= " AND (Codecs LIKE '%|" . db_string(trim($NotifyCodec)) . "|%' OR Codecs = '') ";
} else {
    $SQL .= " AND (Codecs = '') ";
}

if ($Properties['Source']) {
    $NotifySource = Torrents::parse_source($Properties['Source']);
    $SQL .= " AND (Sources LIKE '%|" . db_string(trim($NotifySource)) . "|%' OR Sources = '') ";
} else {
    $SQL .= " AND (Sources = '') ";
}

if ($Properties['Resolution']) {
    $NotifyResolution = Torrents::parse_resolution($Properties['Resolution']);
    $SQL .= " AND (Resolutions LIKE '%|" . db_string(trim($NotifyResolution)) . "|%' OR Resolutions = '') ";
} else {
    $SQL .= " AND (Resolutions = '') ";
}

if ($Properties['Container']) {
    $NotifyContainer = Torrents::parse_container($Properties['Container']);
    $SQL .= " AND (Containers LIKE '%|" . db_string(trim($NotifyContainer)) . "|%' OR Containers = '') ";
} else {
    $SQL .= " AND (Containers = '') ";
}

if ($Properties['Processing']) {
    $NotifyProcessing = $Properties['Processing'];
    if ($NotifyProcessing == '---') {
        $NotifyProcessing = 'Encode';
    }
    $SQL .= " AND (Processings LIKE '%|" . db_string(trim($NotifyProcessing)) . "|%' OR Processings = '') ";
} else {
    $SQL .= " AND (Processings = '') ";
}

if ($Properties['Container']) {
    $NotifyContainer = Torrents::parse_container($Properties['Container']);
    $SQL .= " AND (Containers LIKE '%|" . db_string(trim($NotifyContainer)) . "|%' OR Containers = '') ";
} else {
    $SQL .= " AND (Containers = '') ";
}
if (Torrents::global_freeleech()) {
    $SQL .= " AND (FreeTorrents LIKE '%|" . db_string('1') . "|%' OR FreeTorrents = '') ";
} else if ($T['FreeLeech']) {
    $SQL .= " AND (FreeTorrents LIKE '%|" . db_string(trim($T['FreeLeech'])) . "|%' OR FreeTorrents = '') ";
} else {
    $SQL .= " AND (FreeTorrents = '') ";
}

// Either they aren't using NewGroupsOnly
if (!$IsNewGroup) {
    $SQL .= "AND (NewGroupsOnly = '0' )";
}

$SQL .= " AND (('" . db_string(trim($TotalSize)) . "' BETWEEN FromSize AND ToSize)
			OR (FromSize = 0 AND ToSize = 0)
            OR ('" . db_string(trim($TotalSize)) . "' > FromSize AND ToSize = 0)) ";

if ($Properties['Year'] && $Properties['RemasterYear']) {
    $SQL .= " AND (('" . db_string(trim($Properties['Year'])) . "' BETWEEN FromYear AND ToYear)
			OR ('" . db_string(trim($Properties['RemasterYear'])) . "' BETWEEN FromYear AND ToYear)
			OR (FromYear = 0 AND ToYear = 0)) ";
} elseif ($Properties['Year'] || $Properties['RemasterYear']) {
    $SQL .= " AND (('" . db_string(trim(Max($Properties['Year'], $Properties['RemasterYear']))) . "' BETWEEN FromYear AND ToYear)
			OR (FromYear = 0 AND ToYear = 0)) ";
} else {
    $SQL .= " AND (FromYear = 0 AND ToYear = 0) ";
}


if ($OMDBData->imdbRating && $OMDBData->imdbRating != 'N/A') {
    $SQL .= " AND (" .  $OMDBData->imdbRating . " > FromIMDBRating OR FromIMDBRating = 0)";
}

if ($OMDBData->Country) {
    foreach (explode(',', $OMDBData->Country) as $R) {
        $RegionSQL[] = " Regions LIKE '%|" . db_string(trim($R)) . "|%' ";
    }
}
$RegionSQL[] = "Regions = ''";
$SQL .= " AND (" . implode(' OR ', $RegionSQL) . ") ";

if ($OMDBData->Language) {
    foreach (explode(',', $OMDBData->Language) as $L) {
        $LanguageSQL[] = " Languages LIKE '%|" . db_string(trim($L)) . "|%' ";
    }
}
$LanguageSQL[] = "Languages = ''";
$SQL .= " AND (" . implode(' OR ', $LanguageSQL) . ") ";


if ($Properties['RemasterTitle']) {
    $RemasterTitleNotify = explode(' / ', $Properties['RemasterTitle']);
    foreach ($RemasterTitleNotify as $RTN) {
        $RemasterTitleSQL[] = " RemasterTitles LIKE '%|" . db_string(trim(EditionInfo::text(trim($RTN)))) . "|%' ";
    }
}
$RemasterTitleSQL[] = "RemasterTitles = ''";
$SQL .= " AND (" . implode(' OR ', $RemasterTitleSQL) . ") ";


// $SQL .= " AND UserID != '".$LoggedUser['ID']."' ";

$SQL .= " AND (Users LIKE '%|" . $LoggedUser['ID'] . "|%' OR Users = '') ";
$SQL .= " AND !(NotUsers LIKE '%|" . $LoggedUser['ID'] . "|%') ";

$SQL .= " AND UserID != '" . $LoggedUser['ID'] . "' ";
$DB->query($SQL);
$Debug->set_flag('upload: notification query finished');

if ($DB->has_results()) {
    $UserArray = $DB->to_array('UserID');
    $FilterArray = $DB->to_array('ID');

    $InsertSQL = '
		INSERT IGNORE INTO users_notify_torrents (UserID, GroupID, TorrentID, FilterID)
		VALUES ';
    $Rows = array();
    foreach ($UserArray as $User) {
        list($FilterID, $UserID, $Passkey) = $User;
        $Rows[] = "('$UserID', '$GroupID', '$TorrentID', '$FilterID')";
        $Feed->populate("torrents_notify_$Passkey", $Item);
        $Cache->delete_value("notifications_new_$UserID");
    }
    $InsertSQL .= implode(',', $Rows);
    $DB->query($InsertSQL);
    $Debug->set_flag('upload: notification inserts finished');

    foreach ($FilterArray as $Filter) {
        list($FilterID, $UserID, $Passkey) = $Filter;
        $Feed->populate("torrents_notify_{$FilterID}_$Passkey", $Item);
    }
}

// RSS for bookmarks
$DB->query("
	SELECT u.ID, u.torrent_pass
	FROM users_main AS u
		JOIN bookmarks_torrents AS b ON b.UserID = u.ID
	WHERE b.GroupID = $GroupID");
while (list($UserID, $Passkey) = $DB->next_record()) {
    $Feed->populate("torrents_bookmarks_t_$Passkey", $Item);
}

$Feed->populate('torrents_all', $Item);
$Debug->set_flag('upload: notifications handled');

// Clear cache
$Cache->delete_value("torrents_details_$GroupID");

// Allow deletion of this torrent now
$Cache->delete_value("torrent_{$TorrentID}_lock");
