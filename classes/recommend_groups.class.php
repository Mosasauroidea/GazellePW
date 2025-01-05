<?php

class RecommendGroups {

    const REASON_BUY = 'buy';

    public static function get_recommend_group_for_index(): array {
        $RecommendGroups = G::$Cache->get_value("recommend_group");
        if ($RecommendGroups === false) {
            $QueryID = G::$DB->get_query_id();
            G::$DB->prepared_query("
            SELECT
                DISTINCT g.ID,
                g.Name,
                g.SubName,
                g.IMDBRating,
                g.Year,
                g.WikiImage,
                rg.EndTime,
                GROUP_CONCAT(DISTINCT tags.Name ORDER BY `TagID` SEPARATOR ' ') as TagList
            FROM recommend_group AS rg
               RIGHT JOIN torrents_group AS g ON rg.GroupID = g.ID
               LEFT JOIN torrents_tags AS tt ON tt.GroupID = g.ID
               LEFT JOIN tags ON tags.ID = tt.TagID
            WHERE rg.EndTime > ?
            GROUP BY rg.GroupID
            ORDER BY rg.EndTime DESC
            LIMIT 10", sqltime());

            $RecommendGroups = G::$DB->to_array(false, MYSQLI_ASSOC);

            G::$DB->set_query_id($QueryID);
            G::$Cache->cache_value("recommend_group", $RecommendGroups);
        }

        return $RecommendGroups;
    }

    public static function recommend_group_buy($UserID, $GroupID, $EndTime) {
        self::recommend_group($UserID, $GroupID, $EndTime, self::REASON_BUY);
    }

    public static function recommend_group($UserID, $GroupID, $EndTime, $Reason) {
        $QueryID = G::$DB->get_query_id();

        G::$DB->prepared_query('
            INSERT INTO recommend_group
                   (`UserID`, `GroupID`, `EndTime`, `Reason`)
            VALUES (?, ?, ?, ?)', $UserID, $GroupID, $EndTime, $Reason);

        G::$DB->set_query_id($QueryID);
        G::$Cache->delete_value("recommend_group");
    }
}
