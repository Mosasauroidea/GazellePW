<?

use Gazelle\Torrent\EditionType;
use Gazelle\Torrent\EditionInfo;
use Gazelle\Torrent\Region;
use Gazelle\Torrent\Language;
use Gazelle\Torrent\TorrentSlot;
use Gazelle\Torrent\Notification;
use Illuminate\Support\Facades\File;
use Gazelle\Util\Time;

class Torrents {
    const FILELIST_DELIM = 0xF7; // Hex for &divide; Must be the same as phrase_boundary in manticore.conf!
    const SNATCHED_UPDATE_INTERVAL = 3600; // How often we want to update users' snatch lists
    const SNATCHED_UPDATE_AFTERDL = 300; // How long after a torrent download we want to update a user's snatch lists

    const Normal = 0;
    const FREE = 1;
    const Neutral = 2;
    const OneFourthOff = 11;
    const TwoFourthOff = 12;
    const ThreeFourthOff = 13;

    const Downloading = 1;
    const Seeding = 2;

    public static function tags($Group) {
        $TorrentTags = $Group['TagList'];
        if ($TorrentTags != '') {
            $TorrentTags = explode(' ', $TorrentTags);
        }
        $Ret = Tags::get_sub_name($TorrentTags);
        return implode(' ', array_values($Ret));
    }
    public static function format_region($Region, $Limit = 1) {
        $Regions = array_map(function ($value) {
            return Region::text($value);
        }, array_slice(explode(',', $Region), 0, $Limit));
        return  implode(', ', $Regions);
    }

    public static function format_language($Language, $Limit = 3) {
        $Languages = array_map(function ($value) {
            return Language::text($value);
        }, array_slice(explode(',', $Language), 0, $Limit));
        return implode(', ', $Languages);
    }

    public static function get_actual_size($Torrent) {
        $Size = $Torrent['Size'];
        switch ($Torrent['FreeTorrent']) {
            case self::FREE:
            case self::Neutral:
                return 0;
            case self::OneFourthOff:
                return 0.75 * $Size;
            case self::TwoFourthOff:
                return 0.5 * $Size;
            case self::ThreeFourthOff:
                return 0.25 * $Size;
            default:
                return $Size;
        }
    }

    public static function get_search_language($Language) {
        $key = Lang::get_key('server.upload', $Language);
        if (!empty($key)) {
            return Lang::getWithLang($key, Lang::EN);
        }
        return 'invalid';
    }

    public static function get_thumb_counts($GroupID) {
        G::$DB->query("
        select tt.id, 
            count(tb.fromuserid) count, 
            (
                select count(1) 
                    from thumb where
                    itemid = tt.id
                    and fromuserid=" . G::$LoggedUser['ID'] . "
                    and type = 'torrent'
            ) 'on'
        from torrents as tt
        left join thumb as tb 
            on tt.id = tb.itemid 
            and tb.type = 'torrent' 
        WHERE tt.groupid = $GroupID
        group by tt.id");
        $ThumbCounts = G::$DB->to_array('id');
        return $ThumbCounts;
    }

    public static function get_bonus_sended($GroupID) {
        G::$DB->query("
        SELECT `ID`,
             sum(bonus) Count,
             (select group_concat(bonus)
                from torrents_send_bonus where torrentid=t.id
                and FromUserID = " . G::$LoggedUser['ID'] . "
             ) Sended
        FROM `torrents` as t
        left join torrents_send_bonus as tsb 
            on t.id = tsb.torrentid
        where t.groupid = $GroupID
        group by id");
        $BonusSended = G::$DB->to_array('ID');
        return $BonusSended;
    }

    public static function get_group($GroupID, $Return = true, $RevisionID = 0, $PersonalProperties = true, $ApiCall = false) {
        $Cache = G::$Cache;
        $DB = G::$DB;
        if (!$RevisionID) {
            $TorrentCache = $Cache->get_value("torrents_details_$GroupID");
        }
        if ($RevisionID || empty($TorrentCache)) {
            // Fetch the group details

            $SQL = 'SELECT ';

            if (!$RevisionID) {
                $SQL .= '
				g.Name,
				g.SubName,
				g.Year,
				g.WikiBody,
                g.MainWikiBody,
				g.WikiImage, 
				g.IMDBID,
				g.DoubanID, 
				g.RTTitle,';
            } else {
                $SQL .= '
				w.Name,
				w.SubName,
				w.Year,
				w.Body as WikiBody,
                w.MainBody as MainWikiBody,
				w.Image as WikiImage,
				w.IMDBID,
				w.DoubanID, 
				w.RTTitle,';
            }

            $SQL .= "
				g.Region, 
				g.ReleaseDate,
				g.Language,
				g.Duration,
				g.DoubanRating, 
				g.DoubanVote, 
				g.RTRating, 
				g.IMDBVote, 
				g.IMDBRating, 
				g.ID,
				g.IMDBRating, 
				g.ReleaseType,
				g.CategoryID,
				g.Time,
				GROUP_CONCAT(DISTINCT tags.Name ORDER BY `TagID` SEPARATOR ' ') as TagList,
				GROUP_CONCAT(DISTINCT tags.ID SEPARATOR ' ') as TorrentTagIDs,
				GROUP_CONCAT(tt.UserID SEPARATOR ' ') as TorrentTagUserIDs,
				GROUP_CONCAT(tt.PositiveVotes SEPARATOR ' ') as TagPositiveVotes,
				GROUP_CONCAT(tt.NegativeVotes SEPARATOR ' ') as TagNegativeVotes
			FROM torrents_group AS g
				LEFT JOIN torrents_tags AS tt ON tt.GroupID = g.ID
				LEFT JOIN tags ON tags.ID = tt.TagID";

            if ($RevisionID) {
                $SQL .= "
				LEFT JOIN wiki_torrents AS w ON w.PageID = '" . db_string($GroupID) . "'
						AND w.RevisionID = '" . db_string($RevisionID) . "' ";
            }

            $SQL .= "
			WHERE g.ID = '" . db_string($GroupID) . "'
			GROUP BY NULL";

            $DB->query($SQL);
            $TorrentGroup = $DB->next_record(MYSQLI_ASSOC, false);
            // Fetch the individual torrents
            $DB->query("
			SELECT
				t.ID,
                t.GroupID,
				t.RemasterYear,
				t.RemasterTitle,
                t.RemasterCustomTitle,
				t.Scene,
				t.Jinzhuan,
				t.Diy,
				t.Buy,
				t.Allow,
				t.FileCount,
				t.Size,
				t.Seeders,
				t.Leechers,
				t.Snatched,
				t.FreeTorrent,
				t.Time,
                t.Checked,
				t.NotMainMovie,
				t.Source,
				t.Codec,
				t.Container,
				t.Resolution,
                t.Processing,
				t.ChineseDubbed,
				t.SpecialSub,
				t.Subtitles,
				group_concat(sub.languages separator '|') as ExternalSubtitles,
				group_concat(sub.id separator '|') as ExternalSubtitleIDs,
				t.Makers,
				t.Description,
				t.MediaInfo,
				t.Note,
				t.SubtitleType,
				t.FileList,
				t.FilePath,
				t.UserID,
				t.last_action,
				HEX(t.info_hash) AS InfoHash,
				tbf.TorrentID AS BadFolders,
				tfi.TorrentID AS BadFiles,
				tns.TorrentID AS NoSub,
				ths.TorrentID AS HardSub,
				tct.CustomTrumpable as CustomTrumpable,
				t.LastReseedRequest,
				t.ID AS HasFile,
				fttd.EndTime as FreeEndTime,
				t.Slot,
				t.IsExtraSlot,
                rt.ID as ReportID
			FROM torrents AS t
				LEFT JOIN torrents_bad_folders AS tbf ON tbf.TorrentID = t.ID
				LEFT JOIN torrents_bad_files AS tfi ON tfi.TorrentID = t.ID
				LEFT JOIN torrents_no_sub AS tns ON tns.TorrentID = t.ID
				LEFT JOIN torrents_hard_sub AS ths ON ths.TorrentID = t.ID
				LEFT JOIN torrents_custom_trumpable AS tct ON tct.TorrentID = t.ID
				LEFT JOIN freetorrents_timed as fttd on fttd.TorrentID = t.id
				LEFT JOIN subtitles as sub on sub.torrent_id = t.id
                LEFT JOIN reportsv2 as rt on rt.TorrentID = t.id AND rt.Status != 'Resolved'
			WHERE t.GroupID = '" . db_string($GroupID) . "'
			GROUP BY t.ID
			ORDER BY t.ID");

            $TorrentList = $DB->to_array('ID', MYSQLI_ASSOC, ['MediaInfo']);
            uasort($TorrentList, 'Torrents::sort_torrent');
            if (count($TorrentList) === 0 && $ApiCall == false) {
                header('Location: log.php?search=' . (empty($_GET['torrentid']) ? "Group+$GroupID" : "Torrent+$_GET[torrentid]"));
                die();
            } elseif (count($TorrentList) === 0 && $ApiCall == true) {
                return null;
            }
            $CacheTime = 600;
            $TorrentGroup['Torrents'] = $TorrentList;
            // Store it all in cache
            if (!$RevisionID) {
                $Cache->cache_value("torrents_details_$GroupID", $TorrentGroup, $CacheTime);
            }
        } else { // If we're reading from cache
            $TorrentGroup = $TorrentCache;
            $TorrentList = &$TorrentGroup['Torrents'];
        }

        if ($PersonalProperties) {
            // Fetch all user specific torrent and group properties
            $TorrentGroup['Flags'] = array('IsSnatched' => false);
            foreach ($TorrentList as &$Torrent) {
                Torrents::torrent_properties($Torrent, $TorrentGroup['Flags']);
            }
        }

        if ($Return) {
            return $TorrentGroup;
        }
    }


    public static function get_groups($GroupIDs, $Return = true, $GetArtists = true, $Torrents = true) {
        $Found = $NotFound = array_fill_keys($GroupIDs, false);
        $Key = $Torrents ? 'torrent_group_' : 'torrent_group_light_';

        foreach ($GroupIDs as $i => $GroupID) {
            if (!is_number($GroupID)) {
                unset($GroupIDs[$i], $Found[$GroupID], $NotFound[$GroupID]);
                continue;
            }
            $Data = G::$Cache->get_value($Key . $GroupID, true);
            if (!empty($Data) && is_array($Data) && $Data['ver'] == CACHE::GROUP_VERSION) {
                unset($NotFound[$GroupID]);
                $Found[$GroupID] = $Data['d'];
            }
        }
        // Make sure there's something in $GroupIDs, otherwise the SQL will break
        if (count($GroupIDs) === 0) {
            return array();
        }

        /*
        Changing any of these attributes returned will cause very large, very dramatic site-wide chaos.
        Do not change what is returned or the order thereof without updating:
            torrents, artists, collages, bookmarks, better, the front page,
        and anywhere else the get_groups function is used.
        Update self::array_group(), too
        */

        if (count($NotFound) > 0) {
            $IDs = implode(',', array_keys($NotFound));
            $NotFound = array();
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT
					ID, CategoryID
				FROM torrents_group
				WHERE ID IN ($IDs)");
            $DiffCategoryGroups = G::$DB->to_array();
            $MovieWayIDs = [];
            foreach ($DiffCategoryGroups as $Record) {

                $MovieWayIDs[] = $Record['ID'];
            }
            $MovieWayIDs = implode(',', $MovieWayIDs);
            if ($MovieWayIDs) {
                G::$DB->query("SELECT
						g.ID as ID, 
                        g.Name as Name, 
                        g.Year as Year, 
                        GROUP_CONCAT(DISTINCT tags.Name ORDER BY `TagID` SEPARATOR ' ') as TagList,
                        g.ReleaseType as ReleaseType, 
                        g.WikiImage as WikiImage,
                        g.CategoryID as CategoryID, 
                        g.SubName as SubName, 
                        g.IMDBID as IMDBID, 
                        g.TrailerLink as TrailerLink, 
                        g.IMDBRating as IMDBRating, 
                        g.DoubanRating as DoubanRating, 
                        g.RTRating as RTRating, 
                        g.DoubanVote as DoubanVote, 
                        g.IMDBVote as IMDBVote, 
                        g.DoubanID as DoubanID, 
                        g.RTTitle as RTTitle, 
                        g.Region as Region
					FROM torrents_group AS g
                        LEFT JOIN torrents_tags AS tt ON tt.GroupID = g.ID
				        LEFT JOIN tags ON tags.ID = tt.TagID
					WHERE g.ID IN ($MovieWayIDs) Group by g.ID");
                while ($Group = G::$DB->next_record(MYSQLI_ASSOC)) {
                    $NotFound[$Group['ID']] = $Group;
                    $NotFound[$Group['ID']]['Torrents'] = array();
                    $NotFound[$Group['ID']]['Artists'] = array();
                }
            }

            G::$DB->set_query_id($QueryID);

            if ($Torrents) {
                $QueryID = G::$DB->get_query_id();
                if ($MovieWayIDs) {
                    G::$DB->query("
						SELECT
							t.ID,
							t.GroupID,
                            t.Processing,
							t.RemasterYear,
							t.RemasterTitle,
                            t.RemasterCustomTitle,
							t.Scene,
							t.Jinzhuan,
							t.Diy,
							t.Buy,
							t.Allow,
							t.FileCount,
							t.FreeTorrent,
							t.Size,
							t.Leechers,
							t.Seeders,
							t.Snatched,
							t.Time,
							t.ID AS HasFile,
							t.Checked,
							t.NotMainMovie,
							t.Source,
							t.Codec,
							t.Container,
							t.Resolution,
							t.Subtitles,
                            t.ChineseDubbed,
                            t.SpecialSub,
							t.Makers,
                            t.last_action,
							tbf.TorrentID AS BadFolders,
							tfi.TorrentID AS BadFiles,
							tct.CustomTrumpable as CustomTrumpable,
							fttd.EndTime as FreeEndTime,
				            tns.TorrentID AS NoSub,
				            ths.TorrentID AS HardSub,
				            group_concat(sub.languages separator '|') as ExternalSubtitles,
				            group_concat(sub.id separator '|') as ExternalSubtitleIDs,
                            t.Slot,
                            rt.ID as ReportID,
				            t.FilePath,
                            t.FileList,
                            t.MediaInfo
						FROM torrents t
							LEFT JOIN torrents_bad_folders AS tbf ON tbf.TorrentID = t.ID
							LEFT JOIN torrents_bad_files AS tfi ON tfi.TorrentID = t.ID
							LEFT JOIN torrents_custom_trumpable AS tct ON tct.TorrentID = t.ID
							LEFT JOIN freetorrents_timed as fttd on fttd.TorrentID = t.id
                            LEFT JOIN torrents_no_sub AS tns ON tns.TorrentID = t.ID
				            LEFT JOIN torrents_hard_sub AS ths ON ths.TorrentID = t.ID
				            LEFT JOIN subtitles as sub on sub.torrent_id = t.id
                            LEFT JOIN reportsv2 as rt on rt.TorrentID = t.id AND rt.Status != 'Resolved'
						WHERE t.GroupID IN ($IDs)
						GROUP BY t.ID ORDER BY t.ID");
                    while ($Torrent = G::$DB->next_record(MYSQLI_ASSOC, false)) {
                        $NotFound[$Torrent['GroupID']]['Torrents'][$Torrent['ID']] = $Torrent;
                    }
                }
                G::$DB->set_query_id($QueryID);
            }

            foreach ($NotFound as $GroupID => &$GroupInfo) {
                uasort($GroupInfo['Torrents'], 'Torrents::sort_torrent');
                G::$Cache->cache_value($Key . $GroupID, array('ver' => CACHE::GROUP_VERSION, 'd' => $GroupInfo), 600);
            }

            $Found = $NotFound + $Found;
        }

        // Filter out orphans (elements that are == false)
        $Found = array_filter($Found);

        if ($GetArtists) {
            $Artists = Artists::get_artists($GroupIDs);
        } else {
            $Artists = array();
        }

        if ($Return) { // If we're interested in the data, and not just caching it
            foreach ($Artists as $GroupID => $Data) {
                if (!isset($Found[$GroupID])) {
                    continue;
                }
                $Found[$GroupID]['Artists'] =  $Data;
            }
            // Fetch all user specific torrent properties
            if ($Torrents) {
                foreach ($Found as &$Group) {
                    $Group['Flags'] = array('IsSnatched' => false);
                    if (!empty($Group['Torrents'])) {
                        foreach ($Group['Torrents'] as &$Torrent) {
                            self::torrent_properties($Torrent, $Group['Flags']);
                        }
                    }
                }
            }
            uasort($Found, function ($a, $b) use ($GroupIDs) {
                return (array_search($a['ID'], $GroupIDs) < array_search($b['ID'], $GroupIDs)) ? -1 : 1;
            });
            return $Found;
        }
    }

    /**
     * Returns a reconfigured array from a Torrent Group
     *
     * Use this with extract() instead of the volatile list($GroupID, ...)
     * Then use the variables $GroupID, $GroupName, etc
     *
     * @example  extract(Torrents::array_group($SomeGroup));
     * @param array $Group torrent group
     * @return array Re-key'd array
     */
    public static function array_group(array &$Group) {
        return array(
            'GroupID' => $Group['ID'],
            'GroupName' => $Group['Name'],
            'GroupYear' => $Group['Year'],
            'GroupSubName' => $Group['SubName'],
            'GroupCategoryID' => $Group['CategoryID'],
            'GroupFlags' => isset($Group['Flags']) ? $Group['Flags'] : array('IsSnatched' => false),
            'TagList' => $Group['TagList'],
            'ReleaseType' => $Group['ReleaseType'],
            'WikiImage' => $Group['WikiImage'],
            'Torrents' => isset($Group['Torrents']) ? $Group['Torrents'] : array(),
            'Artists' => $Group['Artists'],
            'Artists' => $Group['Artists']
        );
    }
    private static function getval($str, $key) {
        if (preg_match("/^\s*$key\s*:\s*(.+)$/mi", $str, $match)) {
            return $match[1];
        } else {
            return false;
        }
    }

    public static function parse_file_name($Torrent) {
        $MediaInfo = json_decode($Torrent['MediaInfo'])[0];
        $CM = self::getval($MediaInfo, 'Complete name');
        if ($CM) {
            $slash = strrpos($CM, '\\');
            if (!$slash) {
                $slash = strrpos($CM, '/');
            }
            $CM = ltrim(substr($CM, $slash), '\\');
            $CM = ltrim($CM, '/');
            return trim($CM);
        }

        $CM = self::getval($MediaInfo, 'Disc Title');
        if ($CM) {
            return trim($CM);
        }
        $CM = self::getval($MediaInfo, "Disc Label");
        if ($CM) {
            return trim($CM);
        }

        return "";
    }


    public static function parse_container($Container) {
        global $Containers;
        if (!in_array($Container, $Containers)) {
            return 'Other';
        }
        return $Container;
    }

    public static function parse_codec($Codec) {
        global $Codecs;
        if (!in_array($Codec, $Codecs)) {
            return 'Other';
        }
        return $Codec;
    }

    public static function parse_resolution($Resolution) {
        global $Resolutions;
        if (!in_array($Resolution, $Resolutions)) {
            return 'Other';
        }
        return $Resolution;
    }

    public static function parse_source($Source) {
        global $Sources;
        if (!in_array($Source, $Sources)) {
            return 'Other';
        }
        return $Source;
    }

    /**
     * Supplements a torrent array with information that only concerns certain users and therefore cannot be cached
     *
     * @param array $Torrent torrent array preferably in the form used by Torrents::get_groups() or get_group_info()
     * @param int $TorrentID
     */
    public static function torrent_properties(&$Torrent, &$Flags) {
        $Torrent['PersonalFL'] = self::has_token($Torrent['ID']);
        if ($Torrent['IsSnatched'] = self::has_snatched($Torrent['ID'])) {
            $Flags['IsSnatched'] = true;
        }
        $State = self::torrent_state($Torrent['ID']);
        if ($State == self::Downloading) {
            $Torrent['IsDownloading'] = true;
            $Flag['IsDownloading'] = true;
        } else if ($State == self::Seeding) {
            $Torrent['IsSeeding'] = true;
            $Flag['IsSeeding'] = true;
        }
    }


    /*
     * Write to the group log.
     *
     * @param int $GroupID
     * @param int $TorrentID
     * @param int $UserID
     * @param string $Message
     * @param boolean $Hidden Currently does fuck all. TODO: Fix that.
     */
    public static function write_group_log($GroupID, $TorrentID, $UserID, $Message, $Hidden) {
        $QueryID = G::$DB->get_query_id();
        G::$DB->query("
			INSERT INTO group_log
				(GroupID, TorrentID, UserID, Info, Time, Hidden)
			VALUES
				($GroupID, $TorrentID, $UserID, '" . db_string($Message) . "', '" . Time::sqlTime() . "', $Hidden)");
        G::$DB->set_query_id($QueryID);
    }

    /**
     * Delete a torrent.
     *
     * @param int $ID The ID of the torrent to delete.
     * @param int $GroupID Set it if you have it handy, to save a query. Otherwise, it will be found.
     * @param string $OcelotReason The deletion reason for ocelot to report to users.
     */
    public static function delete_torrent($ID, $GroupID = 0, $OcelotReason = -1) {
        // remove requests
        $QueryID = G::$DB->get_query_id();
        if (!$GroupID) {
            G::$DB->query("
				SELECT GroupID, UserID
				FROM torrents
				WHERE ID = '$ID'");
            list($GroupID, $UserID) = G::$DB->next_record();
        }
        if (empty($UserID)) {
            G::$DB->query("
				SELECT UserID
				FROM torrents
				WHERE ID = '$ID'");
            list($UserID) = G::$DB->next_record();
        }

        $RecentUploads = G::$Cache->get_value("recent_uploads_$UserID");
        if (is_array($RecentUploads)) {
            foreach ($RecentUploads as $Key => $Recent) {
                if ($Recent['ID'] == $GroupID) {
                    G::$Cache->delete_value("recent_uploads_$UserID");
                }
            }
        }

        G::$DB->query("
			SELECT info_hash
			FROM torrents
			WHERE ID = $ID");
        list($InfoHash) = G::$DB->next_record(MYSQLI_BOTH, false);
        G::$DB->query("
			DELETE FROM torrents
			WHERE ID = $ID");
        Tracker::update_tracker('delete_torrent', array('info_hash' => rawurlencode($InfoHash), 'id' => $ID, 'reason' => $OcelotReason));

        G::$Cache->decrement('stats_torrent_count');

        G::$DB->query("
			SELECT COUNT(ID)
			FROM torrents
			WHERE GroupID = '$GroupID'");
        list($Count) = G::$DB->next_record();

        if ($Count == 0) {
            Torrents::delete_group($GroupID);
        } else {
            Torrents::update_hash($GroupID);
        }

        // Torrent notifications
        G::$DB->query("
			SELECT UserID
			FROM users_notify_torrents
			WHERE TorrentID = '$ID'");
        while (list($UserID) = G::$DB->next_record()) {
            G::$Cache->delete_value("notifications_new_$UserID");
        }
        G::$DB->query("
			DELETE FROM users_notify_torrents
			WHERE TorrentID = '$ID'");

        G::$DB->query("
			UPDATE reportsv2
			SET
				Status = 'Resolved',
				LastChangeTime = '" . sqltime() . "',
				ModComment = 'Report already dealt with (torrent deleted)'
			WHERE TorrentID = $ID
				AND Status != 'Resolved'");
        $Reports = G::$DB->affected_rows();
        if ($Reports) {
            G::$Cache->decrement('num_torrent_reportsv2', $Reports);
        }
        G::$DB->query("
			DELETE FROM torrents_files
			WHERE TorrentID = '$ID'");

        G::$DB->query("
			DELETE FROM torrents_bad_folders
			WHERE TorrentID = $ID");
        G::$DB->query("
			DELETE FROM torrents_bad_files
			WHERE TorrentID = $ID");
        // Tells Sphinx that the group is removed
        G::$DB->query("
			REPLACE INTO sphinx_delta (ID, Time)
			VALUES ($ID, UNIX_TIMESTAMP())");

        // remove requests
        G::$DB->query("SELECT ID, SourceTorrent FROM requests WHERE GroupID = $GroupID");
        $Requests = G::$DB->to_array(false, MYSQLI_ASSOC);
        foreach ($Requests as $Request) {
            $Link =  $Request['SourceTorrent'];
            if (!preg_match('/' . TORRENT_REGEX . '/i', $Link, $Matches)) {
                continue;
            }
            if ($Matches[2] != $ID) {
                continue;
            }
            $RequestID = $Request['ID'];
            break;
        }
        if (!empty($RequestID)) {
            Requests::delete_request($RequestID, 0, 'System', 'Torrent deleted');
        }


        G::$Cache->delete_value("torrent_download_$ID");
        G::$Cache->delete_value("torrent_group_$GroupID");
        G::$Cache->delete_value("torrents_details_$GroupID");
        G::$Cache->delete_value("recommend_group");
        G::$DB->set_query_id($QueryID);
    }

    public static function send_pm($TorrentID, $UploaderID, $Name, $Log, $TrumpID = 0, $PMUploader = false) {
        global $DB;

        $Variable = ['TorrentID' => $TorrentID, 'SiteURL' => site_url(false), 'Name' => $Name, 'Log' => $Log, 'TrumpID' => $TrumpID];
        // Uploader
        if ($PMUploader) {
            $Variable['Action'] = 'Uploaded';
            Misc::send_pm_with_tpl($UploaderID, 'torrent_delete', $Variable);
        }
        $PMedUsers = [$UploaderID];
        // Seeders
        $Extra = implode(',', array_fill(0, count($PMedUsers), '?'));
        $DB->prepared_query("
SELECT DISTINCT(xfu.uid) 
FROM 
	xbt_files_users AS xfu
	JOIN users_info AS ui ON xfu.uid = ui.UserID
WHERE xfu.fid = ? 
	AND ui.NotifyOnDeleteSeeding='1' 
	AND xfu.uid NOT IN ({$Extra})", $TorrentID, ...$PMedUsers);
        $UserIDs = $DB->collect('uid');
        foreach ($UserIDs as $UserID) {
            $Variable['Action'] = 'Seeding';
            Misc::send_pm_with_tpl($UserID, 'torrent_delete', $Variable);
        }
        $PMedUsers = array_merge($PMedUsers, $UserIDs);

        // Snatchers
        $Extra = implode(',', array_fill(0, count($PMedUsers), '?'));
        $DB->prepared_query("
SELECT DISTINCT(xs.uid) 
FROM xbt_snatched AS xs JOIN users_info AS ui ON xs.uid = ui.UserID 
WHERE xs.fid=? AND ui.NotifyOnDeleteSnatched='1' AND xs.uid NOT IN ({$Extra})", $TorrentID, ...$PMedUsers);
        $UserIDs = $DB->collect('uid');
        foreach ($UserIDs as $UserID) {
            $Variable['Action'] = 'Snatched';
            Misc::send_pm_with_tpl($UserID, 'torrent_delete', $Variable);
        }
        $PMedUsers = array_merge($PMedUsers, $UserIDs);

        // Downloaders
        $Extra = implode(',', array_fill(0, count($PMedUsers), '?'));
        $DB->prepared_query("
SELECT DISTINCT(ud.UserID)
FROM users_downloads AS ud JOIN users_info AS ui ON ud.UserID = ui.UserID
WHERE ud.TorrentID=? AND ui.NotifyOnDeleteDownloaded='1' AND ud.UserID NOT IN ({$Extra})", $TorrentID, ...$PMedUsers);
        $UserIDs = $DB->collect('UserID');
        foreach ($UserIDs as $UserID) {
            $Variable['Action'] = 'Downloaded';
            Misc::send_pm_with_tpl($UserID, 'torrent_delete', $Variable);
        }
    }


    /**
     * Delete a group, called after all of its torrents have been deleted.
     * IMPORTANT: Never call this unless you're certain the group is no longer used by any torrents
     *
     * @param int $GroupID
     */
    public static function delete_group($GroupID) {
        $QueryID = G::$DB->get_query_id();

        Misc::write_log("Group $GroupID automatically deleted (No torrents have this group).");

        G::$DB->query("
			SELECT CategoryID
			FROM torrents_group
			WHERE ID = '$GroupID'");
        list($Category) = G::$DB->next_record();
        if ($Category == 1) {
            G::$Cache->decrement('stats_album_count');
        }
        if ($Category == 2) {
            G::$Cache->decrement('stats_drama_count');
        }
        G::$Cache->decrement('stats_group_count');



        // Collages
        G::$DB->query("
			SELECT CollageID
			FROM collages_torrents
			WHERE GroupID = '$GroupID'");
        if (G::$DB->has_results()) {
            $CollageIDs = G::$DB->collect('CollageID');
            G::$DB->query("
				UPDATE collages
				SET NumTorrents = NumTorrents - 1
				WHERE ID IN (" . implode(', ', $CollageIDs) . ')');
            G::$DB->query("
				DELETE FROM collages_torrents
				WHERE GroupID = '$GroupID'");

            foreach ($CollageIDs as $CollageID) {
                G::$Cache->delete_value("collage_$CollageID");
            }
            G::$Cache->delete_value("torrent_collages_$GroupID");
        }

        // Artists
        // Collect the artist IDs and then wipe the torrents_artist entry
        G::$DB->query("
			SELECT ArtistID
			FROM torrents_artists
			WHERE GroupID = $GroupID");
        $Artists = G::$DB->collect('ArtistID');

        G::$DB->query("
			DELETE FROM torrents_artists
			WHERE GroupID = '$GroupID'");

        foreach ($Artists as $ArtistID) {
            if (empty($ArtistID)) {
                continue;
            }
            // Get a count of how many groups or requests use the artist ID
            G::$DB->query("
				SELECT COUNT(ag.ArtistID)
				FROM artists_group AS ag
					LEFT JOIN requests_artists AS ra ON ag.ArtistID = ra.ArtistID
				WHERE ra.ArtistID IS NOT NULL
					AND ag.ArtistID = '$ArtistID'");
            list($ReqCount) = G::$DB->next_record();
            G::$DB->query("
				SELECT COUNT(ag.ArtistID)
				FROM artists_group AS ag
					LEFT JOIN torrents_artists AS ta ON ag.ArtistID = ta.ArtistID
				WHERE ta.ArtistID IS NOT NULL
					AND ag.ArtistID = '$ArtistID'");
            list($GroupCount) = G::$DB->next_record();
            if (($ReqCount + $GroupCount) == 0) {
                //The only group to use this artist
                Artists::delete_artist($ArtistID);
            } else {
                //Not the only group, still need to clear cache
                G::$Cache->delete_value("artist_groups_$ArtistID");
            }
        }

        // Requests
        G::$DB->query("
			SELECT ID
			FROM requests
			WHERE GroupID = '$GroupID'");
        $Requests = G::$DB->collect('ID');
        G::$DB->query("
			UPDATE requests
			SET GroupID = NULL
			WHERE GroupID = '$GroupID'");
        foreach ($Requests as $RequestID) {
            G::$Cache->delete_value("request_$RequestID");
        }

        // comments
        Comments::delete_page('torrents', $GroupID);

        G::$DB->query("
			DELETE FROM torrents_group
			WHERE ID = '$GroupID'");
        G::$DB->query("
			DELETE FROM torrents_tags
			WHERE GroupID = '$GroupID'");
        G::$DB->query("
			DELETE FROM torrents_tags_votes
			WHERE GroupID = '$GroupID'");
        G::$DB->query("
			DELETE FROM bookmarks_torrents
			WHERE GroupID = '$GroupID'");
        G::$DB->query("
			DELETE FROM wiki_torrents
			WHERE PageID = '$GroupID'");

        G::$Cache->delete_value("torrents_details_$GroupID");
        G::$Cache->delete_value("torrent_group_$GroupID");
        G::$Cache->delete_value("groups_artists_$GroupID");
        G::$DB->set_query_id($QueryID);
    }


    /**
     * Update the cache and sphinx delta index to keep everything up-to-date.
     *
     * @param int $GroupID
     */
    public static function update_hash($GroupID) {
        $QueryID = G::$DB->get_query_id();
        // Fetch album vote score
        G::$DB->query("
			SELECT Score
			FROM torrents_votes
			WHERE GroupID = $GroupID");
        if (G::$DB->has_results()) {
            list($VoteScore) = G::$DB->next_record();
        } else {
            $VoteScore = 0;
        }

        // Fetch album artists
        G::$DB->query(
            "SELECT GROUP_CONCAT(aa.Name separator ' ')
			FROM torrents_artists AS ta
				JOIN artists_alias AS aa ON aa.ArtistID = ta.ArtistID
			WHERE ta.GroupID = $GroupID
			GROUP BY ta.GroupID"
        );
        if (G::$DB->has_results()) {
            list($ArtistName) = G::$DB->next_record(MYSQLI_NUM, false);
        } else {
            $ArtistName = '';
        }

        G::$DB->query("
			REPLACE INTO sphinx_delta
				(ID, GroupID, GroupName, 
                TagList, 
                Year, CategoryID, Time, ReleaseType, Size, Snatched, Seeders, Leechers, Scene, Jinzhuan, Allow,
				FreeTorrent,Description, FileList, VoteScore, ArtistName, RemTitle,
				IMDBRating, DoubanRating, RTRating, Region, Language, IMDBID, Resolution, Container, Source, Codec, Processing, Subtitles,
                Diy, Buy, ChineseDubbed, SpecialSub, Checked)
			SELECT
				t.ID, g.ID, CONCAT_WS(' ', g.Name, g.SubName), 
                GROUP_CONCAT(DISTINCT tags.Name ORDER BY `TagID` SEPARATOR ' ') as TagList, 
                Year, CategoryID, UNIX_TIMESTAMP(t.Time), ReleaseType,
				Size, Snatched, Seeders,
				Leechers, CAST(Scene AS CHAR), CAST(Jinzhuan AS CHAR), CAST(Allow AS CHAR), 
				CAST(FreeTorrent AS CHAR),Description,
				(case when (t.Container = 'm2ts') then REPLACE(REPLACE(t.FilePath, '_', ' '), '/', ' ') else REPLACE(REPLACE(FileList, '_', ' '), '/', ' ') end) AS FileList, $VoteScore, '" . db_string($ArtistName) . "',
				REPLACE(RemasterTitle, '/', ' '), 
                IMDBRating, DoubanRating, RTRating,
                REPLACE(SUBSTRING_INDEX(g.Region, ',', 1), ',', ' '),
                REPLACE(SUBSTRING_INDEX(g.Language, ',', 2), ',', ' '),
                IMDBID, Resolution, Container, Source, Codec, Processing,
                t.Subtitles, Diy, Buy, ChineseDubbed, SpecialSub, Checked 
			FROM torrents AS t
				JOIN torrents_group AS g ON g.ID = t.GroupID
                LEFT JOIN torrents_tags AS tt ON tt.GroupID = g.ID
				LEFT JOIN tags ON tags.ID = tt.TagID
			WHERE g.ID = $GroupID Group by t.ID");

        G::$Cache->delete_value("torrents_details_$GroupID");
        G::$Cache->delete_value("torrent_group_$GroupID");
        G::$Cache->delete_value("torrent_group_light_$GroupID");

        $ArtistInfo = Artists::get_artist($GroupID);
        foreach ($ArtistInfo as $Importances => $Importance) {
            foreach ($Importance as $Artist) {
                G::$Cache->delete_value('artist_groups_' . $Artist['ArtistID']); //Needed for at least freeleech change, if not others.
            }
        }

        G::$Cache->delete_value("groups_artists_$GroupID");
        G::$DB->set_query_id($QueryID);
    }

    public static function update_slots($TorrentIDs, $TorrentSlots, $GroupID) {
        $SQLArray = [];
        G::$DB->query("SELECT ID, Slot, IsExtraSlot FROM torrents WHERE ID in (" . implode(',', $TorrentIDs) . ")");
        $TorrentOldSlots = G::$DB->to_array('ID', MYSQLI_ASSOC);
        $TorrentNewSlots = array_combine($TorrentIDs, $TorrentSlots);
        $Messages = [];
        foreach ($TorrentNewSlots as $TorrentID => $Slot) {
            $torrentSlot = explode('*', $Slot)[0];
            $isExtraSlot = false;
            if (strpos($Slot, '*')) {
                $isExtraSlot = true;
            }
            $oldExtraSlot = false;
            if (!empty($TorrentOldSlots[$TorrentID]['IsExtraSlot'])) {
                $oldExtraSlot = true;
            }
            if ($TorrentOldSlots[$TorrentID]['Slot'] == $torrentSlot && $oldExtraSlot == $isExtraSlot) {
                continue;
            }
            $SQLArray[] = " ($TorrentID, $torrentSlot, '$isExtraSlot') ";
            $Messages[] = [$TorrentID, $TorrentOldSlots[$TorrentID]['Slot'], $torrentSlot];
        }
        if (count($SQLArray) == 0) {
            return;
        }
        $SQL = implode(',', $SQLArray);
        $SQL = "
        insert into torrents(ID, Slot, IsExtraSlot) VALUES $SQL on duplicate key update Slot=values(Slot), IsExtraSlot=values(IsExtraSlot);";
        G::$DB->query($SQL);
        foreach ($Messages as $Message) {
            $TorrentID = $Message[0];
            $TorrentOldSlot = $Message[1];
            $TorrentNewSlot = $Message[2];
            Misc::write_log("Torrent $TorrentID was edited by " . G::$LoggedUser['Username'] . " (Slot: $TorrentOldSlot -> $TorrentNewSlot)");
        }
        G::$Cache->delete_value("torrents_details_$GroupID");
        G::$Cache->delete_value("torrent_group_$GroupID");
    }

    /**
     * Regenerate a torrent's file list from its meta data,
     * update the database record and clear relevant cache keys
     *
     * @param int $TorrentID
     */
    public static function regenerate_filelist($TorrentID) {
        $QueryID = G::$DB->get_query_id();

        G::$DB->query("
			SELECT tg.ID,
				tf.File
			FROM torrents_files AS tf
				JOIN torrents AS t ON t.ID = tf.TorrentID
				JOIN torrents_group AS tg ON tg.ID = t.GroupID
			WHERE tf.TorrentID = $TorrentID");
        if (G::$DB->has_results()) {
            list($GroupID, $Contents) = G::$DB->next_record(MYSQLI_NUM, false);
            if (Misc::is_new_torrent($Contents)) {
                $Tor = new BencodeTorrent($Contents);
                $FilePath = (isset($Tor->Dec['info']['files']) ? Format::make_utf8($Tor->get_name()) : '');
            } else {
                $Tor = new TORRENT(unserialize(base64_decode($Contents)), true);
                $FilePath = (isset($Tor->Val['info']->Val['files']) ? Format::make_utf8($Tor->get_name()) : '');
            }
            list($TotalSize, $FileList) = $Tor->file_list();
            foreach ($FileList as $File) {
                $TmpFileList[] = self::filelist_format_file($File);
            }
            $FileString = implode("\n", $TmpFileList);
            G::$DB->query("
				UPDATE torrents
				SET Size = $TotalSize, FilePath = '" . db_string($FilePath) . "', FileList = '" . db_string($FileString) . "'
				WHERE ID = $TorrentID");
            G::$Cache->delete_value("torrents_details_$GroupID");
        }
        G::$DB->set_query_id($QueryID);
    }

    /**
     * Return UTF-8 encoded string to use as file delimiter in torrent file lists
     */
    public static function filelist_delim() {
        static $FilelistDelimUTF8;
        if (isset($FilelistDelimUTF8)) {
            return $FilelistDelimUTF8;
        }
        return $FilelistDelimUTF8 = utf8_encode(chr(self::FILELIST_DELIM));
    }

    /**
     * Create a string that contains file info in a format that's easy to use for Sphinx
     *
     * @param array $File (File size, File name)
     * @return string with the format .EXT sSIZEs NAME DELIMITER
     */
    public static function filelist_format_file($File) {
        list($Size, $Name) = $File;
        $Name = Format::make_utf8(strtr($Name, "\n\r\t", '   '));
        $ExtPos = strrpos($Name, '.');
        // Should not be $ExtPos !== false. Extensionless files that start with a . should not get extensions
        $Ext = ($ExtPos ? trim(substr($Name, $ExtPos + 1)) : '');
        return sprintf("%s s%ds %s %s", ".$Ext", $Size, $Name, self::filelist_delim());
    }

    /**
     * Create a string that contains file info in the old format for the API
     *
     * @param string $File string with the format .EXT sSIZEs NAME DELIMITER
     * @return string with the format NAME{{{SIZE}}}
     */
    public static function filelist_old_format($File) {
        $File = self::filelist_get_file($File);
        return $File['name'] . '{{{' . $File['size'] . '}}}';
    }

    /**
     * Translate a formatted file info string into a more useful array structure
     *
     * @param string $File string with the format .EXT sSIZEs NAME DELIMITER
     * @return file info array with the keys 'ext', 'size' and 'name'
     */
    public static function filelist_get_file($File) {
        // Need this hack because filelists are always display_str()ed
        $DelimLen = strlen(display_str(self::filelist_delim())) + 1;
        list($FileExt, $Size, $Name) = explode(' ', $File, 3);
        if ($Spaces = strspn($Name, ' ')) {
            $Name = str_replace(' ', '&nbsp;', substr($Name, 0, $Spaces)) . substr($Name, $Spaces);
        }
        return array(
            'ext' => $FileExt,
            'size' => substr($Size, 1, -1),
            'name' => substr($Name, 0, -$DelimLen)
        );
    }

    public static function display_edition_info($EditionInfo) {
        $t = explode(' / ', $EditionInfo);
        $t = array_map(
            function ($item) {
                return EditionInfo::text(trim($item));
            },
            $t
        );
        return implode(' / ', $t);
    }

    public static function group_name($Group, $Link = true, $Class = '') {
        $GroupName = Lang::choose_content($Group['Name'], $Group['SubName']);
        $Year = $Group['Year'];
        $Ret = $GroupName . ' (' . $Year . ')';
        if ($Link) {
            $GroupID = $Group['ID'];
            $Ret = "<a class='$Class' href='torrents.php?id=$GroupID'>" . display_str($Ret) . "</a>";
        }
        return $Ret;
    }
    public static function torrent_name($Torrent, $WithLink = true, $WithMedia = true, $WithSize = true) {
        $Size = Format::get_size($Torrent['Size']);
        $Info = self::torrent_media_info($Torrent);
        $Group = $Torrent['Group'];
        $GroupName = Lang::choose_content($Group['Name'], $Group['SubName']);
        $Year = $Group['Year'];
        $Ret = $GroupName . ' (' . $Year . ') - ' . implode(' / ', $Info) . ($WithSize ? ' - ' . $Size : '');
        if ($WithLink) {
            $TorrentID = $Torrent['ID'];
            $Ret = "<a href='torrents.php?torrentid=$TorrentID#$TorrentID'>$Ret</a>";
        }
        return $Ret;
    }

    public static function parse_group_snatched($GroupInfo) {
        if (isset($GroupInfo['Flags']) && isset($GroupInfo['Flags']['IsSnatched']) && $GroupInfo['Flags']['IsSnatched']) {
            return true;
        }
        return false;
    }

    private static function torrent_media_info($Data, $Style = false, $Option = []) {
        $Items = $Option['SettingTorrentTitle']['Items'] ?: self::SettingTorrentTitleItemsDefault;
        $Info = array();
        $Codec = $Data['Codec'];
        $Source = $Data['Source'];
        $Resolution = $Data['Resolution'];
        $Container = $Data['Container'];
        $Processing = in_array($Data['Processing'], ['---', 'Encode']) ? '' : $Data['Processing'];
        if ($Option['SettingTorrentTitle']['Alternative']) {
            $Codec = str_pad($Data['Codec'], 5, ' ', STR_PAD_RIGHT);
            $Source = str_pad($Data['Source'], 7, ' ', STR_PAD_RIGHT);
            $Edition = self::get_edition($Resolution, $Data['RemasterTitle'], $Data['RemasterCustomTitle'], $Data['NotMainMovie']);
            if (!in_array($Edition, ['extra_definition', '3d'])) {
                $Resolution = '';
            }
            $Container = str_pad($Data['Container'], 4, ' ', STR_PAD_RIGHT);
            $Processing = str_pad($Processing ?: 'Encode', 6, ' ', STR_PAD_RIGHT);
        }
        foreach ($Items as $Item) {
            if ($Item == 'Codec' && !empty($Codec)) {
                if ($Style) {
                    $Info[] = "<span class='TorrentTitle-item codec'>" . $Codec . "</span>";
                } else {
                    $Info[] = $Codec;
                }
            }
            if ($Item == 'Source' && !empty($Source)) {
                if ($Style) {
                    $Info[] = "<span class='TorrentTitle-item source'>" . $Source . "</span>";
                } else {
                    $Info[] = $Source;
                }
            }
            if ($Item == 'Resolution' && !empty($Resolution)) {
                if ($Style) {
                    $Info[] = "<span class='TorrentTitle-item resolution'>" . $Resolution . "</span>";
                } else {
                    $Info[] = $Resolution;
                }
            }
            if ($Item == 'Container' && !empty($Container)) {
                if ($Style) {
                    $Info[] = "<span class='TorrentTitle-item container'>" . $Container . "</span>";
                } else {
                    $Info[] = $Container;
                }
            }
            if ($Item == 'Processing' && !empty($Processing)) {
                if ($Style) {
                    if ($Option['SettingTorrentTitle']['Alternative']) {
                        if ($Data['Slot'] != TorrentSlot::TorrentSlotTypeNone) {
                            $Slot = TorrentSlot::slot_filter_name($Data['Slot']);
                            $Processing = icon("Torrent/slot_${Slot}");
                        }
                    }
                    $Info[] = "<span class='TorrentTitle-item processing'>" . $Processing . "</span>";
                } else {
                    $Info[] = $Processing;
                }
            }
        }
        return $Info;
    }

    public static function torrent_simple_view($Group, $Torrent, $Link = true, $Option = []) {
        $GroupID = $Group['ID'];
        $TorrentID = $Torrent['ID'];
        $Class = $Option['Class'];
        $TorrentInfo = Torrents::torrent_info($Torrent, true, $Option);
        if ($Link) {
            $TorrentInfo = "<a class='$Class' href='torrents.php?id=$GroupID&amp;torrentid=$TorrentID#torrent$TorrentID'>" . $TorrentInfo . "</a>";
        }
        return "<span class='TorrentTitle'><span class='TorrentTitle-item group_name'>" . Torrents::group_name($Group, $Link, 'TorrentTitle-item') . "</span><span class='TorrentTitle-item'> - </span>" . $TorrentInfo . "</span>";
    }


    /**
     * Format the information about a torrent.
     * @param array $Data an array a subset of the following keys:
     *  Codec, Scene, Jinzhuan, RemasterYear etc
     *  RemasterTitle, FreeTorrent, PersonalFL
     * @param boolean $ShowMedia if false, Media key will be omitted
     * @return string
     */
    public static function torrent_info($Data, $ShowMedia = true, $Option = []) {
        $Option = array_merge(['Self' => true, 'Class' => ''], $Option);
        $Info = array();
        $Separator = '<span class="TorrentTitle-item"> / </span>';
        $FileName = Torrents::filename($Data);

        if ($Option['UseReleaseName'] && !empty($FileName)) {
            $Info = ["<span class='TorrentTitle-item'>" . $FileName . "</span>"];
        } else if ($ShowMedia) {
            $Info = self::torrent_media_info($Data, true, $Option);
        }
        $RemasterYearInfo = '';
        if (!empty($Data['RemasterYear'])) {
            $RemasterYearInfo = " <sup class='remaster_year'><b>" . $Data['RemasterYear'] . "</b></sup>";
        }
        $EditionInfo = array();
        $t = [];
        if (!empty($Data['RemasterTitle'])) {
            $EditionInfo = explode(' / ', $Data['RemasterTitle']);
            sort($EditionInfo);
            $remaster_labels = EditionInfo::allEditionKey(EditionType::Remaster);
            $t = array_filter(
                $remaster_labels,
                function ($label) use ($EditionInfo) {
                    return in_array($label, $EditionInfo);
                }
            );

            $EditionInfo = array_map(
                function ($label) use ($t, $RemasterYearInfo) {
                    $title = EditionInfo::icon($label);
                    if (in_array($label, $t)) {
                        $title = $title . $RemasterYearInfo;
                    }
                    return "<span class='TorrentTitle-item remaster_$label'>" . $title . "</span>";
                },
                array_intersect($EditionInfo, $EditionInfo)
            );
        }
        if (count($EditionInfo)) {
            $Info[] = implode($Separator, $EditionInfo);
        }

        if (!empty($Data['RemasterCustomTitle'])) {
            $CustomTitle = "<span class='TorrentTitle-item remaster_custom_title'>" . $Data['RemasterCustomTitle'] . "</span>";
            if (!count($t)) {
                $CustomTitle .= $RemasterYearInfo;
            }
            $Info[] = $CustomTitle;
        }

        if (!empty($Data['Scene'])) {
            $Info[] = "<span class='TorrentTitle-item scene'>Scene</span>";
        }
        if (!empty($Data['Subtitles'])) {
            $Subtitles = explode(',', $Data['Subtitles']);
            if (in_array('chinese_simplified', $Subtitles)) {
                $Info[] = Format::torrent_label(t('server.torrents.chi'), 'tl_chi');
            } else if (in_array('chinese_traditional', $Subtitles)) {
                $Info[] = Format::torrent_label(t('server.torrents.chi'), 'tl_chi');
            }
        }
        if ($Data['ChineseDubbed']) {
            $Info[] = Format::torrent_label(t('server.torrents.cn_dub'), 'tl_cn_dub');
        }
        if ($Data['SpecialSub']) {
            $Info[] = Format::torrent_label(t('server.torrents.se_sub'), 'tl_se_sub');
        }

        if ($Data['Jinzhuan'] == '1' && $Data['Allow'] == '0') {
            $Info[] = Format::torrent_label(t('server.torrents.jinzhuan'), 'tl_exclusive');
        }
        if ($Data['Allow'] == '1') {
            $Info[] = Format::torrent_label(t('server.torrents.allow'), 'tl_allow');
        }
        $OfficialReleaseGroup = false;
        $ReleaseGroup = Users::get_release_group_by_id($Data['Makers'])['Name'];
        if (empty($ReleaseGroup)) {
            $ReleaseGroup = self::release_group($Data);
        } else {
            $OfficialReleaseGroup = true;
        }
        if (!$Option['UseReleaseName'] && $ReleaseGroup && $Option['SettingTorrentTitle']['ReleaseGroup']) {
            $Info[] = "<span class='TorrentTitle-item is-releaseGroup " . ($OfficialReleaseGroup ? "bg tl_buy" : '') . "'>$ReleaseGroup</span>";
        }
        if (
            (!empty($Data['BadFiles'])) ||
            (!empty($Data['BadFolders'])) ||
            (!empty($Data['NoSub'])) ||
            (!empty($Data['HardSub'])) ||
            (!empty($Data['CustomTrumpable'])) ||
            self::is_torrent_dead($Data)
        ) {
            $Info[] = Format::torrent_label(t('server.torrents.trump'), 'tl_trumpable');
        }
        if (empty($OfficialReleaseGroup)) {
            if ($Data['Buy'] == '1' &&  $Data['Diy'] == '0') {
                $Info[] = Format::torrent_label(t('server.torrents.buy'), 'bg tl_buy');
            }
            if ($Data['Diy'] == '1') {
                $Info[] = Format::torrent_label(t('server.torrents.diy'), 'bg tl_diy');
            }
        }
        if (self::global_freeleech()) {
            $Info[] = Format::torrent_label(t('server.torrents.fld'), 'tl_free bg torrent_discount free');
        } else if (isset($Data['FreeTorrent'])) {
            if ($Data['FreeTorrent'] == self::FREE) {
                $Info[] = Format::torrent_label(t('server.torrents.fld'), 'tl_free bg torrent_discount free', ($Data['FreeEndTime'] ? t('server.torrents.free_left', ['Values' => [time_diff($Data['FreeEndTime'], 2, false)]]) : ""));
            } else if ($Data['FreeTorrent'] == self::Neutral) {
                $Info[] = Format::torrent_label('Neutral Leech!', 'bg torrent_discount neutral');
            } else if ($Data['FreeTorrent'] == self::OneFourthOff) {
                $Info[] = Format::torrent_label('-25%', 'bg torrent_discount one_fourth_off', ($Data['FreeEndTime'] ? t('server.torrents.free_left', ['Values' => [time_diff($Data['FreeEndTime'], 2, false)]]) : ""));
            } else if ($Data['FreeTorrent'] == self::TwoFourthOff) {
                $Info[] = Format::torrent_label('-50%', 'bg torrent_discount two_fourth_off', ($Data['FreeEndTime'] ? t('server.torrents.free_left', ['Values' => [time_diff($Data['FreeEndTime'], 2, false)]]) : ""));
            } else if ($Data['FreeTorrent'] == self::ThreeFourthOff) {
                $Info[] = Format::torrent_label('-75%', 'bg torrent_discount three_fourth_off', ($Data['FreeEndTime'] ? t('server.torrents.free_left', ['Values' => [time_diff($Data['FreeEndTime'], 2, false)]]) : ""));
            }
        }

        $Class = $Option['Class'];
        if ($Option['Self']) {
            if (!empty($Data['ReportID']) > 0) {
                $Info[] = Format::torrent_label(t('server.torrents.reported'), 'tl_reported tips-reported');
            }
            if (!empty($Data['PersonalFL'])) {
                $Info[] = Format::torrent_label(t('server.torrents.pfl'), 'tl_free');
            }
            if (!empty($Data['IsSnatched'])) {
                $Class .=  ' ' . 'TorrentSnatched';
            }
            if (!empty($Data['IsDownloading'])) {
                $Class .=  ' ' . 'TorrentDownloading';
            }
            if (!empty($Data['IsSeeding'])) {
                $Class .=  ' ' . 'TorrentSeeding';
            }
        }
        if ($Option['ShowNew'] && time_ago($Data['Time']) < 600) {
            $Info[] = Format::torrent_label(t('server.subtitles.new'), 'u-colorWarning');
        }

        return "<span class='TorrentTitle $Class'>" . implode($Separator, $Info) . '</span>';
    }

    const SettingTorrentTitleItemsDefault = ['Resolution', 'Processing', 'Codec', 'Source', 'Container'];

    public static function settingTorrentTitle($SettingTorrentTitle, $Options = []) {
        $Options = array_merge(['Class' => ''], $Options);
        $Class = $Options['Class'];
        $Items = $SettingTorrentTitle['Items'] ?: self::SettingTorrentTitleItemsDefault;
        $Result = "<span class='TorrentTitle $Class u-sortable'>";
        $ResultInner = [];
        foreach ($Items as $Item) {
            $ResultInner[] = "<span class='TorrentTitle-item u-sortable-item'><span data-value='" . $Item . "'>" . t('server.torrents.' . strtolower($Item)) . "</span></span>";
        }
        return $Result . implode(' / ', $ResultInner) . '</span>';
    }

    public static function release_group($Torrent) {
        $FileName = self::filename($Torrent);
        preg_match("/[-@]?([a-zA-Z0-9]{0,15})(\.\w+)?$/i", $FileName, $Matches);
        if (count($Matches) > 1) {
            return $Matches[1];
        }
        return '';
    }

    public static function filename($Torrent) {
        if ($Torrent['FilePath']) {
            return $Torrent['FilePath'];
        }
        $FileList = explode("\n", display_str($Torrent['FileList']));
        $FileInfo = Torrents::filelist_get_file($FileList[0]);
        return $FileInfo['name'];
    }

    /**
     * Will freeleech / neutral leech / normalise a set of torrents
     *
     * @param array $TorrentIDs An array of torrent IDs to iterate over
     * @param int $FreeNeutral 0 = normal, 1 = fl, 2 = nl
     * @param int $FreeLeechType 0 = Unknown, 1 = Staff picks, 2 = Perma-FL (Toolbox, etc.)
     */
    public static function freeleech_torrents($TorrentIDs, $FreeNeutral = 1, $FreeLeechType = 0, $Schedule = false, $LimitTime = null) {
        if (!is_array($TorrentIDs)) {
            $TorrentIDs = array($TorrentIDs);
        }

        $QueryID = G::$DB->get_query_id();
        G::$DB->query("
			UPDATE torrents
			SET FreeTorrent = '$FreeNeutral', FreeLeechType = '$FreeLeechType'
			WHERE ID IN (" . implode(', ', $TorrentIDs) . ')');

        G::$DB->query('
			SELECT ID, GroupID, info_hash
			FROM torrents
			WHERE ID IN (' . implode(', ', $TorrentIDs) . ')
			ORDER BY GroupID ASC');
        $Torrents = G::$DB->to_array(false, MYSQLI_NUM, false);
        $GroupIDs = G::$DB->collect('GroupID');
        G::$DB->set_query_id($QueryID);

        foreach ($Torrents as $Torrent) {
            list($TorrentID, $GroupID, $InfoHash) = $Torrent;
            G::$Cache->delete_value("torrents_details_$GroupID");
            if ($LimitTime !== null) {
                G::$DB->query("
                   INSERT INTO `freetorrents_timed`(`TorrentID`, `EndTime`) 
                   VALUES ($TorrentID, '$LimitTime') ON DUPLICATE KEY UPDATE EndTime=VALUES(EndTime)");
            }
            Tracker::update_tracker('update_torrent', array('info_hash' => rawurlencode($InfoHash), 'freetorrent' => $FreeNeutral));
            G::$Cache->delete_value("torrent_download_$TorrentID");
            Misc::write_log(($Schedule ? "Schedule" : G::$LoggedUser['Username']) . " marked torrent $TorrentID freeleech $FreeNeutral type $FreeLeechType!");
            Torrents::write_group_log($GroupID, $TorrentID, $Schedule ? 0 : G::$LoggedUser['ID'], "marked as freeleech $FreeNeutral type $FreeLeechType!", 0);
            $TorrentInfo = Torrents::get_torrent($TorrentID);
            $Notification = new Notification;
            $Notification->edit_notify($TorrentInfo);
        }

        foreach ($GroupIDs as $GroupID) {
            Torrents::update_hash($GroupID);
        }
    }


    /**
     * Convenience function to allow for passing groups to Torrents::freeleech_torrents()
     *
     * @param array $GroupIDs the groups in question
     * @param int $FreeNeutral see Torrents::freeleech_torrents()
     * @param int $FreeLeechType see Torrents::freeleech_torrents()
     */
    public static function freeleech_groups($GroupIDs, $FreeNeutral = 1, $FreeLeechType = 0, $LimitTime = null) {
        $QueryID = G::$DB->get_query_id();

        if (!is_array($GroupIDs)) {
            $GroupIDs = array($GroupIDs);
        }

        G::$DB->query('
			SELECT ID
			FROM torrents
			WHERE GroupID IN (' . implode(', ', $GroupIDs) . ')');
        if (G::$DB->has_results()) {
            $TorrentIDs = G::$DB->collect('ID');
            Torrents::freeleech_torrents($TorrentIDs, $FreeNeutral, $FreeLeechType, false, $LimitTime);
        }
        G::$DB->set_query_id($QueryID);
    }


    /**
     * Check if the logged in user has an active freeleech token
     *
     * @param int $TorrentID
     * @return true if an active token exists
     */
    public static function has_token($TorrentID) {
        if (empty(G::$LoggedUser)) {
            return false;
        }

        static $TokenTorrents;
        $UserID = G::$LoggedUser['ID'];
        if (!isset($TokenTorrents)) {
            $TokenTorrents = G::$Cache->get_value("users_tokens_$UserID");
            if ($TokenTorrents === false) {
                $QueryID = G::$DB->get_query_id();
                G::$DB->query("
					SELECT TorrentID
					FROM users_freeleeches
					WHERE UserID = $UserID
						AND Expired = 0");
                $TokenTorrents = array_fill_keys(G::$DB->collect('TorrentID', false), true);
                G::$DB->set_query_id($QueryID);
                G::$Cache->cache_value("users_tokens_$UserID", $TokenTorrents);
            }
        }
        return isset($TokenTorrents[$TorrentID]);
    }


    /**
     * Check if the logged in user can use a freeleech token on this torrent
     *
     * @param int $Torrent
     * @return boolen True if user is allowed to use a token
     */
    public static function can_use_token($Torrent) {
        if (empty(G::$LoggedUser)) {
            return false;
        }

        return (G::$LoggedUser['FLTokens'] >= 1
            && !$Torrent['PersonalFL']
            && (in_array($Torrent['FreeTorrent'], [self::OneFourthOff, self::TwoFourthOff, self::ThreeFourthOff]) || empty($Torrent['FreeTorrent']))
            && G::$LoggedUser['CanLeech'] == 1);
    }

    /**
     * Build snatchlists and check if a torrent has been snatched
     * if a user has the 'ShowSnatched' option enabled
     * @param int $TorrentID
     * @return bool
     */
    public static function has_snatched($TorrentID) {
        if (empty(G::$LoggedUser) || empty(G::$LoggedUser['ShowSnatched'])) {
            return false;
        }

        $UserID = G::$LoggedUser['ID'];
        $Buckets = 64;
        $LastBucket = $Buckets - 1;
        $BucketID = $TorrentID & $LastBucket;
        static $SnatchedTorrents = array(), $UpdateTime = array();

        if (empty($SnatchedTorrents)) {
            $SnatchedTorrents = array_fill(0, $Buckets, false);
            $UpdateTime = G::$Cache->get_value("users_snatched_{$UserID}_time");
            if ($UpdateTime === false) {
                $UpdateTime = array(
                    'last' => 0,
                    'next' => 0
                );
            }
        } elseif (isset($SnatchedTorrents[$BucketID][$TorrentID])) {
            return true;
        }

        // Torrent was not found in the previously inspected snatch lists
        $CurSnatchedTorrents = &$SnatchedTorrents[$BucketID];
        if ($CurSnatchedTorrents === false) {
            $CurTime = time();
            // This bucket hasn't been checked before
            $CurSnatchedTorrents = G::$Cache->get_value("users_snatched_{$UserID}_$BucketID", true);
            if ($CurSnatchedTorrents === false || $CurTime > $UpdateTime['next']) {
                $Updated = array();
                $QueryID = G::$DB->get_query_id();
                if ($CurSnatchedTorrents === false || $UpdateTime['last'] == 0) {
                    for ($i = 0; $i < $Buckets; $i++) {
                        $SnatchedTorrents[$i] = array();
                    }
                    // Not found in cache. Since we don't have a suitable index, it's faster to update everything
                    G::$DB->query("
						SELECT fid
						FROM xbt_snatched
						WHERE uid = '$UserID'");
                    while (list($ID) = G::$DB->next_record(MYSQLI_NUM, false)) {
                        $SnatchedTorrents[$ID & $LastBucket][(int)$ID] = true;
                    }
                    $Updated = array_fill(0, $Buckets, true);
                } elseif (isset($CurSnatchedTorrents[$TorrentID])) {
                    // Old cache, but torrent is snatched, so no need to update
                    return true;
                } else {
                    // Old cache, check if torrent has been snatched recently
                    G::$DB->query("
						SELECT fid
						FROM xbt_snatched
						WHERE uid = '$UserID'
							AND tstamp >= $UpdateTime[last]");
                    while (list($ID) = G::$DB->next_record(MYSQLI_NUM, false)) {
                        $CurBucketID = $ID & $LastBucket;
                        if ($SnatchedTorrents[$CurBucketID] === false) {
                            $SnatchedTorrents[$CurBucketID] = G::$Cache->get_value("users_snatched_{$UserID}_$CurBucketID", true);
                            if ($SnatchedTorrents[$CurBucketID] === false) {
                                $SnatchedTorrents[$CurBucketID] = array();
                            }
                        }
                        $SnatchedTorrents[$CurBucketID][(int)$ID] = true;
                        $Updated[$CurBucketID] = true;
                    }
                }
                G::$DB->set_query_id($QueryID);
                for ($i = 0; $i < $Buckets; $i++) {
                    if (isset($Updated[$i])) {
                        G::$Cache->cache_value("users_snatched_{$UserID}_$i", $SnatchedTorrents[$i], 0);
                    }
                }
                $UpdateTime['last'] = $CurTime;
                $UpdateTime['next'] = $CurTime + self::SNATCHED_UPDATE_INTERVAL;
                G::$Cache->cache_value("users_snatched_{$UserID}_time", $UpdateTime, 0);
            }
        }
        return isset($CurSnatchedTorrents[$TorrentID]);
    }

    public static function torrent_state($TorrentID) {
        if (empty(G::$LoggedUser) || empty(G::$LoggedUser['ShowSnatched'])) {
            return 0;
        }

        $UserID = G::$LoggedUser['ID'];
        $Buckets = 64;
        $LastBucket = $Buckets - 1;
        $BucketID = $TorrentID & $LastBucket;
        static $DownloadedTorrents = array(), $UpdateTime = array();

        if (empty($DownloadedTorrents)) {
            $DownloadedTorrents = array_fill(0, $Buckets, false);
            $UpdateTime = G::$Cache->get_value("users_downloaded_{$UserID}_time");
            if ($UpdateTime === false) {
                $UpdateTime = array(
                    'last' => 0,
                    'next' => 0
                );
            }
        } elseif (isset($DownloadedTorrents[$BucketID][$TorrentID])) {
            return $DownloadedTorrents[$BucketID][$TorrentID] > 0 ? self::Downloading : self::Seeding;
        }

        // Torrent was not found in the previously inspected snatch lists
        $CurDownloadedTorrents = &$DownloadedTorrents[$BucketID];
        if ($CurDownloadedTorrents === false) {
            $CurTime = time();
            // This bucket hasn't been checked before
            $CurDownloadedTorrents = G::$Cache->get_value("users_downloaded_{$UserID}_$BucketID", true);
            if ($CurDownloadedTorrents === false || $CurTime > $UpdateTime['next']) {
                $Updated = array();
                $QueryID = G::$DB->get_query_id();
                if ($CurDownloadedTorrents === false || $UpdateTime['last'] == 0) {
                    for ($i = 0; $i < $Buckets; $i++) {
                        $DownloadedTorrents[$i] = array();
                    }
                    // Not found in cache. Since we don't have a suitable index, it's faster to update everything
                    G::$DB->query("
						SELECT fid, remaining
						FROM xbt_files_users 
						WHERE uid = '$UserID' and active = 1");
                    while (list($ID, $Remaining) = G::$DB->next_record(MYSQLI_NUM, false)) {
                        $DownloadedTorrents[$ID & $LastBucket][(int)$ID] = $Remaining;
                    }
                    $Updated = array_fill(0, $Buckets, true);
                } elseif (isset($CurDownloadedTorrents[$TorrentID])) {
                    // Old cache, but torrent is snatched, so no need to update
                    return $CurDownloadedTorrents[$TorrentID] > 0 ? self::Downloading : self::Seeding;
                } else {
                    // Old cache, check if torrent has been snatched recently
                    G::$DB->query("
						SELECT fid, remaining
						FROM xbt_files_users
						WHERE uid = '$UserID'
							AND mtime >= $UpdateTime[last]");
                    while (list($ID, $Remainging) = G::$DB->next_record(MYSQLI_NUM, false)) {
                        $CurBucketID = $ID & $LastBucket;
                        if ($DownloadedTorrents[$CurBucketID] === false) {
                            $DownloadedTorrents[$CurBucketID] = G::$Cache->get_value("users_downloaded_{$UserID}_$CurBucketID", true);
                            if ($DownloadedTorrents[$CurBucketID] === false) {
                                $DownloadedTorrents[$CurBucketID] = array();
                            }
                        }
                        $DownloadedTorrents[$CurBucketID][(int)$ID] = $Remainging;
                        $Updated[$CurBucketID] = true;
                    }
                }
                G::$DB->set_query_id($QueryID);
                for ($i = 0; $i < $Buckets; $i++) {
                    if (isset($Updated[$i])) {
                        G::$Cache->cache_value("users_downloaded_{$UserID}_$i", $DownloadedTorrents[$i], 0);
                    }
                }
                $UpdateTime['last'] = $CurTime;
                $UpdateTime['next'] = $CurTime + self::SNATCHED_UPDATE_INTERVAL;
                G::$Cache->cache_value("users_downloaded_{$UserID}_time", $UpdateTime, 0);
            }
        }
        if (!isset($CurDownloadedTorrents[$TorrentID])) {
            return 0;
        }
        return $CurDownloadedTorrents[$TorrentID] > 0 ? self::Downloading : self::Seeding;
    }

    /**
     * Change the schedule for when the next update to a user's cached snatch list should be performed.
     * By default, the change will only be made if the new update would happen sooner than the current
     * @param int $Time Seconds until the next update
     * @param bool $Force Whether to accept changes that would push back the update
     */
    public static function set_snatch_update_time($UserID, $Time, $Force = false) {
        if (!$UpdateTime = G::$Cache->get_value("users_snatched_{$UserID}_time")) {
            return;
        }
        $NextTime = time() + $Time;
        if ($Force || $NextTime < $UpdateTime['next']) {
            // Skip if the change would delay the next update
            $UpdateTime['next'] = $NextTime;
            G::$Cache->cache_value("users_snatched_{$UserID}_time", $UpdateTime, 0);
        }
    }

    // Some constants for self::display_string's $Mode parameter
    const DISPLAYSTRING_HTML = 1; // Whether or not to use HTML for the output (e.g. VH tooltip)
    const DISPLAYSTRING_ARTISTS = 2; // Whether or not to display artists
    const DISPLAYSTRING_YEAR = 4; // Whether or not to display the group's year
    const DISPLAYSTRING_VH = 8; // Whether or not to display the VH flag
    const DISPLAYSTRING_RELEASETYPE = 16; // Whether or not to display the release type
    const DISPLAYSTRING_LINKED = 33; // Whether or not to link artists and the group
    // The constant for linking is 32, but because linking only works with HTML, this constant is defined as 32|1 = 33, i.e. LINKED also includes HTML
    // Keep this in mind when defining presets below!

    // Presets to facilitate the use of $Mode
    const DISPLAYSTRING_DEFAULT = 63; // HTML|ARTISTS|YEAR|VH|RELEASETYPE|LINKED = 63
    const DISPLAYSTRING_SHORT = 6; // Very simple format, only artists and year, no linking (e.g. for forum thread titles)

    public static function update_movie_artist_info($GroupID, $IMDBID, $Refresh = false) {
        G::$DB->query("SELECT 
                        ta.ArtistID,
                        ag.IMDBID,
                        ag.RevisionID as RevisionID,
                        ag.Image as Image,
                        ag.Birthday as Birthday,
                        ag.PlaceOfBirth as PlaceOfBirth,
                        ag.SubName as SubName,
                        ag.Body as Body
                       FROM 
                        torrents_artists as ta
                       LEFT JOIN 
                        artists_group as ag
                       ON 
                        ag.ArtistID = ta.ArtistID
                       WHERE 
                        GroupID=$GroupID
                       AND
                        ag.IMDBID <> ''");
        $Artists = G::$DB->to_array(false, MYSQLI_ASSOC, false);
        $IMDBIDs = [];
        foreach ($Artists as $Artist) {
            if (!empty($Artist['IMDBID'])) {
                $IMDBIDs[] = $Artist['IMDBID'];
            }
        }
        $ArtistInfos = MOVIE::get_artists_seq($IMDBIDs, $IMDBID, $Refresh);
        foreach ($Artists as $Artist) {
            $UpdateSQL = [];
            $WikiChange = false;
            $ArtistID = $Artist['ArtistID'];
            $SubName = $Artist['SubName'];
            $Name = $Artist['Name'];
            $Body = $Artist['Body'];
            $MainBody = $Artist['MainBody'];
            $Image = $Artist['Image'];
            $ArtistIMDBID = $Artist['IMDBID'];
            $ArtistInfo = $ArtistInfos[$ArtistIMDBID];
            if (empty($ArtistInfo)) {
                continue;
            }
            if (empty($Image) && $ArtistInfo && !empty($ArtistInfo['Image'])) {
                $UpdateSQL[] = "Image = '" . db_string($ArtistInfo['Image']) . "'";
                $Image = db_string($ArtistInfo['Image']);
                $WikiChange = true;
            }
            if (empty($Body) && $ArtistInfo && !empty($ArtistInfo['Description'])) {
                $UpdateSQL[] = "Body = '" . db_string($ArtistInfo['Description']) . "'";
                $Body = db_string($ArtistInfo['Description']);
                $WikiChange = true;
            }
            if (empty($MainBody) && $ArtistInfo && !empty($ArtistInfo['MainDescription'])) {
                $UpdateSQL[] = "MainBody = '" . db_string($ArtistInfo['MainDescription']) . "'";
                $MainBody = db_string($ArtistInfo['MainDescription']);
                $WikiChange = true;
            }
            if (empty($Artist['PlaceOfBirth']) && $ArtistInfo && $ArtistInfo['PlaceOfBirth']) {
                $UpdateSQL[] = "PlaceOfBirth = '" . db_string($ArtistInfo['PlaceOfBirth']) . "'";
            }
            if (empty($Artist['Birthday']) && $ArtistInfo && $ArtistInfo['Birthday']) {
                $UpdateSQL[] = "Birthday = '" . db_string($ArtistInfo['Birthday']) . "'";
            }
            if ($WikiChange) {
                G::$DB->query(
                    "INSERT INTO wiki_artists
			        (PageID, Body, MainBody, Image, UserID, Summary, Time, IMDBID, SubName, Name)
		        VALUES
			        ('$ArtistID', '$Body', '$MainBody', '$Image', '0', 'Auto load', '" . sqltime() . "', '$ArtistIMDBID', '$SubName', '$Name')"
                );
                $RevisionID = G::$DB->inserted_id();
                $UpdateSQL[] = "RevisionID = $RevisionID";
            }
            if (empty($UpdateSQL)) {
                continue;
            }
            G::$DB->query("UPDATE artists_group SET " . implode(',', $UpdateSQL) . " WHERE ArtistID = $ArtistID");
            G::$Cache->delete_value('artist_' . $Artist['ArtistID']);
        }
        G::$Cache->delete_value('groups_artists_' . $GroupID);
        G::$Cache->delete_value("torrent_group_$GroupID");
        G::$Cache->delete_value("torrents_details_$GroupID");
    }

    public static function update_movie_info($GroupID, $IMDBID, $DoubanID = null, $Force = true) {
        if (empty($IMDBID)) {
            return;
        }
        $OMDBData = MOVIE::get_omdb_data($IMDBID, $Force);
        $UpdateSQL = [];
        if ($OMDBData->imdbVotes && $OMDBData->imdbVotes != 'N/A') {
            $UpdateSQL[] = "IMDBVote = " . str_replace(',', '', $OMDBData->imdbVotes);
        }
        if ($OMDBData->imdbRating && $OMDBData->imdbRating != 'N/A') {
            $UpdateSQL[] = "IMDBRating = " . $OMDBData->imdbRating;
        }
        if ($OMDBData->Runtime && $OMDBData->Runtime != 'N/A') {
            $UpdateSQL[] = "Duration = '" . $OMDBData->Runtime . "'";
        }
        if ($OMDBData->Released  && $OMDBData->Released != 'N/A') {
            $UpdateSQL[] = "ReleaseDate = '" . $OMDBData->Released . "'";
        }
        if ($OMDBData->Country  && $OMDBData->Country != 'N/A') {
            $UpdateSQL[] = "Region = '" . $OMDBData->Country . "'";
        }
        if ($OMDBData->Language  && $OMDBData->Language != 'N/A') {
            $UpdateSQL[] = "Language = '" . $OMDBData->Language . "'";
        }
        $RTRating = null;
        foreach ($OMDBData->Ratings as $key => $value) {
            if ($value->Source == "Rotten Tomatoes") {
                $RTRating = $value->Value;
            }
        }
        if ($RTRating) {
            $UpdateSQL[] = "RTRating = '" . $RTRating . "'";
        }
        $DoubanData = null;
        if (!empty($DoubanID)) {
            $UpdateSQL[] = "DoubanID = " . $DoubanID;
            $DoubanData = MOVIE::get_douban_data_by_doubanid($DoubanID, $Force);
        }
        if ($DoubanData && $DoubanData->rating) {
            $UpdateSQL[] = "DoubanRating = " . $DoubanData->rating;
        }
        if ($DoubanData && $DoubanData->votes) {
            $UpdateSQL[] = "DoubanVote = " . $DoubanData->votes;
        }

        $SQL = '
        Update torrents_group set ' . implode(',', $UpdateSQL) .
            ' WHERE ID = ' . $GroupID . ' ';
        G::$DB->query($SQL);
        G::$Cache->delete_value("torrent_group_$GroupID");
        G::$Cache->delete_value("torrents_details_$GroupID");
        return $DoubanID;
    }

    //Used to get reports info on a unison cache in both browsing pages and torrent pages.
    public static function get_reports($TorrentID) {
        $Reports = G::$Cache->get_value("reports_torrent_$TorrentID");
        if ($Reports === false) {
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT
					ID,
					ReporterID,
					Type,
					UserComment,
					ReportedTime,
					UploaderReply,
					ReplyTime
				FROM reportsv2
				WHERE TorrentID = $TorrentID
					AND Status != 'Resolved'");
            $Reports = G::$DB->to_array(false, MYSQLI_ASSOC);
            G::$DB->set_query_id($QueryID);
            G::$Cache->cache_value("reports_torrent_$TorrentID", $Reports, 0);
        }
        if (!check_perms('admin_reports')) {
            $Return = array();
            foreach ($Reports as $Report) {
                if ($Report['Type'] !== 'edited') {
                    $Return[] = $Report;
                }
            }
            return $Return;
        }
        return $Reports;
    }

    public static function resolution_level($a) {
        global $HighDefinition, $StandardDefinition, $UltraDefinition;
        $Resolution = $a['Resolution'];
        if ($a['NotMainMovie']) {
            return SUBGROUP_Extra;
        }
        if (self::is_3d($a['RemasterTitle'])) {
            return SUBGROUP_3D;
        }
        if (in_array($Resolution, $StandardDefinition)) {
            return SUBGROUP_SD;
        } else if (in_array($Resolution, $HighDefinition)) {
            return SUBGROUP_HD;
        } else if (in_array($Resolution, $UltraDefinition)) {
            return SUBGROUP_UHD;
        }
    }
    private static function resolution_value($a) {
        $resolution_val = [
            '480p' => 1,
            'NTSC' => 2,
            '576p' => 3,
            'PAL' => 4,
            '720p' => 5,
            '1080i' => 6,
            '1080p' => 7,
            '2160p' => 8,
        ];
        $value = 0;
        if (!isset($resolution_val[$a['Resolution']])) {
            list($width, $height) = explode('&times;', $a['Resolution']);
            $value = 100000000 + $height * 10000 + $width;
        } else {
            $value = 200000000 + $resolution_val[$a['Resolution']];
        }
        return $value;
    }
    public static function codec_value($a) {
        global $Codecs;
        $v = array_search($a['Codec'], $Codecs);
        if (!$v) {
            $v = 2.5;
        }
        return $v;
    }

    public static function processing_value($a) {
        if (empty($a['Processing']) || $a['Processing'] == '---') {
            return 'Encode';
        }
        if (in_array($a['Processing'], ['BD25', 'BD66', 'BD50', 'BD100', 'DVD9', 'DVD5'])) {
            return 'Untouched';
        }
        return $a['Processing'];
    }

    public static function get_processing_list($a) {
        if ($a == 'untouched') {
            return ['BD25', 'BD66', 'BD50', 'BD100', 'DVD9', 'DVD5'];
        }
        return [$a];
    }
    private static function slot_value($a) {
        $extra = 1;
        if (empty($a['IsSlotExtra'])) {
            $extra = 0;
        }
        return $a['Slot'] * 100 + $extra;
    }


    public static function sort_torrent($a, $b) {
        global $Processings;
        $LevelA = self::resolution_level($a);
        $LevelB = self::resolution_level($b);
        if ($LevelA != $LevelB) {
            return $LevelA < $LevelB ? -1 : 1;
        }
        $ProcessingA = array_search(self::processing_value($a), $Processings);
        $ProcessingB = array_search(self::processing_value($b), $Processings);
        if ($ProcessingA != $ProcessingB) {
            return $ProcessingA < $ProcessingB ? -1 : 1;
        }
        $ResA = self::resolution_value($a);
        $ResB = self::resolution_value($b);
        if ($ResA != $ResB) {
            return $ResA < $ResB ? -1 : 1;
        }

        $SlotA = self::slot_value($a);
        $SlotB = self::slot_value($b);
        if ($SlotA != $SlotB) {
            return $SlotA < $SlotB ? -1 : 1;
        }
        $CodecA = self::codec_value($a);
        $CodecB = self::codec_value($b);
        if ($CodecA != $CodecB) {
            return $CodecA < $CodecB ? -1 : 1;
        }
        $SizeA = $a['Size'];
        $SizeB = $b['Size'];
        if ($SizeA != $SizeB) {
            return $SizeA < $SizeB ? -1 : 1;
        }

        // 不应该走到这里
        return 1;
    }

    public static function get_torrent_checked($TorrentID) {
        $TorrentChecked = G::$Cache->get_value("torrent_checked_$TorrentID");
        if ($TorrentChecked === false) {
            G::$DB->query("select Checked from torrents where ID=$TorrentID");
            list($TorrentChecked) = G::$DB->next_record();
            G::$Cache->cache_value("torrent_checked_$TorrentID", $TorrentChecked);
        }
        return $TorrentChecked;
    }

    public static function get_new_edition_title($LastTorrent, $Torrent) {
        $LastResolution = isset($LastTorrent['Resolution']) ? $LastTorrent['Resolution'] : '';
        $LastRemasterTitle = isset($LastTorrent['RemasterTitle']) ? $LastTorrent['RemasterTitle'] : '';
        $LastRemasterCustomTitle = isset($LastTorrent['RemasterCustomTitle']) ? $LastTorrent['RemasterCustomTitle'] : '';
        $LastNotMain = isset($LastTorrent['NotMainMovie']) ? $LastTorrent['NotMainMovie'] : '';
        $Resolution = isset($Torrent['Resolution']) ? $Torrent['Resolution'] : '';
        $RemasterTitle = isset($Torrent['RemasterTitle']) ? $Torrent['RemasterTitle'] : '';
        $RemasterCustomTitle = isset($Torrent['RemasterCustomTitle']) ? $Torrent['RemasterCustomTitle'] : '';
        $NotMain = isset($Torrent['NotMainMovie']) ? $Torrent['NotMainMovie'] : '';
        // TODO bad design
        $lastEdition = self::get_edition($LastResolution, $LastRemasterTitle, $LastRemasterCustomTitle, $LastNotMain);
        $nextEdition = self::get_edition($Resolution, $RemasterTitle, $RemasterCustomTitle, $NotMain);
        if ($lastEdition != $nextEdition) {
            return t("server.torrents.$nextEdition", ['DefaultValue' => $nextEdition]);
        }
        return false;
    }

    private static function is_3d($RemasterTitle) {
        foreach (EditionInfo::allEditionKey(EditionType::ThreeD) as $value) {
            if (strstr($RemasterTitle, $value)) {
                return true;
            }
        }
        return false;
    }

    private static function is_extra($RemasterTitle, $RemasterCustomTitle) {

        if (strstr(strtolower($RemasterCustomTitle), "extra")) {
            return true;
        }
        if (strstr($RemasterTitle, "extras")) {
            return true;
        }
        return false;
    }

    public static function get_edition($Resolution, $RemasterTitle, $RemasterCustomTitle, $NotMain) {
        global $HighDefinition, $StandardDefinition, $UltraDefinition, $LoggedUser;
        $SettingTorrentTitle = $LoggedUser['SettingTorrentTitle'];
        if ($NotMain) {
            return "extra_definition";
        }
        if (self::is_3d($RemasterTitle)) {
            return "3d";
        }
        if ($SettingTorrentTitle['Alternative']) {
            return $Resolution;
        }
        if (in_array($Resolution, $StandardDefinition)) {
            return "group_standard_resolution";
        } else if (in_array($Resolution, $HighDefinition)) {
            return "group_high_resolution";
        } else if (in_array($Resolution, $UltraDefinition)) {
            return "group_ultra_high_resolution";
        } else if (empty($Resolution)) {
            return "";
        }
        return "group_standard_resolution";
    }

    public static function display_simple_group_name($GroupInfo, $TorrentID = null, $Style = true) {
        $SubName = $GroupInfo['SubName'];
        $GroupName = $GroupInfo['Name'];
        $GroupYear = $GroupInfo['Year'];
        $DisplayName = '';

        if (!$Style) {
            if ($SubName) {
                $DisplayName .= " [" . $SubName . "] ";
            }
            $DisplayName .= $GroupName;
            if ($GroupYear) {
                $DisplayName .= " ($GroupYear)";
            }
            return $DisplayName;
        }
        $GroupID = $GroupInfo['ID'];
        if ($SubName) {
            $DisplayName .= " [<a href=\"torrents.php?searchstr=" . $SubName . "\">$SubName</a>] ";
        }
        $DisplayName .= "<a href=\"torrents.php?id=$GroupID&amp;torrentid=$TorrentID#torrent$TorrentID\" data-tooltip=\"" . t('server.common.view_torrent') . "\" dir=\"ltr\">$GroupName</a>";
        if ($GroupYear) {
            $DisplayName .= " ($GroupYear)";
        }
        return $DisplayName;
    }

    public static function is_torrent_dead($Torrent) {
        return $Torrent['Seeders'] == 0 && !empty($Torrent['last_action'])  && $Torrent['last_action'] != '0000-00-00 00:00:00' && $Torrent['last_action'] < time_minus(3600 * 24 * TORRENT_DEAD_PERIOD);
    }
    // New Torrent Name: 安全领域 / Dirty Rotten Scoundrels Year: 1988 Uploader: joey Tags: comedy,documentary Codec: x264 Source: Blu-ray Container: MKV Resolution: 720p Size: 1.1 GB Freeleech: Freeleech! Link: https://CONFIG['SITE_NAME']/torrents.php?id=375
    public static function build_irc_msg($UploaderName, $Torrent) {
        $Freeleech = '';
        switch ($Torrent['FreeTorrent']) {
            case self::FREE:
                $Freeleech = 'Freeleech!';
                break;
            case self::OneFourthOff:
                $Freeleech = '25% off!';
                break;
            case self::ThreeFourthOff:
                $Freeleech = '50% off!';
                break;
            case self::ThreeFourthOff:
                $Freeleech = '75% off!';
                break;
        }
        if (self::global_freeleech()) {
            $Freeleech = 'Freeleech!';
        }
        $Link = site_url() . "torrents.php?torrentid={$Torrent['ID']}";
        $Size = Format::get_size($Torrent['Size']);
        return "\002New Torrent\002 \00303Name: " . $Torrent['SubName'] . ' / ' . $Torrent['Name'] . ' Year: ' . $Torrent['Year'] . ' Uploader: ' . $UploaderName . ' Tags: ' . $Torrent['TagList'] . "\003 \00312Codec: " . $Torrent['Codec'] . ' Source: ' . $Torrent['Source'] . ' Container: ' . $Torrent['Container'] . ' Resolution: ' . $Torrent['Resolution'] . ' Size: ' . $Size . ' Freeleech: ' . $Freeleech . "\003 \00304Link: " . $Link . "\003";
    }


    public static function torrentid_to_groupid($TorrentID) {
        global $Cache, $DB;
        $DB->query("
		SELECT GroupID
		FROM torrents
		WHERE ID = '" . db_string($TorrentID) . "'");
        list($GroupID) = $DB->next_record(MYSQLI_NUM);
        if ($GroupID) {
            return $GroupID;
        }
        return null;
    }

    public static function convert_torrent($Group, $TorrentID = 0) {
        if ($TorrentID) {
            $TorrentInfo = $Group['Torrents'][$TorrentID];
        } else {
            list(, $TorrentInfo) = each($Group['Torrents']);
        }
        $TorrentInfo['Group'] = $Group;
        unset($Group['Torrents']);
        return $TorrentInfo;
    }

    public static function get_torrent($TorrentID, $Return = true, $RevisionID = 0, $PersonalProperties = true, $ApiCall = false) {
        $GroupID = self::torrentid_to_groupid($TorrentID);
        $GroupInfo = Torrents::get_group($GroupID, $Return, $RevisionID, $PersonalProperties, $ApiCall);
        if ($GroupInfo) {
            foreach ($GroupInfo['Torrents'] as &$Torrent) {
                if ($Torrent['ID'] == $TorrentID) {
                    $TorrentInfo = $GroupInfo['Torrents'][$TorrentID];
                    $TorrentInfo['Group'] = $GroupInfo;
                    unset($GroupInfo['Torrents']);
                    return $TorrentInfo;
                }
            }
            return null;
        } else {
            if ($Return) {
                return null;
            }
        }
    }
    public static function freeleech_option() {
        return array(
            self::Normal => "Normal",
            self::FREE => "Free",
            self::Neutral => "Neutral",
            self::OneFourthOff => "-25%",
            self::TwoFourthOff => "-50%",
            self::ThreeFourthOff => "-75%"
        );
    }

    public static function global_freeleech() {
        return CONFIG['GLOBAL_FREELEECH'] == true;
    }

    public static function torrent_freeleech($Torrent) {
        return $Torrent['FreeTorrent'] == self::FREE || self::global_freeleech();
    }
    public static function torrent_freetype($Torrent) {
        if (self::global_freeleech()) {
            return 1;
        }
        return $Torrent['FreeTorrent'];
    }
    public static function sanitizeName($Name) {
        return preg_replace(
            '/_+/',
            '_',         // remove doubled-up underscore
            trim(                                 // trim leading, trailing underscore
                preg_replace(
                    '/[^a-z0-9_]+/',
                    '', // remove non alphanum, underscore
                    str_replace(
                        [' ', '-'],
                        '_',  // dash and internal space to underscore
                        strtolower(               // lowercase
                            trim($Name)            // whitespace
                        )
                    )
                ),
                '.' // trim-a-dot
            )
        );
    }
    public static function build_file_tree($FilePath, $FileList) {
        $FileList = array_map(function ($value) use ($FilePath) {
            if (!empty($FilePath)) {
                return [array_merge([$FilePath], explode('/', $value[0])), $value[1]];
            }
            return [explode('/', $value[0]), $value[1]];
        }, $FileList);
        $Root = [];
        foreach ($FileList as $File) {
            self::_group_File($Root, $File[0], $File[1]);
        }
        return $Root;
    }
    private static function _group_file(&$Root, $Fragment, $Size) {
        if (count($Fragment) == 1) {
            $Root[$Fragment[0]] = ['size' => $Size, 'children' => []];
        } else {
            self::_group_file($Root[$Fragment[0]]['children'], array_slice($Fragment, 1), $Size);
            $Root[$Fragment[0]]['size'] += $Size;
        }
        return;
    }

    public static function render_media_info($MediaInfo) {
        $Index = 0;
        $MediaInfoObj = json_decode($MediaInfo);
        if (is_array($MediaInfoObj)) {
            foreach ($MediaInfoObj as $MediaInfo) {
                $MediaInfo = ltrim(trim($MediaInfo), '[mediainfo]');
                $MediaInfo = ltrim(trim($MediaInfo), '[bdinfo]');
                $MediaInfo = rtrim(trim($MediaInfo), '[/mediainfo]');
                $MediaInfo = rtrim(trim($MediaInfo), '[/bdinfo]');
                echo ($Index > 0 ? "<br>" : "") . Text::full_format('[mediainfo]' . $MediaInfo . '[/mediainfo]');
                $Index++;
            }
        }
    }
}
