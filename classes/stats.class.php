<?
class Stats {
  const CACHE_TIME = 24 * 3600 * 1000;

  public static function torrentByDay() {
  }

  public static function torrentByMonth() {
  }

  public static function torrentByYear() {
    global $Cache, $DB, $WINDOW_DATA;
    $DataKey = 'statsTorrentByYear';
    // $Cache->delete_value($DataKey);
    $Data = $Cache->get_value($DataKey);
    if (!$Data) {
      $DB->query(
        "
          SELECT DATE_FORMAT(l.Time, '%Y') AS Date, Count(l.ID) AS Count
          FROM log l
          WHERE l.Message LIKE 'Torrent % was uploaded by %'
          GROUP BY Date
          ORDER BY Date DESC
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
}
