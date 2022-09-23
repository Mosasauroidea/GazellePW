<?php

namespace Gazelle\API;
use Gazelle\Torrent\EditionInfo;
use Gazelle\Torrent\TorrentSlot;
use Gazelle\Util\Time;

ini_set('max_file_uploads', 100);
define('MAX_FILENAME_LENGTH', 255);

class Upload extends AbstractAPI {

    public function run() {
        return $this->uploadTorrent();
    }

    private function uploadTorrent() {
       

        include(CONFIG['SERVER_ROOT'] . '/classes/validate.class.php');
        include(CONFIG['SERVER_ROOT'] . '/classes/feed.class.php');
        include(CONFIG['SERVER_ROOT'] . '/classes/regex.php');
        include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
        include(CONFIG['SERVER_ROOT'] . '/classes/file_checker.class.php');

        $Feed = new \FEED;

        //******************************************************************************//
        //--------------- Set $Properties array ----------------------------------------//
        // This is used if the form doesn't validate, and when the time comes to enter  //
        // it into the database.                                                        //
        $json_response = array();
        
        $Properties = array();
        $Properties['GroupID'] = $_POST['groupid'];
        if (!empty($_POST['imdb'])) {
            preg_match('/' . IMDB_REGEX . '/', $_POST['imdb'], $IMDBMatch);
            if ($IMDBMatch[1]) {
                $Properties['IMDBID'] = $IMDBMatch[1];
            } else {
                $json_response["error"] = "Invalid IMDb id provided";
                return $json_response;
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
        $Properties['GroupMainDescription'] = trim($_POST['maindesc']);

        $Properties['RemasterTitle'] = trim($_POST['remaster_title']);
        if (!EditionInfo::validate($Properties['RemasterTitle'])) {
            $json_response["error"] = "Invalid remaster title provided";
            return $json_response;
        }
        $Properties['RemasterCustomTitle'] = html_entity_decode($_POST['remaster_custom_title'], ENT_QUOTES);
        $Properties['TorrentDescription'] = $_POST['release_desc'];

        if (!empty($_POST['requestid'])) {
            $RequestID = $_POST['requestid'];
            $Properties['RequestID'] = $RequestID;
        }
        $Properties['MediaInfo']  = $_POST['mediainfo'] ? json_encode($_POST['mediainfo']) : null;
        $Properties['Note'] = isset($POST['staff_note']) ? trim($_POST['staff_note']) : "";

        $Type = $Categories[(int)$_POST['type']];
        $TypeID = $_POST['type'] + 1;
        $GroupID = $Properties['GroupID'];
        $Artists = $_POST['artists'];
        $Importance = $_POST['importance'];
        $ArtistIMDBIDs = $_POST['artist_ids'];
        $ArtistSubName = $_POST['artists_sub'];
        $IsNewGroup = empty($GroupID);

        //******************************************************************************//
        //--------------- Validate data in upload form ---------------------------------//

        $Validate = new \VALIDATE;
        $Validate->SetFields(
            'codec',
            '1',
            'string',
            'Please select a valid format.'
        );
        $Validate->SetFields(
            'resolution',
            '1',
            'string',
            'Please select a valid format.'
        );
        $Validate->SetFields(
            'container',
            '1',
            'string',
            'Please select a valid format.'
        );
        $Validate->SetFields(
            'source',
            '1',
            'string',
            'Please select a valid format.'
        );
        if ($IsNewGroup) {
            $Validate->SetFields(
                'name',
                '1',
                'string',
                'Title must be between 2 and 200 characters.'
            );
        }

        $Err = $Validate->ValidateForm($_POST); // Validate the form

        $File = $_FILES['file_input']; // This is our torrent file
        $TorrentName = $File['tmp_name'];

        if (!is_uploaded_file($TorrentName) || !filesize($TorrentName)) {
            $Err = 'No torrent file uploaded, or file is empty.';
        } elseif (substr(strtolower($File['name']), strlen($File['name']) - strlen('.torrent')) !== '.torrent') {
            $Err = "You seem to have put something other than a torrent file into the upload field. -> " . "(" . $File['name'] . ")";
        }
        if ($IsNewGroup) {
            for ($i = 0, $il = count($Artists); $i < $il; $i++) {
                if (trim($Artists[$i]) != '') {
                    if ($Importance[$i] == \Artists::Director) {
                        $MainArtistCount++;
                    }
                    $ArtistForm[$Importance[$i]][] = array('Name' => db_string($Artists[$i]), 'IMDBID' => isset($ArtistIMDBIDs[$i]) ? $ArtistIMDBIDs[$i] : null, 'SubName' => db_string($ArtistSubName[$i]));
                }
            }
            if ($MainArtistCount < 1) {
                $Err = 'Please enter at least one main artist';
            }
        }

        if ($Properties['ReleaseType'] == 3 || $Properties['ReleaseType'] == 4) {
            $Err = 'Please select a valid format.';
        }

        if ($Err) { // Show the upload form, with the data the user entered
            $json_response["error"] = $Err;
            return $json_response;
        }

        // Strip out Amazon's padding
        \ImageTools::blacklisted($Properties['Image']);

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

        $Tor = new \BencodeTorrent($TorrentName, true);
        $PublicTorrent = $Tor->make_private(); // The torrent is now private.
        $UnsourcedTorrent = $Tor->set_source(); // The source is now CONFIG['TORRENT_SOURCE']
        $TorEnc = db_string($Tor->encode());
        $InfoHash = pack('H*', $Tor->info_hash());

        $this->db->query("
            SELECT ID
            FROM torrents
            WHERE info_hash = '" . db_string($InfoHash) . "'");
        if ($this->db->has_results()) {
            list($ID) = $this->db->next_record();
            $this->db->query("
                SELECT TorrentID
                FROM torrents_files
                WHERE TorrentID = $ID");
            if ($this->db->has_results()) {
                $Err = 'The exact same torrent file already exists on the site!';
            } else {
                // A lost torrent
                $this->db->query("
                    INSERT INTO torrents_files (TorrentID, File)
                    VALUES ($ID, '$TorEnc')");
                $Err = 'Thank you for fixing this torrent!'; # TODO: what the hell is this????
            }
        }

        if (isset($Tor->Dec['encrypted_files'])) {
            $Err = 'This torrent contains an encrypted file list which is not supported here.';
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
            $TmpFileList[] = \Torrents::filelist_format_file($File);
        }
        if (count($TooLongPaths) > 0) {
            $Names = implode(' <br />', $TooLongPaths);
            $Err = 'The torrent contained one or more files with too long of a name: ' . "$Names";
        }
        $FilePath = db_string($DirName);
        $FileString = db_string(implode("\n", $TmpFileList));
        // $Debug->set_flag('upload: torrent decoded');


        if (!empty($Err)) { // Show the upload form, with the data the user entered
            $json_response["error"] = $Err;
            return $json_response;
        }

        //******************************************************************************//
        //--------------- Start database stuff -----------------------------------------//

        $Body = $Properties['GroupDescription'];
        $MainBody = $Properties['GroupMainDescription'];

        // Trickery
        if (!preg_match('/^' . IMAGE_REGEX . '$/i', $Properties['Image'])) {
            $Properties['Image'] = '';
            $T['Image'] = "''";
        }

        //Needs to be here as it isn't set for add format until now
        $Properties['Size'] = $TotalSize;
        $Properties['Group'] = ['SubName' => $Properties['SubName'], 'Name' => $Properties['Name'], 'Year' => $Properties['Year']];
        $LogName = \Torrents::torrent_name($Properties, false);
        //For notifications--take note now whether it's a new group

        // Fetching the userid of the user by api key
        $LoggedUser = $this->cache->get_value("api_apps_{$_GET['api_key']}")[0];
        if (!is_array($LoggedUser)) {
            $json_response["error"] = "Unauthorized Access";
            return $json_response;
        }
        $LoggedUser['ID'] = $LoggedUser['UserID'];

        //----- Start inserts
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
            $OMDBData = \MOVIE::get_omdb_data($Properties['IMDBID']);
            $IMDBRating = $OMDBData->imdbRating && $OMDBData->imdbRating != 'N/A' ? $OMDBData->imdbRating : 'null';
            $Runtime = $OMDBData->Runtime && $OMDBData->Runtime != 'N/A' ? $OMDBData->Runtime : '';
            $Released = $OMDBData->Released && $OMDBData->Released != 'N/A' ? $OMDBData->Released : '';
            $Country = $OMDBData->Country && $OMDBData->Country != 'N/A' ? $OMDBData->Country : '';
            $Language = $OMDBData->Language && $OMDBData->Language != 'N/A' ? $OMDBData->Language : '';
            foreach ($OMDBData->Ratings as $key => $value) {
                if ($value->Source == "Rotten Tomatoes") {
                    $RTRating = $value->Value;
                }
            }
            $DoubanData = \MOVIE::get_douban_data($Properties['IMDBID']);
            $DoubanRating = $DoubanData->rating ? $DoubanData->rating : 'null';
            $DoubanID = $DoubanData->id ? $DoubanData->id : 'null';
            $DoubanVote = $DoubanData->votes ? $DoubanData->votes : 'null';
            $IMDBVote = $OMDBData->imdbVotes && $OMDBData->imdbVotes != 'N/A' ? str_replace(',', '', $OMDBData->imdbVotes) : 'null';
            $RTRating = $RTRating ? $RTRating : '';
        }
        if ($IsNewGroup) {
            $ArtistForm = \Artists::new_artist($ArtistForm, $Properties['IMDBID']);
            // Create torrent group
            $this->db->query(
                "INSERT INTO torrents_group
                    (
                        ArtistID, 
                        CategoryID, 
                        Name, 
                        SubName, 
                        Year,  
                        Time, 
                        WikiBody, 
                        MainWikiBody,
                        WikiImage, 
                        ReleaseType, 
                        IMDBID, 
                        TrailerLink, 
                        IMDBRating, 
                        Duration, 
                        ReleaseDate, 
                        Region, 
                        Language, 
                        RTRating, 
                        DoubanRating, 
                        DoubanID, 
                        DoubanVote, 
                        IMDBVote
                    )
                VALUES
                    (
                        0, 
                        $TypeID, 
                        " . $T['Name'] . ", 
                        " . $T['SubName'] . ", 
                        $T[Year],
                        '" . Time::sqltime() . "', 
                        '" . db_string($Body) . "', 
                        '" . db_string($MainBody) . "', 
                        $T[Image], 
                        $T[ReleaseType], 
                        '" . $IMDBID . "', 
                        $T[TrailerLink], 
                        '" . $IMDBRating . "', 
                        '" . $Runtime . "', 
                        '" . $Released . "', 
                        '" . $Country . "', 
                        '" . $Language . "', 
                        '" . $RTRating . "', 
                        " . $DoubanRating . ", 
                        " . $DoubanID . ", 
                        " . $DoubanVote . ", 
                        " . $IMDBVote .
                    ")"
            );
            $GroupID = $this->db->inserted_id();
            foreach ($ArtistForm as $Importance => $Artists) {
                foreach ($Artists as $Num => $Artist) {
                    $this->db->query(
                        "INSERT IGNORE INTO torrents_artists (GroupID, ArtistID, UserID, Importance, Credit, `Order`)
                            VALUES ($GroupID, " . $Artist['ArtistID'] . ', ' . $LoggedUser['ID'] . ", '$Importance', true, $Num)"
                    );
                }
            }
            $this->cache->increment('stats_album_count');
            $this->cache->increment('stats_group_count');
            $this->db->query(
                "INSERT INTO wiki_torrents
                    (
                        PageID, 
                        Body, 
                        MainBody,
                        UserID, 
                        Summary, 
                        Time, 
                        Image, 
                        IMDBID, 
                        DoubanID, 
                        Year, 
                        Name, 
                        SubName,
                        ReleaseType)
                VALUES
                    (
                        $GroupID, 
                        $T[GroupDescription], 
                        $T[GroupMainDescription], 
                        $LoggedUser[ID], 
                        'Uploaded new torrent', '" . Time::sqltime() . "', 
                        $T[Image], 
                        '" . $IMDBID . "', 
                        " . $DoubanID . ", 
                        " . $T['Year'] . ",
                        " . $T['Name'] . ",
                        " . $T['SubName'] . ",
                        " . $T['ReleaseType'] . "
                        " .
                    ")"
            );
            $RevisionID = $this->db->inserted_id();

            // Revision ID
            $this->db->query(
                "UPDATE torrents_group
                SET RevisionID = '$RevisionID'
                WHERE ID = $GroupID"
            );
        } else {
            $this->cache->delete_value("torrent_group_$GroupID");
            $this->cache->delete_value("torrents_details_$GroupID");
            $this->cache->delete_value("detail_files_$GroupID");
            $this->db->query(
                "SELECT ReleaseType
                FROM torrents_group
                WHERE ID = '$GroupID'"
            );
            list($Properties['ReleaseType']) = $this->db->next_record();
        }

        // Tags
        $Tags = explode(',', $Properties['TagList']);
        $Tags = \Tags::main_name($Tags);
        $tagMan = new \Gazelle\Manager\Tag;
        if (!$Properties['GroupID']) {
            foreach ($Tags as $Tag) {
                $TagID = $tagMan->create($Tag, $LoggedUser['ID']);
                if ($TagID) {
                    $this->db->query("
                        INSERT INTO torrents_tags
                            (TagID, GroupID, UserID, PositiveVotes)
                        VALUES
                            ($TagID, $GroupID, $LoggedUser[ID], 10)
                        ON DUPLICATE KEY UPDATE
                            PositiveVotes = PositiveVotes + 1;
                    ");
                    \Tags::clear_all_cache();
                }
            }
        }

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
        $FreeEndTime = Time::timePlus(3600 * 48);

        $Checked = 0;
        // Torrent

        $Slot = TorrentSlot::CalSlot($Properties);
        echo $LoggedUser["ID"];
        echo "$LoggedUser[ID]";

        $this->db->query(
            "INSERT INTO torrents
                (GroupID, UserID,
                RemasterYear, RemasterTitle,
                Scene, Jinzhuan, Diy, Buy, Allow, info_hash, FileCount, FileList,
                FilePath, Size, Time, Description, FreeTorrent, FreeLeechType, Checked, NotMainMovie, Source, Codec, Container, Resolution, Subtitles, Makers, Processing, RemasterCustomTitle, ChineseDubbed, SpecialSub, MediaInfo, Note, SubtitleType, Slot)
            VALUES
                ($GroupID, $LoggedUser[ID], 
                $T[RemasterYear], $T[RemasterTitle],
                $T[Scene], $T[Jinzhuan],  $T[Diy],  $T[Buy],  $T[Allow], '" . db_string($InfoHash) . "', $NumFiles, '$FileString',
                '$FilePath', $TotalSize, '" . Time::sqlTime() . "', $T[TorrentDescription], '$T[FreeLeech]', '$T[FreeLeechType]', $Checked, $T[NotMainMovie], $T[Source], $T[Codec], $T[Container], $T[Resolution], $T[Subtitles], $T[Makers], $T[Processing], $T[RemasterCustomTitle], $T[ChineseDubbed], $T[SpecialSub], $T[MediaInfo], $T[Note], $T[SubtitleType], $Slot)"
        );


        $this->cache->increment('stats_torrent_count');
        $TorrentID = $this->db->inserted_id();

        $this->db->query(
            "INSERT INTO `freetorrents_timed`(`TorrentID`, `EndTime`) VALUES ($TorrentID,'$FreeEndTime') ON DUPLICATE KEY UPDATE EndTime=VALUES(EndTime)"
        );


        \Tracker::update_tracker('add_torrent', array('id' => $TorrentID, 'info_hash' => rawurlencode($InfoHash), 'freetorrent' => $T['FreeLeech']));
        // $Debug->set_flag('upload: ocelot updated');
        // Prevent deletion of this torrent until the rest of the upload process is done
        // (expire the key after 10 minutes to prevent locking it for too long in case there's a fatal error below)
        $this->cache->cache_value("torrent_{$TorrentID}_lock", true, 600);

        //******************************************************************************//
        //--------------- Write FirstTorrent       -------------------------------------------//

        $FirstTorrent = $TotalSize > 2 * 1024 * 1024 * 1024 ? 1 : $TorrentID;
        $this->db->query("update users_main set firsttorrent=IF(firsttorrent = 0, $FirstTorrent, firsttorrent) ,TotalUploads=TotalUploads+1 where id=" . $LoggedUser['ID']);

        //******************************************************************************//
        //--------------- Write torrent file -------------------------------------------//

        $this->db->query("
            INSERT INTO torrents_files (TorrentID, File)
            VALUES ($TorrentID, '$TorEnc')");
        \Misc::write_log_with_time("Torrent $TorrentID ($LogName) was uploaded by " . $LoggedUser['Username']);
        if ($Checked) {
            \Misc::write_log_with_time("Torrent $TorrentID was auto checked");
        }
        \Torrents::write_group_log_with_time($GroupID, $TorrentID, $LoggedUser['ID'], "uploaded (" . number_format($TotalSize / (1024 * 1024 * 1024), 2) . ' GB)', 0);

        \Torrents::update_hash($GroupID);
        // $Debug->set_flag('upload: sphinx updated');
        $Properties['ID'] = $TorrentID;
        $Properties['FreeTorrent'] = $T['FreeLeech'];
        $IRCMessage = \Torrents::build_irc_msg($LoggedUser['Username'], $Properties);
        // ENT_QUOTES is needed to decode single quotes/apostrophes
        send_irc('PRIVMSG ' . CONFIG['BOT_ANNOUNCE_CHAN'] . ' :' . html_entity_decode($IRCMessage, ENT_QUOTES));
        // $Debug->set_flag('upload: announced on irc');


        //******************************************************************************//
        //--------------- Give Bonus Points  -------------------------------------------//

        // TODO: Cannot give bonus points since bonus points. Since DisablePoints is not available
        // in api applications table. To support bonus points via api upload, the DisablePoints needs to be fetched from DB
        // or grant bonus points for all users via api upload.

        // if (\G::$LoggedUser['DisablePoints'] == 0) {
        //     $BonusPoints = 300;
        //     $this->db->query("UPDATE users_main SET BonusPoints = BonusPoints + {$BonusPoints} WHERE ID=" . $LoggedUser['ID']);
        //     $this->cache->delete_value('user_stats_' . $LoggedUser['ID']);
        // }

        //******************************************************************************//
        //--------------- Stupid Recent Uploads ----------------------------------------//

        if (trim($Properties['Image']) != '') {
            $RecentUploads = $this->cache->get_value("recent_uploads_$UserID");
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
                        'TorrentID' => $GroupID,
                        'Name' => trim($Properties['Name']),
                        'SubName' => trim($Properties['SubName']),
                        'Year' => trim($Properties['Year']),
                        'WikiImage' => trim($Properties['Image'])
                    ));
                    $this->cache->cache_value("recent_uploads_$UserID", $RecentUploads, 0);
                } while (0);
            }
        }

        if ($Properties['NoSub']) {
            $this->db->query("
                INSERT INTO torrents_no_sub
                VALUES ($TorrentID, $LoggedUser[ID], '" . Time::sqltime() . "')");
        }

        if ($Properties['HardSub']) {
            $this->db->query("
                INSERT INTO torrents_hard_sub
                VALUES ($TorrentID, $LoggedUser[ID], '" . Time::sqltime() . "')");
        }

        if (isset($Properties['BadFolders'])) {
            $this->db->query("
                INSERT INTO torrents_bad_folders
                VALUES ($TorrentID, $LoggedUser[ID], '" . Time::sqltime() . "')");
        }

        $release_types_array = array("Feature Film", "Short Film", "Miniseries", "Stand-up Comedy", "Live Performance", "Movie Collection");

        // Manage notifications
        $Title = \Torrents::display_simple_group_name($Properties, null, false);
        if ($Properties['ReleaseType'] > 0) {
            $Title .= ' [' . $release_types_array[$Properties['ReleaseType']] . ']';
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

        if ($T['FreeLeech'] == \Torrents::FREE) {
            $Details .= ' / Freeleech!';
        } else if ($T['FreeLeech'] == \Torrents::Neutral) {
            $Details .= ' / Neutral Leech!';
        } else if ($T['FreeLeech'] == \Torrents::OneFourthOff) {
            $Details .= ' / 25% off!';
        } else if ($T['FreeLeech'] == \Torrents::TwoFourthOff) {
            $Details .= ' / 50% off!';
        } else if ($T['FreeLeech'] == \Torrents::ThreeFourthOff) {
            $Details .= ' / 75% off!';
        }

        if ($Details !== "") {
            $Title .= " - " . $Details;
        }


        // For RSS
        $Item = $Feed->item($Title, html_entity_decode(\Text::strip_bbcode($Body)), 'torrents.php?action=download&amp;authkey=[[AUTHKEY]]&amp;torrent_pass=[[PASSKEY]]&amp;id=' . $TorrentID, $LoggedUser['Username'], 'torrents.php?id=' . $GroupID, trim($Properties['TagList']));


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
                    $ArtistNameList[] = "Artists LIKE '%|" . db_string(str_replace('\\', '\\\\', $Artist['Name']), true) . "|%'";
                    $ArtistNameList[] = "Artists LIKE '%|" . db_string(str_replace('\\', '\\\\', $Artist['SubName']), true) . "|%'";
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
            $NotifyCodec = \Torrents::parse_codec($Properties['Codec']);
            $SQL .= " AND (Codecs LIKE '%|" . db_string(trim($NotifyCodec)) . "|%' OR Codecs = '') ";
        } else {
            $SQL .= " AND (Codecs = '') ";
        }

        if ($Properties['Source']) {
            $NotifySource = \Torrents::parse_source($Properties['Source']);
            $SQL .= " AND (Sources LIKE '%|" . db_string(trim($NotifySource)) . "|%' OR Sources = '') ";
        } else {
            $SQL .= " AND (Sources = '') ";
        }

        if ($Properties['Resolution']) {
            $NotifyResolution = \Torrents::parse_resolution($Properties['Resolution']);
            $SQL .= " AND (Resolutions LIKE '%|" . db_string(trim($NotifyResolution)) . "|%' OR Resolutions = '') ";
        } else {
            $SQL .= " AND (Resolutions = '') ";
        }

        if ($Properties['Container']) {
            $NotifyContainer = \Torrents::parse_container($Properties['Container']);
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
            $NotifyContainer = \Torrents::parse_container($Properties['Container']);
            $SQL .= " AND (Containers LIKE '%|" . db_string(trim($NotifyContainer)) . "|%' OR Containers = '') ";
        } else {
            $SQL .= " AND (Containers = '') ";
        }
        if (\Torrents::global_freeleech()) {
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
        $this->db->query($SQL);
        // $Debug->set_flag('upload: notification query finished');

        if ($this->db->has_results()) {
            $UserArray = $this->db->to_array('UserID');
            $FilterArray = $this->db->to_array('ID');

            $InsertSQL = '
                INSERT IGNORE INTO users_notify_torrents (UserID, GroupID, TorrentID, FilterID)
                VALUES ';
            $Rows = array();
            foreach ($UserArray as $User) {
                list($FilterID, $UserID, $Passkey) = $User;
                $Rows[] = "('$UserID', '$GroupID', '$TorrentID', '$FilterID')";
                $Feed->populate("torrents_notify_$Passkey", $Item);
                $this->cache->delete_value("notifications_new_$UserID");
            }
            $InsertSQL .= implode(',', $Rows);
            $this->db->query($InsertSQL);
            // $Debug->set_flag('upload: notification inserts finished');

            foreach ($FilterArray as $Filter) {
                list($FilterID, $UserID, $Passkey) = $Filter;
                $Feed->populate("torrents_notify_{$FilterID}_$Passkey", $Item);
            }
        }

        // RSS for bookmarks
        $this->db->query("
            SELECT u.ID, u.torrent_pass
            FROM users_main AS u
                JOIN bookmarks_torrents AS b ON b.UserID = u.ID
            WHERE b.GroupID = $GroupID");
        while (list($UserID, $Passkey) = $this->db->next_record()) {
            $Feed->populate("torrents_bookmarks_t_$Passkey", $Item);
        }

        $Feed->populate('torrents_all', $Item);
        // $Debug->set_flag('upload: notifications handled');

        // Clear cache
        $this->cache->delete_value("torrents_details_$GroupID");

        // Allow deletion of this torrent now
        $this->cache->delete_value("torrent_{$TorrentID}_lock");

        $json_response["message"] = "Succesfully uploaded torrent";
        return $json_response;

    }
}

