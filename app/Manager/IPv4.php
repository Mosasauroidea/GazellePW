<?php

namespace Gazelle\Manager;

class IP extends \Gazelle\Base {
    const CACHE_KEY = 'ip_bans_';

    /**
     * Create an ip address ban over a range of addresses. Will append
     * the given reason to an existing ban.
     *
     * @param int $userId The person doing the band (0 for system)
     * @param string $from The first address (dotted quad a.b.c.d)
     * @param string $to The last adddress in the range (may equal $from)
     * @param string $reason Why ban?
     */
    public function createBan(int $userId,  $ipFrom, string $ipTo, string $reason) {
        $current = $this->db->scalar(
            '
            SELECT Reason
            FROM ip_bans
            WHERE INET6_ATON(?) BETWEEN FromIP AND ToIP
            ',
            $ipFrom
        );

        if ($current) {
            if ($current != $reason) {
                $this->db->prepared_query(
                    "
                    UPDATE ip_bans SET
                        Reason = concat(?, ' AND ', Reason),
                        user_id = ?,
                        created = now()
                    WHERE FromIP = INET6_ATON(?)
                        AND ToIP = INET6_ATON(?)
                    ",
                    $reason,
                    $userId,
                    $ipFrom,
                    $ipTo
                );
            }
        } else { // Not yet banned
            $this->db->prepared_query(
                "
                INSERT INTO ip_bans
                       (Reason, FromIP, ToIP, user_id)
                VALUES (?,      ?,      INET6_ATON(?),    INET6_ATON(?))
                ",
                $reason,
                $ipFrom,
                $ipTo,
                $userId
            );
            $this->cache->delete_value(
                self::CACHE_KEY . substr($ipFrom, 0, strcspn($ipFrom, '.'))
            );
        }
    }

    /**
     * Remove an ip ban
     *
     * param int $id Row to remove
     */
    public function removeBan(int $id) {
        $fromClassA = $this->db->scalar(
            "
            SELECT INET6_NTOA(FromIP) FROM ip_bans WHERE ID = ?
            ",
            $id
        );
        if (is_null($fromClassA)) {
            return;
        }
        $this->db->prepared_query(
            "
            DELETE FROM ip_bans WHERE ID = ?
            ",
            $id
        );
        if ($this->db->affected_rows()) {
            $this->cache->delete_value(self::CACHE_KEY . $fromClassA);
        }
    }
}
