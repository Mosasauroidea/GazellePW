<?php

namespace Gazelle\Top10;

class User extends \Gazelle\Base {
    public const UPLOADERS = 'uploaders';
    public const DOWNLOADERS = 'downloaders';
    public const UPLOADS = 'uploads';
    public const BONUSPOINTS = 'bonus_points';
    public const SEEDINGSIZE = 'bonus_points';

    private const CACHE_KEY = 'topusers_%s_%d';

    private $sortMap = [
        self::UPLOADERS => 'uploaded',
        self::DOWNLOADERS => 'downloaded',
        self::UPLOADS => 'num_uploads',
        self::BONUSPOINTS => 'bonus_points',
        self::SEEDINGSIZE => 'seeding_size',
    ];

    public function fetch(string $type, int $limit) {
        if (!array_key_exists($type, $this->sortMap)) {
            return [];
        }

        if (!$results = $this->cache->get_value(sprintf(self::CACHE_KEY, $type, $limit))) {
            $orderBy = $this->sortMap[$type];
            $this->db->prepared_query(
                sprintf(
                    "
                SELECT
                    um.ID AS id,
                    ui.JoinDate AS join_date,
                    uls.Uploaded AS uploaded,
                    uls.Downloaded AS downloaded,
                    coalesce(bs.Bounty, 0) AS request_votes,
                    coalesce(bf.Fills, 0) AS request_fills,
                    abs(uls.Uploaded - ?) / (unix_timestamp() - unix_timestamp(ui.JoinDate)) AS up_speed,
                    uls.Downloaded / (unix_timestamp() - unix_timestamp(ui.JoinDate)) AS down_speed,
                    count(t.ID) AS num_uploads
                FROM users_main AS um
                INNER JOIN users_info AS ui ON (ui.UserID = um.ID)
                INNER JOIN users_leech_stats AS uls ON (uls.UserID = um.ID)
                LEFT JOIN torrents AS t ON (t.UserID = um.ID)
                LEFT JOIN
                (
                    SELECT UserID, sum(Bounty) AS Bounty
                    FROM requests_votes
                    GROUP BY UserID
                ) AS bs ON (bs.UserID = um.ID)
                LEFT JOIN
                (
                    SELECT FillerID, count(*) AS Fills
                    FROM requests
                    GROUP BY FillerID
                ) AS bf ON (bf.FillerID = um.ID)
                WHERE um.Enabled = '1'
                    AND uls.Uploaded > ?
                    AND uls.Downloaded > ?
                    AND (um.Paranoia IS NULL OR (um.Paranoia NOT LIKE '%%\"uploaded\"%%' AND um.Paranoia NOT LIKE '%%\"downloaded\"%%'))
                GROUP BY um.ID
                ORDER BY %s DESC
                LIMIT ?",
                    $orderBy
                ),
                CONFIG['STARTING_UPLOAD'],
                5 * 1024 * 1024 * 1024,
                5 * 1024 * 1024 * 1024,
                $limit
            );

            $results = $this->db->to_array();
            $this->cache->cache_value(sprintf(self::CACHE_KEY, $type, $limit), $results, 3600 * 12);
        }

        return $results;
    }
}
