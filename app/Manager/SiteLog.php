<?php

namespace Gazelle\Manager;

class SiteLog extends \Gazelle\Base {
    protected $debug;
    protected $logQuery;
    protected $totalMatches;
    protected $queryStatus;
    protected $queryError;
    protected $qid;
    protected $usernames;

    public function __construct(\DEBUG $debug) {
        parent::__construct();
        $this->debug = $debug;
        $this->usernames = [];
    }

    public function totalMatches() {
        return $this->totalMatches;
    }
    public function error() {
        return $this->queryStatus;
    }
    public function errorMessage() {
        return $this->queryError;
    }

    public function next() {
        $this->db->set_query_id($this->qid);
        while ($result = $this->db->next_record(MYSQLI_NUM, false)) {
            yield $result;
            $this->db->set_query_id($this->qid);
        }
    }

    public function load(int $page, int $offset, string $searchTerm) {
        if ($searchTerm === '') {
            $this->logQuery = $this->db->prepared_query(
                "
                SELECT ID, Message, Time
                FROM log
                ORDER BY ID DESC
                LIMIT ?, ?
                ",
                $offset,
                CONFIG['LOG_ENTRIES_PER_PAGE']
            );
            $this->totalMatches = $this->db->record_count();
            if ($this->totalMatches == CONFIG['LOG_ENTRIES_PER_PAGE']) {
                // This is a lot faster than SQL_CALC_FOUND_ROWS
                $sq = new \SphinxqlQuery();
                $result = $sq->select('id')->from('log, log_delta')->limit(0, 1, 1)->query();
                $this->debug->log_var($result, '$result');
                $this->totalMatches = min(CONFIG['SPHINX_MAX_MATCHES'], $result->get_meta('total_found'));
            } else {
                $this->totalMatches += $offset;
            }
            $this->queryStatus = 0;
        } else {
            $page = min(CONFIG['SPHINX_MAX_MATCHES'] / CONFIG['TORRENTS_PER_PAGE'], $page);
            $sq = new \SphinxqlQuery();
            $sq->select('id')
                ->from('log, log_delta')
                ->order_by('id', 'DESC')
                ->limit($offset, CONFIG['LOG_ENTRIES_PER_PAGE'], $offset + CONFIG['LOG_ENTRIES_PER_PAGE']);
            foreach (explode(' ', $searchTerm) as $s) {
                $sq->where_match($s, 'message');
            }
            $result = $sq->query();
            $this->debug->log_var($result, '$result');
            $this->debug->set_flag('Finished SphQL query');
            if ($this->queryStatus = $result->Errno) {
                $this->queryError = $result->Error;
                $this->logQuery = $this->db->prepared_query('SET @nothing = 0');
            } else {
                $this->totalMatches = min(CONFIG['SPHINX_MAX_MATCHES'], $result->get_meta('total_found'));
                $logIds = $result->collect('id') ?: [0];
                $this->logQuery = $this->db->prepared_query(
                    "
                    SELECT ID, Message, Time
                    FROM log
                    WHERE ID IN (" . placeholders($logIds) . ")
                    ORDER BY ID DESC
                    ",
                    ...$logIds
                );
            }
        }
        $this->qid = $this->db->get_query_id();
    }

    public function colorize(string $logMessage) {
        $messageParts = explode(' ', $logMessage);
        $message = '';
        $color = $colon = false;
        for ($i = 0, $n = count($messageParts); $i < $n; $i++) {
            if (strpos($messageParts[$i], CONFIG['SITE_URL']) === 0) {
                $offset = strlen(CONFIG['SITE_URL']) + 1; // trailing slash
                $messageParts[$i] = '<a href="' . substr($messageParts[$i], $offset) . '">' . substr($messageParts[$i], $offset) . '</a>';
            }
            switch ($messageParts[$i]) {
                case 'Torrent':
                case 'torrent':
                    $TorrentID = $messageParts[$i + 1];
                    if ((int)$TorrentID) {
                        $message .= ' ' . $messageParts[$i++] . " <a href=\"torrents.php?torrentid=$TorrentID\">$TorrentID</a>";
                    } else {
                        $message .= ' ' . $messageParts[$i];
                    }
                    break;
                case 'Request':
                    $RequestID = $messageParts[$i + 1];
                    if ((int)$RequestID) {
                        $message .= ' ' . $messageParts[$i++] . " <a href=\"requests.php?action=view&amp;id=$RequestID\">$RequestID</a>";
                    } else {
                        $message .= ' ' . $messageParts[$i];
                    }
                    break;
                case 'Artist':
                case 'artist':
                    $ArtistID = $messageParts[$i + 1];
                    if ((int)$ArtistID) {
                        $message .= ' ' . $messageParts[$i++] . " <a href=\"artist.php?id=$ArtistID\">$ArtistID</a>";
                    } else {
                        $message .= ' ' . $messageParts[$i];
                    }
                    break;
                case 'Group':
                case 'group':
                    $GroupID = $messageParts[$i + 1];
                    if ((int)$GroupID) {
                        $message .= ' ' . $messageParts[$i] . " <a href=\"torrents.php?id=$GroupID\">$GroupID</a>";
                    } else {
                        $message .= ' ' . $messageParts[$i];
                    }
                    $i++;
                    break;
                case 'by':
                    $userId = 0;
                    $user = '';
                    $URL = '';
                    if ($messageParts[$i + 1] == 'user') {
                        $i++;
                        if ((int)($messageParts[$i + 1])) {
                            $userId = $messageParts[++$i];
                        }
                        $URL = "user $userId (<a href=\"user.php?id=$userId\">" . substr($messageParts[++$i], 1, -1) . '</a>)';
                    } elseif (in_array($messageParts[$i - 1], ['deleted', 'uploaded', 'edited', 'created', 'recovered'])) {
                        $user = $messageParts[++$i];
                        if (substr($user, -1) == ':') {
                            $user = substr($user, 0, -1);
                            $colon = true;
                        }
                        if (!isset($this->usernames[$user])) {
                            $userId = $this->usernameLookup($user);
                            $this->usernames[$user] = $userId ? $userId : '';
                        } else {
                            $userId = $this->usernames[$user];
                        }
                        $this->db->set_query_id($Log);
                        $URL = $this->usernames[$user] ? "<a href=\"user.php?id=$userId\">$user</a>" . ($colon ? ':' : '') : $user;
                    }
                    $message .= " by $URL";
                    break;
                case 'uploaded':
                    if ($color === false) {
                        $color = 'forestgreen';
                    }
                    $message .= ' ' . $messageParts[$i];
                    break;
                case 'deleted':
                    if ($color === false || $color === 'forestgreen') {
                        $color = 'crimson';
                    }
                    $message .= ' ' . $messageParts[$i];
                    break;
                case 'edited':
                    if ($color === false) {
                        $color = 'royalblue';
                    }
                    $message .= ' ' . $messageParts[$i];
                    break;
                case 'un-filled':
                    if ($color === false) {
                        $color = '';
                    }
                    $message .= ' ' . $messageParts[$i];
                    break;
                case 'marked':
                    if ($i == 1) {
                        $user = $messageParts[$i - 1];
                        if (!isset($this->usernames[$user])) {
                            $userId = $this->usernameLookup($user);
                            $this->usernames[$user] = $userId ? $userId : '';
                        } else {
                            $userId = $this->usernames[$user];
                        }
                        $URL = $this->usernames[$user] ? "<a href=\"user.php?id=$userId\">$user</a>" : $user;
                        $message = $URL . " " . $messageParts[$i];
                    } else {
                        $message .= ' ' . $messageParts[$i];
                    }
                    break;
                case 'Collage':
                    $CollageID = $messageParts[$i + 1];
                    if (is_numeric($CollageID)) {
                        $message .= ' ' . $messageParts[$i] . " <a href=\"collages.php?id=$CollageID\">$CollageID</a>";
                        $i++;
                    } else {
                        $message .= ' ' . $messageParts[$i];
                    }
                    break;
                default:
                    $message .= ' ' . $messageParts[$i];
            }
        }
        return [$color, $message];
    }

    protected function usernameLookup(string $username) {
        return $this->db->scalar(
            "
            SELECT ID FROM users_main WHERE Username = ?
            ",
            $username
        );
    }
}
