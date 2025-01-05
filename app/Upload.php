<?php

namespace Gazelle;

use Gazelle\Manager\ActionTrigger;
use Gazelle\Manager\Tag;
use VALIDATE, Artists, Format, ImageTools, Movie, Tags, Misc, Tracker, Torrents, Users;
use Gazelle\Util\Time, Gazelle\Torrent\EditionInfo, Gazelle\Torrent\TorrentSlot, Gazelle\Torrent\Notification, Gazelle\Util\FileChecker;

class UploadedTorrent {
    public $GroupID;
    public $TorrentID;
    public $RequestID;
    public $IsPrivate;
}

class Upload extends Base {
    private $validate;
    private $isNewGroup;
    private $properties;
    private $notification;
    private $fileChecker;
    private $useAPI = false;
    private $trigger;

    public function __construct($IsNewGroup, $UseAPI = false) {
        parent::__construct();
        $this->notification = new Notification;
        $this->validate = new VALIDATE;
        $this->isNewGroup = $IsNewGroup;
        $this->fileChecker = new FileChecker;
        $this->useAPI = $UseAPI;
        $this->trigger = new ActionTrigger;
        $this->initValidate();
    }

    public function uploadTorrent($Params, $Files) {
        $this->checkParam($Params, $Files);
        $this->initProperties($Params, $Files);
        // prepare data (artists and movie data)
        if ($this->isNewGroup) {
            $this->createArtists();
            $this->fetchMovieData();
        }

        // core logic upload torrent
        $this->upload();

        $GroupID = $this->properties['GroupID'];
        $TorrentID = $this->properties['TorrentID'];

        $LogName = Torrents::torrent_name($this->properties, false);
        $TotalSize = $this->properties['Size'];
        Misc::write_log("Torrent $TorrentID ($LogName) was uploaded by " . $this->user['Username'] . ($this->useAPI ? ' using the api' : ''));
        Torrents::write_group_log($GroupID, $TorrentID, $this->user['ID'], 'uploaded ' . $this->properties['FilePath'] .  '(' . number_format($TotalSize / (1024 * 1024 * 1024), 2) . ' GiB)', 0);

        $this->clearCache();
        Tracker::update_tracker('add_torrent', array('id' => $TorrentID, 'info_hash' => rawurlencode($this->properties['InfoHash']), 'freetorrent' => $this->properties['FreeLeech']));
        Torrents::update_hash($GroupID);
        // Prevent deletion of this torrent until the rest of the upload process is done
        // (expire the key after 10 minutes to prevent locking it for too long in case there's a fatal error below)
        $this->cache->cache_value("torrent_{$TorrentID}_lock", true, 600);
        $this->updateUser();
        // TODO by qwerty async
        $this->notification->notify($this->properties, $this->isNewGroup);
        $this->trigger->triggerUpload($GroupID, $TorrentID);
        $this->cache->delete_value("torrent_{$TorrentID}_lock");
        return $this->buildUploadedTorrent();
    }

    private function initValidate() {
        global $ReleaseTypes, $Codecs, $Sources, $Containers, $Processings, $Resolutions;
        $this->validate->SetFields('codec', '1', 'inarray', 'codec', ['inarray' => $Codecs]);
        $this->validate->SetFields('resolution', '1', 'inarray', 'resolution', ['inarray' => $Resolutions]);
        $this->validate->SetFields('container', '1', 'inarray', 'container', ['inarray' => $Containers]);
        $this->validate->SetFields('source', '1', 'inarray', 'source', ['inarray' => $Sources]);
        $this->validate->SetFields('processing', '0', 'inarray', 'processing', ['inarray' => $Processings]);

        $this->validate->SetFields('release_desc', '1', 'string', 'allow',  ['maxlength' => 65535]);
        $this->validate->SetFields('staff_note', '0', 'string', 'staff note.', ['maxlength' => 65535]);

        $this->validate->SetFields('subtitle_type', '1', 'inarray', 'subtitle type.', ['inarray' => [1, 2, 3]]);
        $this->validate->SetFields('no_sub', '0', 'inarray', 'no sub', ['inarray' => [0, 1]]);
        $this->validate->SetFields('not_main_movie', '0', 'inarray', 'not main movie', ['inarray' => [0, 1]]);
        $this->validate->SetFields('special_effects_subtitles', '0', 'inarray', 'special effects subtitles', ['inarray' => [0, 1]]);
        $this->validate->SetFields('chinese_dubbed', '0', 'inarray', 'chinese dubbed', ['inarray' => [0, 1]]);
        $this->validate->SetFields('remaster_year', '0', 'number', 'remaster year');
        $this->validate->SetFields('remaster_title', '0', 'string', 'remaster title');
        $this->validate->SetFields('scene', '0', 'inarray', 'scene', ['inarray' => [0, 1]]);
        $this->validate->SetFields('jinzhuan', '0', 'inarray', 'jin zhuan', ['inarray' => [0, 1]]);
        $this->validate->SetFields('diy', '0', 'inarray', 'diy', ['inarray' => [0, 1]]);
        $this->validate->SetFields('buy', '0', 'inarray', 'buy', ['inarray' => [0, 1]]);
        $this->validate->SetFields('allow', '0', 'inarray', 'allow', ['inarray' => [0, 1]]);

        if ($this->isNewGroup) {
            $this->validate->SetFields('name', '1', 'string', 'Title must be between 2 and 200 characters.');
            $this->validate->SetFields('desc', '0', 'string', 'Title must be between 2 and 200 characters.', ['maxlength' => 65535]);
            $this->validate->SetFields('maindesc', '0', 'string', 'Title must be between 2 and 200 characters.', ['maxlength' => 65535]);
            $this->validate->SetFields('image', '1', 'link', 'Image link must be between 2 and 200 characters.');
            $this->validate->SetFields('year', '1', 'number', 'year.');
            $this->validate->SetFields('releasetype', '1', 'inarray', 'release type', ['inarray' => $ReleaseTypes]);
        } else {
            $this->validate->SetFields('groupid', '1', 'number', 'group id');
        }
    }
    // TODO by qwerty strict check
    private function checkParam($Params, $Files) {
        $Err = $this->validate->ValidateForm($Params);
        if ($Err) {
            throw new Exception\InvalidParamException($Err);
        }

        if (!is_array($Params['mediainfo'])) {
            throw new Exception\InvalidParamException('mediainfo');
        }
        foreach ($Params['mediainfo'] as $Idx => $MediaInfo) {
            if (!is_string($MediaInfo)) {
                throw new Exception\InvalidParamException('mediainfo');
            }
        }
        if (!EditionInfo::validate($Params['RemasterTitle'])) {
            throw new Exception\InvalidParamException('edition info');
        }
        if ($this->isNewGroup) {
            $MainArtistCount = 0;
            for ($i = 0, $il = count($Params['artists']); $i < $il; $i++) {
                if (trim($Params['artists'][$i]) != '') {
                    if ($Params['importance'][$i] == Artists::Director) {
                        $MainArtistCount += 1;
                    }
                }
            }
            if ($MainArtistCount < 1) {
                throw new Exception\InvalidParamException('main artist number');
            }
            if (empty($Params['maindesc']) && empty($Params['desc'])) {
                throw new Exception\InvalidParamException('description');
            }
            if (!empty($Params['no_imdb_link'])) {
            } else if (preg_match('/' . IMDB_REGEX . '/', $Params['imdb'])) {
            } else {
                throw new Exception\InvalidParamException('imdb id');
            }
            if (count($Params['artists']) != count($Params['importance']) || count($Params['artists']) != count($Params['artist_ids']) || count($Params['artists']) != count($Params['artists_sub'])) {
                throw new Exception\InvalidParamException('artists info count');
            }
            if (!ImageTools::whitelisted($Params['image'], false)) {
                throw new Exception\InvalidParamException('image host');
            }
            if (count($Params['tags']) < 1) {
                throw new Exception\InvalidParamException('tags');
            }
        }

        $File = $Files['file_input']; // This is our torrent file
        $TorrentName = $File['tmp_name'];

        if (!is_uploaded_file($TorrentName) || !filesize($TorrentName)) {
            throw new Exception\InvalidParamException('No torrent file uploaded, or file is empty.');
        } elseif (substr(strtolower($File['name']), strlen($File['name']) - strlen('.torrent')) !== '.torrent') {
            throw new Exception\InvalidParamException("You seem to have put something other than a torrent file into the upload field. -> " . "(" . $File['name'] . ")");
        }
    }

    private function initProperties($Params, $Files) {
        $properties = array();
        $properties['GroupID'] = $Params['groupid'];
        if (!empty($Params['imdb'])) {
            preg_match('/' . IMDB_REGEX . '/', $Params['imdb'], $IMDBMatch);
            $properties['IMDBID'] = $IMDBMatch[1];
        } else {
            $properties['IMDBID'] = '';
        }
        $properties['Name'] = $Params['name'];
        $properties['SubName'] = isset($Params['subname']) ? $Params['subname'] : '';
        $properties['Year'] = trim($Params['year']);

        $properties['TrailerLink'] = $Params['trailer_link'];
        $properties['TagList'] = $Params['tags'];
        $properties['Body'] = preg_replace("/\r|\n/", "", trim($Params['desc']));
        $properties['MainBody'] = preg_replace("/\r|\n/", "", trim($Params['maindesc']));
        $properties['Image'] = $Params['image'];
        $properties['ReleaseType'] = $Params['releasetype'];

        $properties['Source'] = $Params['source'];
        if ($properties['Source'] == 'Other') {
            $properties['Source'] = $Params['source_other'];
        }
        $properties['Codec'] = $Params['codec'];
        if ($properties['Codec'] == 'Other') {
            $properties['Codec'] = $Params['codec_other'];
        }
        $properties['Container'] = $Params['container'];
        if ($properties['Container'] == 'Other') {
            $properties['Container'] = $Params['container_other'];
        }
        $properties['Resolution'] = $Params['resolution'];
        if ($properties['Resolution'] == 'Other' && $Params['resolution_width'] && $Params['resolution_height']) {
            $properties['Resolution'] = $Params['resolution_width'] . '×' . $Params['resolution_height'];
        }
        $properties['Processing'] = $Params['processing'];
        if ($Params['processing_other']) {
            $properties['Processing'] = $Params['processing_other'];
        }
        if (empty($properties['Processing'])) {
            $properties['Processing'] = '';
        }

        $properties['NotMainMovie'] = isset($Params['not_main_movie']) ? '1' : '0';

        $properties['NoSub'] = isset($Params['no_sub']) ? 1 : 0;
        $properties['HardSub'] = (isset($Params['hardcode_sub'])) ? 1 : 0;
        $properties['BadFolders'] = (isset($Params['bad_folders'])) ? 1 : 0;

        $properties['SubtitleType'] = $Params['subtitle_type'];
        if ($properties['SubtitleType'] == 2) {
            $properties['HardSub'] = 1;
        } else if ($properties['SubtitleType'] == 3) {
            $properties['NoSub'] = 1;
        }
        $properties['Subtitles'] = implode(',', $Params['subtitles']);

        $properties['Makers'] = isset($Params['makers']) ? $Params['makers'] : "";
        $properties['SpecialSub'] = isset($Params['special_effects_subtitles']) ? 1 : 0;
        $properties['ChineseDubbed'] = isset($Params['chinese_dubbed']) ? 1 : 0;

        $properties['RemasterYear'] = !empty($Params['remaster_year']) ? trim($Params['remaster_year']) : '';
        $properties['RemasterTitle'] = isset($Params['remaster_title']) ? trim($Params['remaster_title']) : '';
        $properties['RemasterTitle'] = EditionInfo::mergeAdvanceFeature($properties['RemasterTitle'], $Params);

        $properties['RemasterCustomTitle'] = isset($Params['remaster_custom_title']) ? $Params['remaster_custom_title'] : '';
        $properties['Scene'] = isset($Params['scene']) ? '1' : '0';
        $properties['Jinzhuan'] = isset($Params['jinzhuan']) ? '1' : '0';
        $properties['Diy'] = isset($Params['diy']) ? '1' : '0';
        $properties['Buy'] = isset($Params['buy']) ? '1' : '0';
        $properties['Allow'] = isset($Params['allow']) ? '1' : '0';
        $properties['TorrentDescription'] = $Params['release_desc'];
        $properties['MediaInfo']  = json_encode($Params['mediainfo']);
        $properties['Note'] = isset($POST['staff_note']) ? trim($Params['staff_note']) : "";

        if (!empty($Params['requestid'])) {
            $properties['RequestID'] = $Params['requestid'];
        }

        $properties = $this->initTorrentFile($properties, $Files);

        $Artists = $Params['artists'];
        $Importance = $Params['importance'];
        $ArtistIMDBIDs = $Params['artist_ids'];
        $ArtistSubName = $Params['artists_sub'];

        for ($i = 0, $il = count($Artists); $i < $il; $i++) {
            if (trim($Artists[$i]) != '') {
                $ArtistForm[$Importance[$i]][] = array('Name' => $Artists[$i], 'IMDBID' => isset($ArtistIMDBIDs[$i]) ? $ArtistIMDBIDs[$i] : null, 'SubName' => $ArtistSubName[$i]);
            }
        }
        $properties['Artists'] = $ArtistForm;

        $properties['FreeLeech'] = Torrents::Normal;
        $properties['FreeLeechType'] = '1';
        if (CONFIG['TORRENT_UPLOAD_FREE'] == true) {
            $properties['FreeLeech'] = Torrents::FREE;
        } else if (in_array($this->properties['Processing'], ['Untouched', 'DIY', 'Remux', 'BD25', 'BD66', 'BD50', 'BD100', 'DVD9', 'DVD5'])) {
            $properties['FreeLeech'] = Torrents::OneFourthOff;
        } else {
            $properties['FreeLeech'] = Torrents::TwoFourthOff;
        }

        $isFree = random_int(0, 100);
        if ($isFree < CONFIG['FREE_PROBABILITY']) {
            $properties['FreeLeech'] = Torrents::FREE;
        }

        if ($properties['Diy'] || $properties['Buy']) {
            $properties['FreeLeech'] = Torrents::FREE;
        }
        $ReleaseGroup = Users::get_release_group_by_id($properties['Makers']);
        if (count($ReleaseGroup) > 0) {
            $properties['FreeEndTime'] = Time::timePlus(3600 * CONFIG['PG_TORRENT_UPLOAD_FREE_HOUR']);
        } else {
            $properties['FreeEndTime'] = Time::timePlus(3600 * CONFIG['TORRENT_UPLOAD_FREE_HOUR']);
        }

        // limit free
        $properties['FreeTorrent'] = $properties['FreeLeech'];

        $properties['Slot'] = TorrentSlot::CalSlot($properties);
        $this->properties = $properties;
    }

    private function initTorrentFile(&$properties, $Files) {
        $properties['File'] = $Files['file_input']; // This is our torrent file
        $properties['TorrentName'] = $properties['File']['tmp_name'];
        $TorrentName = $properties['TorrentName'];
        $Tor = new \BencodeTorrent($TorrentName, true);
        $properties['PublicTorrent'] = $Tor->make_private(); // The torrent is now private.
        $properties['UnsourcedTorrent'] = $Tor->set_source(); // The source is now CONFIG['TORRENT_SOURCE']
        $TorEnc = db_string($Tor->encode());
        $properties['TorEnc'] = $TorEnc;
        $InfoHash = pack('H*', $Tor->info_hash());
        $properties['InfoHash'] = $InfoHash;
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
                throw new \Gazelle\Exception\InvalidParamException('The exact same torrent file already exists on the site!');
            } else {
                // A lost torrent
                $this->db->query("
                    INSERT INTO torrents_files (TorrentID, File)
                    VALUES ($ID, '$TorEnc')");
                throw new \Exception('Thank you for fixing this torrent!');
            }
        }

        if (isset($Tor->Dec['encrypted_files'])) {
            throw new \Exception('This torrent contains an encrypted file list which is not supported here.');
        }

        // File list and size
        list($TotalSize, $FileList) = $Tor->file_list();
        $properties['NumFiles'] = count($FileList);
        $TmpFileList = array();
        $TooLongPaths = array();
        $DirName = (isset($Tor->Dec['info']['files']) ? Format::make_utf8($Tor->get_name()) : '');
        $Err = $this->fileChecker->checkName($DirName); // check the folder name against the blacklist
        if ($Err) {
            throw new \Exception($Err);
        }
        foreach ($FileList as $File) {
            list($Size, $Name) = $File;

            // Check file name and extension against blacklist/whitelist
            $Err = $this->fileChecker->checkFile(1, $Name);
            if ($Err) {
                throw new \Exception($Err);
            }
            // Make sure the filename is not too long
            if (mb_strlen($Name, 'UTF-8') + mb_strlen($DirName, 'UTF-8') + 1 > MAX_FILENAME_LENGTH) {
                $TooLongPaths[] = "$DirName/$Name";
            }
            // Add file info to array
            $TmpFileList[] = \Torrents::filelist_format_file($File);
        }
        if (count($TooLongPaths) > 0) {
            $Names = implode(' <br />', $TooLongPaths);
            throw new \Exception('The torrent contained one or more files with too long of a name: ' . "$Names");
        }
        $properties['FilePath'] = $DirName;
        $properties['FileString'] = implode("\n", $TmpFileList);
        $properties['Size'] = $TotalSize;
        return $properties;
    }

    public function createArtists() {
        $ArtistForm = $this->properties['Artists'];
        $IMDBID = $this->properties['IMDBID'];
        $ArtistForm = Artists::new_artist($ArtistForm, $IMDBID);
        $this->properties['Artists'] = $ArtistForm;
    }

    private function updateUser() {
        $TotalSize = $this->properties['Size'];
        $TorrentID = $this->properties['TorrentID'];
        $UserID = $this->user['ID'];
        $FirstTorrent = $TotalSize > 2 * 1024 * 1024 * 1024 ? 1 : $TorrentID;
        $this->db->query("update users_main set firsttorrent=IF(firsttorrent = 0, $FirstTorrent, firsttorrent) ,TotalUploads=TotalUploads+1 where id=" . $this->user['ID']);
        $this->cache->delete_value("recent_uploads_$UserID");
    }

    private function fetchMovieData() {
        $RTRating = null;
        $DoubanRating = 'null';
        $DoubanID = 'null';
        $DoubanVote = 'null';
        $IMDBVote = 'null';
        $RTRating = '';
        $IMDBRating = 'null';
        $Runtime = '';
        $Released = '';
        $Country = '';
        $Language = '';
        $IMDBID = $this->properties['IMDBID'];
        if ($IMDBID) {
            $OMDBData = MOVIE::get_omdb_data($IMDBID);
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
            $DoubanData = MOVIE::get_douban_data($IMDBID);
            $DoubanRating = $DoubanData->rating ? $DoubanData->rating : 'null';
            $DoubanID = $DoubanData->id ? $DoubanData->id : 'null';
            $DoubanVote = $DoubanData->votes ? $DoubanData->votes : 'null';
            $IMDBVote = $OMDBData->imdbVotes && $OMDBData->imdbVotes != 'N/A' ? str_replace(',', '', $OMDBData->imdbVotes) : 'null';
            $RTRating = $RTRating ? $RTRating : '';
        }
        $MovieData['RTRating'] = $RTRating;
        $MovieData['IMDBRating'] = $IMDBRating;
        $MovieData['Runtime'] = $Runtime;
        $MovieData['Released'] = $Released;
        $MovieData['Country'] = $Country;
        $MovieData['Language'] = $Language;
        $MovieData['DoubanRating'] = $DoubanRating;
        $MovieData['DoubanID'] = $DoubanID;
        $MovieData['DoubanVote'] = $DoubanVote;
        $MovieData['IMDBVote'] = $IMDBVote;
        $this->properties['MovieData'] = $MovieData;
    }

    private function upload() {
        $this->db->begin_transaction();
        try {
            $IMDBID = $this->properties['IMDBID'];
            if ($IMDBID) {
                $this->db->prepared_query("SELECT * FROM torrents_group WHERE IMDBID = ?", $IMDBID);
                if ($this->db->record_count() > 0) {
                    $ExistedGroup = $this->db->next_record(MYSQLI_ASSOC, false);
                    $this->isNewGroup = false;
                    $this->properties['Group'] = $ExistedGroup;
                    $this->properties['GroupID'] = $ExistedGroup['ID'];
                }
            }
            if ($this->isNewGroup) {
                $ArtistForm = $this->properties['Artists'];
                $Name = $this->properties['Name'];
                $SubName = $this->properties['SubName'];
                $Year = $this->properties['Year'];
                $Body = $this->properties['Body'];
                $MainBody = $this->properties['MainBody'];
                $Image = $this->properties['Image'];
                $ReleaseType = $this->properties['ReleaseType'];
                $TrailerLink = $this->properties['TrailerLink'];
                $MovieData = $this->properties['MovieData'];
                // Create torrent group
                $this->db->prepared_query(
                    "INSERT INTO torrents_group
		              (
                          ArtistID, CategoryID, Name, SubName, Year,  Time, WikiBody, MainWikiBody,WikiImage, ReleaseType, IMDBID, TrailerLink, 
                          IMDBRating, Duration, ReleaseDate, Region, Language, RTRating, DoubanRating, DoubanID, DoubanVote, IMDBVote
                      )
		              VALUES
                      (?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?,   ?)",
                    0,
                    1,
                    $Name,
                    $SubName,
                    $Year,
                    Time::sqltime(),
                    $Body,
                    $MainBody,
                    $Image,
                    $ReleaseType,
                    $IMDBID,
                    $MovieData['TrailerLink'],
                    $MovieData['IMDBRating'],
                    $MovieData['Runtime'],
                    $MovieData['Released'],
                    $MovieData['Country'],
                    $MovieData['Language'],
                    $MovieData['RTRating'],
                    $MovieData['DoubanRating'],
                    $MovieData['DoubanID'],
                    $MovieData['DoubanVote'],
                    $MovieData['IMDBVote']
                );
                $GroupID = $this->db->inserted_id();
                $this->properties['GroupID'] = $GroupID;

                foreach ($ArtistForm as $Importance => $Artists) {
                    foreach ($Artists as $Num => $Artist) {
                        $this->db->query(
                            "INSERT IGNORE INTO torrents_artists (GroupID, ArtistID, UserID, Importance, Credit, `Order`)
					VALUES ($GroupID, " . $Artist['ArtistID'] . ', ' . $this->user['ID'] . ", '$Importance', true, $Num)"
                        );
                    }
                }

                $this->db->prepared_query(
                    "INSERT INTO wiki_torrents
			(
                PageID, Body, MainBody,UserID, Summary, Time, Image, IMDBID, DoubanID, Year, Name, SubName,ReleaseType)
		VALUES
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    $GroupID,
                    $Body,
                    $MainBody,
                    $this->user['ID'],
                    'Uploaded new torrent',
                    sqltime(),
                    $Image,
                    $IMDBID,
                    $MovieData['DoubanID'],
                    $Year,
                    $Name,
                    $SubName,
                    $ReleaseType
                );
                $RevisionID = $this->db->inserted_id();

                // Revision ID
                $this->db->query(
                    "UPDATE torrents_group
		SET RevisionID = '$RevisionID'
		WHERE ID = $GroupID"
                );
                $this->properties['GroupID'] = $GroupID;
                // Tags
                $GroupID = $this->properties['GroupID'];
                $UserID = $this->user['ID'];
                $Tags = explode(',', $this->properties['TagList']);
                $Tags = Tags::main_name($Tags);
                $tagMan = new Tag;
                foreach ($Tags as $Tag) {
                    $TagID = $tagMan->create($Tag, $this->user['ID']);
                    if ($TagID) {
                        $this->db->query("
				INSERT INTO torrents_tags
					(TagID, GroupID, UserID, PositiveVotes)
				VALUES
					($TagID, $GroupID, $UserID, 10)
				ON DUPLICATE KEY UPDATE
					PositiveVotes = PositiveVotes + 1;
			");
                        Tags::clear_all_cache();
                    }
                }
            } else {
                $GroupID = $this->properties['GroupID'];
                $this->db->query(
                    "SELECT ReleaseType
		FROM torrents_group
		WHERE ID = '$GroupID'"
                );
                list($properties['ReleaseType']) = $this->db->next_record();
            }
            $this->db->prepared_query("SELECT * FROM torrents_group WHERE ID = ?", $GroupID);
            $ExistedGroup = $this->db->next_record(MYSQLI_ASSOC, false);
            $this->properties['Group'] = $ExistedGroup;
            $this->properties['Artists'] = Artists::get_artist($GroupID);
            // Use this section to control freeleeches
            $Checked = 0;
            $UserID = $this->user['ID'];
            $this->db->prepared_query(
                "INSERT INTO torrents
		        (GroupID, UserID, RemasterYear, RemasterTitle, Scene, Jinzhuan, Diy, Buy, Allow, info_hash, FileCount, FileList,
		        FilePath, Size, Time, Description, FreeTorrent, FreeLeechType, Checked, NotMainMovie, Source, Codec, Container, 
                Resolution, Subtitles, Makers, Processing, RemasterCustomTitle, ChineseDubbed, SpecialSub, MediaInfo, Note, SubtitleType, Slot)
	        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                $this->properties['GroupID'],
                $this->user['ID'],
                $this->properties['RemasterYear'],
                $this->properties['RemasterTitle'],
                $this->properties['Scene'],
                $this->properties['Jinzhuan'],
                $this->properties['Diy'],
                $this->properties['Buy'],
                $this->properties['Allow'],
                $this->properties['InfoHash'],
                $this->properties['NumFiles'],
                $this->properties['FileString'],
                $this->properties['FilePath'],
                $this->properties['Size'],
                Time::sqltime(),
                $this->properties['TorrentDescription'],
                strval($this->properties['FreeLeech']),
                $this->properties['FreeLeechType'],
                $Checked,
                $this->properties['NotMainMovie'],
                $this->properties['Source'],
                $this->properties['Codec'],
                $this->properties['Container'],
                $this->properties['Resolution'],
                $this->properties['Subtitles'],
                $this->properties['Makers'],
                $this->properties['Processing'],
                $this->properties['RemasterCustomTitle'],
                $this->properties['ChineseDubbed'],
                $this->properties['SpecialSub'],
                $this->properties['MediaInfo'],
                $this->properties['Note'],
                $this->properties['SubtitleType'],
                $this->properties['Slot']
            );


            $TorrentID = $this->db->inserted_id();
            $FreeEndTime = $this->properties['FreeEndTime'];
            $this->properties['TorrentID'] = $TorrentID;

            $this->db->query(
                "INSERT INTO `freetorrents_timed`(`TorrentID`, `EndTime`) VALUES ($TorrentID,'$FreeEndTime') ON DUPLICATE KEY UPDATE EndTime=VALUES(EndTime)"
            );
            $GroupID = $this->properties['GroupID'];
            $TorEnc = $this->properties['TorEnc'];


            $this->db->query("
	        INSERT INTO torrents_files (TorrentID, File) VALUES ($TorrentID, '$TorEnc')");

            if ($this->properties['NoSub']) {
                $this->db->query("
                INSERT INTO torrents_no_sub
                VALUES ($TorrentID, $UserID, '" . sqltime() . "')");
            }

            if ($this->properties['HardSub']) {
                $this->db->query("
                INSERT INTO torrents_hard_sub
                VALUES ($TorrentID, $UserID, '" . sqltime() . "')");
            }

            if ($this->properties['BadFolders']) {
                $this->db->query("
                INSERT INTO torrents_bad_folders
                VALUES ($TorrentID, $UserID, '" . sqltime() . "')");
            }
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        $this->db->commit();
    }

    private function clearCache() {
        $GroupID = $this->properties['GroupID'];
        if ($this->isNewGroup) {
            $this->cache->increment('stats_album_count');
            $this->cache->increment('stats_group_count');
        } else {
            $this->cache->delete_value("torrent_group_$GroupID");
            $this->cache->delete_value("torrents_details_$GroupID");
            $this->cache->delete_value("detail_files_$GroupID");
        }
        $this->cache->increment('stats_torrent_count');
    }

    private function buildUploadedTorrent() {
        $T = new UploadedTorrent;
        $T->GroupID = $this->properties['GroupID'];
        $T->TorrentID = $this->properties['TorrentID'];
        $T->RequestID = $this->properties['RequestID'];
        $T->IsPrivate = !($this->properties['PublicTorrent'] || $this->properties['UnsourcedTorrent']);
        return $T;
    }
}
