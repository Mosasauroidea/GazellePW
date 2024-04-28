<?php

class RecommendGroups
{

    const REASON_BUY = 'buy';

    public static function get_recommend_group_for_index(): array
    {
        $QueryID = G::$DB->get_query_id();

        G::$DB->prepared_query('
            SELECT
                g.ID,
                g.Name,
                g.SubName,
                g.Year,
                g.WikiImage,
                rg.EndTime
            FROM recommend_group AS rg
            LEFT JOIN torrents_group AS g ON rg.GroupID = g.ID
            WHERE rg.EndTime > ?
            ORDER BY rg.EndTime DESC
            LIMIT 10', sqltime());

        $RecommendGroups = G::$DB->to_array(false, MYSQLI_ASSOC);

        G::$DB->set_query_id($QueryID);

        return $RecommendGroups;
    }

    public static function recommend_group_buy($UserID, $GroupID, $EndTime)
    {
        self::recommend_group($UserID, $GroupID, $EndTime, self::REASON_BUY);
    }

    public static function recommend_group($UserID, $GroupID, $EndTime, $Reason)
    {
        $QueryID = G::$DB->get_query_id();

        G::$DB->prepared_query('
            INSERT INTO recommend_group
                   (`UserID`, `GroupID`, `EndTime`, `Reason`)
            VALUES (?, ?, ?, ?)', $UserID, $GroupID, $EndTime, $Reason);

        G::$DB->set_query_id($QueryID);
    }


}