<? View::show_header(Lang::get('stats', 'stats'), '', 'PageStatsHome'); ?>

<?
$Cache->delete_value('stats_torrent_by_month');
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
    $Data['ChartTorrentByMonth'] = [
      'date' => $TimelineIn[$Index]['Date'],
      'in' => (int) $TimelineIn[$Index]['Count'] ?: 0,
      // 'out' => (int) $TimelineOut[$Index]['Count'] ?: 0,
      // 'net' => (int) $TimelineNet[$Index]['Count'] ?: 0,
    ];
  }
  $Cache->cache_value('stats_torrent_by_month', $Data, mktime(0, 0, 0, date('n') + 1, 2));
}
?>

<div id="root"></div>

<?
// Stats::torrentByMonth();
if (IS_DEV) {
  Stats::torrentByYear();
}
View::show_footer([], 'stats/index');
