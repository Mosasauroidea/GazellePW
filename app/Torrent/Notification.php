<?

namespace Gazelle\Torrent;

use Gazelle\Base;
use FEED, Text, Torrents, Lang, Tags;

class Notification extends Base {
    private $feed;
    public function __construct() {
        parent::__construct();
        $this->feed = new FEED;
    }

    private function buildBody($Properties) {
        $Body = '';
        if (empty($Properties['WikiBody'])) {
            $Body = $Properties['MainWikiBody'];
        } else if (empty($Properties["MainWikiBody"])) {
            $Body = $Properties['WikiBody'];
        } else {
            $Body = $Properties['WikiBody'] . "\n\n" . $Properties['MainWikiBody'];
        }
        return $Body;
    }

    private function buildTitle($Properties) {
        $Title = Torrents::display_simple_group_name($Properties['Group'], null, false);
        $Title .= ' [' . t('server.torrents.release_types', ['Lang' => Lang::EN])[$Properties['Group']['ReleaseType']] . ']';
        $Details = '';
        $Details .= trim($Properties['Codec']) . ' / ' . trim($Properties['Source']) . ' / ' . trim($Properties['Resolution']) . ' / ' . trim($Properties['Container']);
        if (!empty(trim($Properties['Processing'])) && trim($Properties['Processing']) != '---' && trim($Properties['Processing']) != ' Encode') {
            $Details .= ' / ' . trim($Properties['Processing']);
        }
        if ($Properties['Scene'] == '1') {
            $Details .= ' / Scene';
        }

        $RemasterTitleRSS = explode(' / ', $Properties['RemasterTitle']);
        foreach ($RemasterTitleRSS as $RT) {
            if ($RT) {
                $Details .= " / " . EditionInfo::text(trim($RT), Lang::EN);
            }
        }

        if ($Properties['FreeTorrent'] == Torrents::FREE) {
            $Details .= ' / Freeleech!';
        } else if ($Properties['FreeTorrent'] == Torrents::Neutral) {
            $Details .= ' / Neutral Leech!';
        } else if ($Properties['FreeTorrent'] == Torrents::OneFourthOff) {
            $Details .= ' / 25% off!';
        } else if ($Properties['FreeTorrent'] == Torrents::TwoFourthOff) {
            $Details .= ' / 50% off!';
        } else if ($Properties['FreeTorrent'] == Torrents::ThreeFourthOff) {
            $Details .= ' / 75% off!';
        }

        if ($Details !== "") {
            $Title .= " - " . $Details;
        }
        return $Title;
    }

    public function notify($Properties, $IsNewGroup) {
        $GroupID = $Properties['GroupID'];
        $TorrentID = $Properties['TorrentID'];
        $TotalSize = $Properties['Size'];
        $Group = $Properties['Group'];

        $Title = $this->buildTitle($Properties);
        $Body = $this->buildBody($Properties['Group']);

        // For RSS
        $Tags = explode(',', $Group['TagList']);
        $TagList = implode(',', Tags::main_name($Tags));
        $Item = $this->feed->item($Title, Text::strip_bbcode($Body), 'torrents.php?action=download&amp;authkey=[[AUTHKEY]]&amp;torrent_pass=[[PASSKEY]]&amp;id=' . $TorrentID, $this->user['Username'], 'torrents.php?id=' . $GroupID, trim($TagList));


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
        $SQL .= " AND (ReleaseTypes LIKE '%|" . db_string($Group['ReleaseType']) . "|%' OR ReleaseTypes = '') ";

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
        } else if ($Properties['FreeTorrent']) {
            $SQL .= " AND (FreeTorrents LIKE '%|" . db_string(trim($Properties['FreeTorrent'])) . "|%' OR FreeTorrents = '') ";
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
        if ($Group['Year'] && $Properties['RemasterYear']) {
            $SQL .= " AND (('" . db_string(trim($Group['Year'])) . "' BETWEEN FromYear AND ToYear)
			OR ('" . db_string(trim($Properties['RemasterYear'])) . "' BETWEEN FromYear AND ToYear)
			OR (FromYear = 0 AND ToYear = 0)) ";
        } elseif ($Group['Year'] || $Properties['RemasterYear']) {
            $SQL .= " AND (('" . db_string(trim(Max($Group['Year'], $Properties['RemasterYear']))) . "' BETWEEN FromYear AND ToYear)
			OR (FromYear = 0 AND ToYear = 0)) ";
        } else {
            $SQL .= " AND (FromYear = 0 AND ToYear = 0) ";
        }

        //  imdb rating
        $IMDBRating = $Group['IMDBRating'];
        if ($IMDBRating && $IMDBRating != 'N/A') {
            $SQL .= " AND (" .  $IMDBRating . " > FromIMDBRating OR FromIMDBRating = 0)";
        }

        // Region 
        $Region = $Group['Region'];
        if ($Region) {
            foreach (explode(',', $Region) as $R) {
                $RegionSQL[] = " Regions LIKE '%|" . db_string(trim($R)) . "|%' ";
            }
        }
        $RegionSQL[] = "Regions = ''";
        $SQL .= " AND (" . implode(' OR ', $RegionSQL) . ") ";

        // Language
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
        $this->feed->populate('torrents_movie', $Item);
        if ($Properties['FreeTorrent'] == Torrents::FREE) {
            $this->feed->populate('torrents_free', $Item);
        }
    }

    function edit_notify($Properties) {
        if ($Properties['FreeTorrent'] != Torrents::FREE) {
            return;
        }
        $Title = $this->buildTitle($Properties);
        $Body = $this->buildBody($Properties['Group']);

        $GroupID = $Properties['GroupID'];
        $TorrentID = $Properties['ID'];
        $Tags = explode(',', $Properties['Group']['TagList']);
        $TagList = implode(',', Tags::main_name($Tags));

        $Item = $this->feed->item($Title, Text::strip_bbcode($Body), 'torrents.php?action=download&amp;authkey=[[AUTHKEY]]&amp;torrent_pass=[[PASSKEY]]&amp;id=' . $TorrentID, $this->user['Username'], 'torrents.php?id=' . $GroupID, trim($TagList));
        $this->feed->populate('torrents_free', $Item);
    }
}
