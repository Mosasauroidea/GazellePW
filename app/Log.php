<?php

namespace Gazelle;

class Log extends Base {
    /**
     * Write a general message to the system log.
     *
     * @param string $message the message to write.
     */
    public function general(string $message) {
        $qid = $this->db->get_query_id();
        $this->db->prepared_query(
            "
            INSERT INTO log (Message) VALUES (?)
            ",
            trim($message)
        );
        $this->db->set_query_id($QueryID);
        return $this;
    }

    /**
     * Write a group entry
     *
     * @param int $groupId
     * @param int $userId
     * @param string $message
     */
    public function group(int $groupId, int $userId, $message) {
        $qid = $this->db->get_query_id();
        $this->db->prepared_query(
            "
            INSERT INTO group_log
                   (GroupID, UserID, Info, TorrentID, Hidden)
            VALUES (?,       ?,      ?,    0,         0)
            ",
            $groupId,
            $userId,
            $message
        );
        $this->db->set_query_id($qid);
        return $this;
    }

    /**
     * Write a torrent entry
     *
     * @param int $groupId
     * @param int $torrentId
     * @param int $userId
     * @param string $message
     */
    public function torrent(int $groupId, int $torrentId, int $userId, $message) {
        $qid = $this->db->get_query_id();
        $this->db->prepared_query(
            "
            INSERT INTO group_log
                   (GroupID, TorrentID, UserID, Info, Hidden)
            VALUES (?,       ?,         ?,      ?,    0)
            ",
            $groupId,
            $torrentId,
            $userId,
            $message
        );
        $this->db->set_query_id($qid);
        return $this;
    }
}
