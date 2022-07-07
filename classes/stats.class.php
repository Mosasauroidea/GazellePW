<?
class Stats {
  const CACHE_TIME = 1656720000;

  public static function torrentByMonth() {
    global $Cache, $DB, $WINDOW_DATA;
    $Data = $Cache->get_value('stats_torrent_by_month');
    if (!$Data) {
      $DB->query(
        "
		SELECT DATE_FORMAT(Time,'%b %y') AS Date, COUNT(ID) AS Count
		FROM log
		WHERE Message LIKE 'Torrent % was uploaded by %'
		GROUP BY Date
		ORDER BY Time DESC
		LIMIT 1, 12
        "
      );
      $TimelineIn = array_reverse($DB->to_array());
      // $DB->query(
      //   "
      // 	SELECT DATE_FORMAT(Time,'%b %y') AS Date, COUNT(ID) As Count
      // 	FROM log
      // 	WHERE Message LIKE 'Torrent % was deleted %'
      // 	GROUP BY Date
      // 	ORDER BY Time DESC
      // 	LIMIT 1, 12
      //   "
      // );
      // $TimelineOut = array_reverse($DB->to_array());
      // $DB->query(
      //   "
      // 	SELECT DATE_FORMAT(Time,'%b %y') AS Date, COUNT(ID) As Count
      // 	FROM torrents
      // 	GROUP BY Date
      // 	ORDER BY Time DESC
      // 	LIMIT 1, 12
      //   "
      // );
      // $TimelineNet = array_reverse($DB->to_array());
      $Data = [];
      foreach ($TimelineIn as $Index => $Value) {
        $Data[] = [
          'date' => $TimelineIn[$Index]['Date'],
          'in' => (int) $TimelineIn[$Index]['Count'] ?: 0,
          // 'out' => (int) $TimelineOut[$Index]['Count'] ?: 0,
          // 'net' => (int) $TimelineNet[$Index]['Count'] ?: 0,
        ];
      }
      $Cache->cache_value('stats_torrent_by_month', $Data, self::CACHE_TIME);
    }
    $WINDOW_DATA['ChartTorrentByMonth'] = $Data;
  }

  public static function torrentByDay() {
    global $Cache, $DB, $WINDOW_DATA;
    $CacheKey = 'statsTorrentByDay';
    $DataKey = 'statsTorrentByDay';
    $Data = $Cache->get_value($CacheKey);
    if (!$Data) {
      $DB->query(
        "
          WITH RECURSIVE 
            dates AS (
              SELECT CURRENT_DATE() - interval 6 day AS Date
              UNION ALL 
              SELECT date + INTERVAL 1 DAY FROM dates WHERE date < CURRENT_DATE()
            ), 
            logs AS (
              select DATE_FORMAT(l.Time, '%Y-%m-%d') Date, l.*
              FROM log l
              WHERE l.Message LIKE 'Torrent % was uploaded by %'
            )
          SELECT d.Date as Date, count(l.ID) AS Count
          FROM dates d
          LEFT JOIN logs l on d.Date = l.Date
          GROUP BY d.Date
          ORDER BY d.Date DESC
        "
      );
      $Uploads = array_reverse($DB->to_array());
      $Data = [];
      foreach ($Uploads as $Index => $Value) {
        $Data[] = [
          'date' => $Uploads[$Index]['Date'],
          'uploads' => (int) $Uploads[$Index]['Count'] ?: 0,
        ];
      }
      $Cache->cache_value($CacheKey, $Data, self::CACHE_TIME);
    }
    $WINDOW_DATA[$DataKey] = $Data;
  }
}
