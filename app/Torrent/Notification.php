<?

namespace Gazelle\Torrent;

use Gazelle\Base;
use FEED, Text, Torrents;

class Notification extends Base {

    private $feed;
    public function __construct() {
        parent::__construct();
        $this->feed = new FEED;
    }

    public function notify($Properties, $IsNewGroup) {
        $Title = Torrents::display_simple_group_name($Properties, null, false);
        $Body = $Properties['Body'];
        $GroupID = $Properties['GroupID'];
        $TorrentID = $Properties['TorrentID'];
        $TotalSize = $Properties['Size'];
        $Group = $Properties['Group'];
        if ($Properties['ReleaseType'] > 0) {
            $Title .= ' [' . t('server.torrents.release_types')[$Properties['ReleaseType']] . ']';
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

        if ($Properties['FreeLeech'] == Torrents::FREE) {
            $Details .= ' / Freeleech!';
        } else if ($Properties['FreeLeech'] == Torrents::Neutral) {
            $Details .= ' / Neutral Leech!';
        } else if ($Properties['FreeLeech'] == Torrents::OneFourthOff) {
            $Details .= ' / 25% off!';
        } else if ($Properties['FreeLeech'] == Torrents::TwoFourthOff) {
            $Details .= ' / 50% off!';
        } else if ($Properties['FreeLeech'] == Torrents::ThreeFourthOff) {
            $Details .= ' / 75% off!';
        }

        if ($Details !== "") {
            $Title .= " - " . $Details;
        }


        // For RSS
        $Item = $this->feed->item($Title, html_entity_decode(Text::strip_bbcode($Body)), 'torrents.php?action=download&amp;authkey=[[AUTHKEY]]&amp;torrent_pass=[[PASSKEY]]&amp;id=' . $TorrentID, $this->user['Username'], 'torrents.php?id=' . $GroupID, trim($Properties['TagList']));


        //Notifications
        $SQL = "SELECT unf.ID, unf.UserID, torrent_pass FROM users_notify_filters AS unf JOIN users_main AS um ON um.ID = unf.UserID WHERE um.Enabled = '1'";

        // artists
        $SQL .= " AND (";
        if (!empty($Properties['Artists'])) {
            foreach ($Properties['Artists'] as $Importance => $Artists) {
                foreach ($Artists as $Artist) {
                    $ArtistNameList[] = "Artists LIKE '%|" . db_string(str_replace('\\', '\\\\', $Artist['Name']), true) . "|%'";
                    $ArtistNameList[] = "Artists LIKE '%|" . db_string(str_replace('\\', '\\\\', $Artist['SubName']), true) . "|%'";
                }
            }
            // Don't add notification if >2 main artists or if tracked artist isn't a main artist
            $SQL .= implode(' OR ', $ArtistNameList);
            $SQL .= " OR Artists = '') AND (";
        }

        // tags
        $Tags = explode(',', $Properties['TagList']);
        $TagSQL = array();
        $NotTagSQL = array();
        foreach ($Tags as $Tag) {
            $TagSQL[] = " Tags LIKE '%|" . db_string(trim($Tag)) . "|%' ";
            $NotTagSQL[] = " NotTags LIKE '%|" . db_string(trim($Tag)) . "|%' ";
        }
        $TagSQL[] = "Tags = ''";
        $SQL .= implode(' OR ', $TagSQL);

        $SQL .= ") AND !(" . implode(' OR ', $NotTagSQL) . ')';

        // release type
        $SQL .= " AND (ReleaseTypes LIKE '%|" . db_string($Properties['ReleaseType']) . "|%' OR ReleaseTypes = '') ";

        // codec
        $NotifyCodec = Torrents::parse_codec($Properties['Codec']);
        $SQL .= " AND (Codecs LIKE '%|" . db_string(trim($NotifyCodec)) . "|%' OR Codecs = '') ";

        // source
        $NotifySource = Torrents::parse_source($Properties['Source']);
        $SQL .= " AND (Sources LIKE '%|" . db_string(trim($NotifySource)) . "|%' OR Sources = '') ";

        // resolution
        $NotifyResolution = Torrents::parse_resolution($Properties['Resolution']);
        $SQL .= " AND (Resolutions LIKE '%|" . db_string(trim($NotifyResolution)) . "|%' OR Resolutions = '') ";

        // container
        $NotifyContainer = Torrents::parse_container($Properties['Container']);
        $SQL .= " AND (Containers LIKE '%|" . db_string(trim($NotifyContainer)) . "|%' OR Containers = '') ";

        // processing
        $NotifyProcessing = $Properties['Processing'];
        if ($NotifyProcessing == '---') {
            $NotifyProcessing = 'Encode';
        }
        $SQL .= " AND (Processings LIKE '%|" . db_string(trim($NotifyProcessing)) . "|%' OR Processings = '') ";

        // freeleech
        if (Torrents::global_freeleech()) {
            $SQL .= " AND (FreeTorrents LIKE '%|" . db_string('1') . "|%' OR FreeTorrents = '') ";
        } else if ($Properties['FreeLeech']) {
            $SQL .= " AND (FreeTorrents LIKE '%|" . db_string(trim($Properties['FreeLeech'])) . "|%' OR FreeTorrents = '') ";
        } else {
            $SQL .= " AND (FreeTorrents = '') ";
        }

        // new group
        if (!$IsNewGroup) {
            $SQL .= "AND (NewGroupsOnly = '0' )";
        }

        // size
        $SQL .= " AND (('" . db_string(trim($TotalSize)) . "' BETWEEN FromSize AND ToSize)
			OR (FromSize = 0 AND ToSize = 0)
            OR ('" . db_string(trim($TotalSize)) . "' > FromSize AND ToSize = 0)) ";

        // year
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

        //  imdb rating
        $IMDBRating = $Group['IMDBRating'];
        if ($IMDBRating && $IMDBRating != 'N/A') {
            $SQL .= " AND (" .  $IMDBRating . " > FromIMDBRating OR FromIMDBRating = 0)";
        }

        // country
        $Country = $Group['Country'];
        if ($Country) {
            foreach (explode(',', $Country) as $R) {
                $RegionSQL[] = " Regions LIKE '%|" . db_string(trim($R)) . "|%' ";
            }
        }
        $RegionSQL[] = "Regions = ''";
        $SQL .= " AND (" . implode(' OR ', $RegionSQL) . ") ";

        // language
        $Language = $Group['Language'];
        if ($Language) {
            foreach (explode(',', $Language) as $L) {
                $LanguageSQL[] = " Languages LIKE '%|" . db_string(trim($L)) . "|%' ";
            }
        }
        $LanguageSQL[] = "Languages = ''";
        $SQL .= " AND (" . implode(' OR ', $LanguageSQL) . ") ";

        // remaster title (edition info)
        $RemasterTitle = $Properties['RemasterTitle'];
        if ($RemasterTitle) {
            $RemasterTitleNotify = explode(' / ', $RemasterTitle);
            foreach ($RemasterTitleNotify as $RTN) {
                $RemasterTitleSQL[] = " RemasterTitles LIKE '%|" . db_string(trim(EditionInfo::text(trim($RTN)))) . "|%' ";
            }
        }
        $RemasterTitleSQL[] = "RemasterTitles = ''";
        $SQL .= " AND (" . implode(' OR ', $RemasterTitleSQL) . ") ";

        // user
        $SQL .= " AND (Users LIKE '%|" . $this->user['ID'] . "|%' OR Users = '') ";
        $SQL .= " AND !(NotUsers LIKE '%|" . $this->user['ID'] . "|%') ";
        $SQL .= " AND UserID != '" . $this->user['ID'] . "' ";

        $this->db->query($SQL);

        if ($this->db->has_results()) {
            $UserArray = $this->db->to_array('UserID');
            $FilterArray = $this->db->to_array('ID');

            $InsertSQL = "INSERT IGNORE INTO users_notify_torrents (UserID, GroupID, TorrentID, FilterID) VALUES ";
            $Rows = array();
            foreach ($UserArray as $User) {
                list($FilterID, $UserID, $Passkey) = $User;
                $Rows[] = "('$UserID', '$GroupID', '$TorrentID', '$FilterID')";
                $this->feed->populate("torrents_notify_$Passkey", $Item);
                $this->cache->delete_value("notifications_new_$UserID");
            }
            $InsertSQL .= implode(',', $Rows);
            $this->db->query($InsertSQL);
            foreach ($FilterArray as $Filter) {
                list($FilterID, $UserID, $Passkey) = $Filter;
                $this->feed->populate("torrents_notify_{$FilterID}_$Passkey", $Item);
            }
        }

        // RSS for bookmarks
        $this->db->query("SELECT u.ID, u.torrent_pass FROM users_main AS u JOIN bookmarks_torrents AS b ON b.UserID = u.ID WHERE b.GroupID = $GroupID");
        while (list($UserID, $Passkey) = $this->db->next_record()) {
            $this->feed->populate("torrents_bookmarks_t_$Passkey", $Item);
        }

        // RSS for all
        $this->feed->populate('torrents_all', $Item);
    }
}
