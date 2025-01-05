<?
class Top10Movies {
    private $BaseQuery = "
    SELECT
      g.ID,
      g.Name,
      g.SubName,
      g.IMDBRating,
      g.Year,
      g.WikiImage,
      GROUP_CONCAT(DISTINCT tags.Name ORDER BY `TagID` SEPARATOR ' ') as TagList
    FROM torrents AS t
    LEFT JOIN torrents_group AS g ON g.ID = t.GroupID
    LEFT JOIN torrents_tags AS tt ON tt.GroupID = g.ID
    LEFT JOIN tags ON tags.ID = tt.TagID
    {rest}
    GROUP BY g.ID
    ORDER BY {order} DESC
    LIMIT {limit}
  ";

    public function getData($Name, $Options = []) {
        global $Cache, $DB;
        $Options = array_merge(['Limit' => 10], $Options);
        $Limit = $Options['Limit'];
        $Key = "top10_movies_${Name}_${Limit}";
        $Data = $Cache->get_value($Key);
        if ($Data !== false) {
            return $Data;
        }
        if (!$Cache->get_query_lock($Key)) {
            return false;
        }
        $Query = strtr($this->BaseQuery, [
            '{rest}' => $this->rest($Name),
            '{order}' => $this->order($Name),
            '{limit}' => $Limit,
        ]);
        $DB->query($Query);
        $Data = $DB->to_array(false, MYSQLI_ASSOC);
        $Cache->cache_value($Key, $Data, 3600 * 6);
        $Cache->clear_query_lock($Key);
        return $Data;
    }

    private function rest($Name) {
        switch ($Name) {
            case 'active_day':
                return "WHERE t.Seeders > 0 AND t.Time > now() - INTERVAL 1 day";
            case 'active_week':
                return "WHERE t.Seeders > 0 AND t.Time > now() - INTERVAL 1 week";
            case 'active_month':
                return "WHERE t.Seeders > 0 AND t.Time > now() - INTERVAL 1 month";
            case 'active_year':
                // IMPORTANT NOTE - we use WHERE t.Seeders>200 in order to speed up this query. You should remove it!
                return "WHERE t.Seeders > 0 AND t.Time > now() - INTERVAL 1 year";
            case 'active_all':
                // IMPORTANT NOTE - we use WHERE t.Seeders>500 in order to speed up this query. You should remove it!
                return "WHERE t.Seeders > 0";
            case 'snatched':
                return "WHERE t.Snatched > 0";
            case 'data':
                // IMPORTANT NOTE - we use WHERE t.Snatched>100 in order to speed up this query. You should remove it!
                return "WHERE t.Snatched > 0";
            case 'seeded':
                return "WHERE t.Seeders > 0";
        }
    }

    private function order($Name) {
        switch ($Name) {
            case 'active_day':
            case 'active_week':
            case 'active_month':
            case 'active_year':
            case 'active_all':
                return 'sum(t.Seeders + t.Leechers)';
            case 'snatched':
                return 'sum(t.Snatched)';
            case 'data':
                return 'sum((t.Size * t.Snatched) + (t.Size * 0.5 * t.Leechers))';
            case 'seeded':
                return 'sum(t.Seeders)';
        }
    }
}
