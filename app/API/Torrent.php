<?php

namespace Gazelle\API;

class Torrent extends AbstractAPI {
    public function run() {
        switch ($_GET['req']) {
            case 'group':
                return $this->getGroup();
            case 'torrent':
                return $this->getTorrent();
            default:
                return $this->getTorrent();
        }
    }

    private function getTorrent() {
        if (isset($_GET['torrent_id'])) {
            $this->db->prepared_query("
                SELECT
                    tg.ID,
                    tg.Name,
                    tg.Year,
                    tg.ReleaseType AS ReleaseTypeID,
                    t.Codec,
                    t.RemasterTitle,
                    t.Resolution,
                    t.Container,
                    t.Processing,
                    t.Source,
                    t.Snatched,
                    t.Seeders,
                    t.Leechers
                FROM
                    torrents AS t
                    INNER JOIN torrents_group AS tg ON (tg.ID = t.GroupID)
                WHERE
                    t.ID = ?", $_GET['torrent_id']);
        } elseif (isset($_GET['imdbID'])) {
            $this->db->prepared_query("
                SELECT
                    tg.ID,
                    tg.Name,
                    tg.Year,
                    tg.IMDBID,
                    tg.ReleaseType AS ReleaseTypeID,
                    t.Codec,
                    t.RemasterTitle,
                    t.Resolution,
                    t.Container,
                    t.Processing,
                    t.Source
                FROM
                    torrents_group AS tg
                    INNER JOIN torrents AS t ON (tg.ID = t.GroupID)
                WHERE
                    tg.IMDBID = ?", $_GET['imdbID']);
        } else {
            json_error('Missing torrent id and imdbid');
        }

        if (!$this->db->has_results()) {
            json_error('Torrent not found');
        }
        $response = array();
        for ($x = 0; $x < $this->db->record_count(); $x++) {
            $torrent = $this->db->next_record(MYSQLI_ASSOC, false);
            array_push($response, $torrent);
        }
        return $response;
    }

    private function getGroup() {
        if (isset($_GET['group_id'])) {
            $this->db->prepared_query("
                SELECT
                    ID,
                    Name,
                    Year,
                    ReleaseType AS ReleaseTypeID
                FROM
                    torrents_group
                WHERE
                    ID = ?", $_GET['group_id']);
        } elseif (isset($_GET['imdbID'])) {
            $this->db->prepared_query("
                SELECT
                    ID,
                    Name,
                    Year,
                    ReleaseType AS ReleaseTypeID
                FROM
                    torrents_group
                WHERE
                    IMDBID = ?", $_GET['imdbID']);
        } else {
            json_error('Missing group id and imdbid');
        }

        if (!$this->db->has_results()) {
            json_error('Group not found');
        }
        $group = $this->db->next_record(MYSQLI_ASSOC, false);
        $group['ReleaseType'] = $group['ReleaseTypeID'];
        $artists = \Artists::get_artist($group['ID']);
        $group['Artists'] = $artists;
        $group['DisplayArtists'] = \Artists::display_artists(
            $artists,
            false,
            false,
            false
        );
        return $group;
    }
}
