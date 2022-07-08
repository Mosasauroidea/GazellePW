<?
class Stats {
  const CACHE_TIME = 24 * 3600 * 1000;

  public static function torrentByDay() {
    global $Cache, $DB, $WINDOW_DATA;
    $DataKey = 'statsTorrentByDay';
    // $Cache->delete_value($DataKey);
    $Data = $Cache->get_value($DataKey);
    if (!$Data) {
      $DB->query(
        "
          WITH RECURSIVE 
            dates AS (
              SELECT CURRENT_DATE() - INTERVAL 6 DAY AS Date
              UNION ALL 
              SELECT Date + INTERVAL 1 DAY FROM dates WHERE Date < CURRENT_DATE()
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
      $Cache->cache_value($DataKey, $Data, self::CACHE_TIME);
    }
    $WINDOW_DATA[$DataKey] = $Data;
  }

  public static function torrentByMonth() {
    global $Cache, $DB, $WINDOW_DATA;
    $DataKey = 'statsTorrentByMonth';
    // $Cache->delete_value($DataKey);
    $Data = $Cache->get_value($DataKey);
    if (!$Data) {
      $DB->query(
        "
          WITH RECURSIVE 
            dates AS (
              SELECT DATE_FORMAT(NOW(), '%Y-%m-01') - interval 11 MONTH AS Date
              UNION ALL 
              SELECT Date + INTERVAL 1 MONTH FROM dates WHERE Date < DATE_FORMAT(NOW(), '%Y-%m-01')
            ),
            logs AS (
              select DATE_FORMAT(l.Time, '%Y-%m-01') Date, l.*
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
      $Cache->cache_value($DataKey, $Data, self::CACHE_TIME);
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
    }
    $WINDOW_DATA[$DataKey] = $Data;
  }
}
