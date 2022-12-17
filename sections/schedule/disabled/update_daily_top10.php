<?php

// Daily top 10 history.
$DB->query("
		INSERT INTO top10_history (Date, Type)
		VALUES ('$sqltime', 'Daily')");
$HistoryID = $DB->inserted_id();

$Top10 = $Cache->get_value('top10tor_day_10');
if ($Top10 === false) {
    $DB->query("
			SELECT
				t.ID as TorrentID,
				g.ID,
				g.Name,
                g.SubName,
				g.CategoryID,
				t.Scene,
				t.RemasterYear,
				g.Year,
				t.RemasterTitle,
				t.Snatched,
				t.Seeders,
				t.Leechers,
                t.Codec,
                t.Processing,
                t.Source, 
                t.Container,
                t.Resolution,
                t.Size,
				((t.Size * t.Snatched) + (t.Size * 0.5 * t.Leechers)) AS Data
			FROM torrents AS t
				LEFT JOIN torrents_group AS g ON g.ID = t.GroupID
			WHERE t.Seeders > 0
				AND t.Time > ('$sqltime' - INTERVAL 1 DAY)
			ORDER BY (t.Seeders + t.Leechers) DESC
			LIMIT 10;");

    $Top10 = $DB->to_array();
}

$i = 1;
foreach ($Top10 as $Torrent) {
    $TorrentID = $Torrent['TorrentID'];
    $TorrentDetail = Torrents::get_torrent($TorrengID);
    $TitleString = Torrents::torrent_name($TorrentDetail, false);
    $TagString = str_replace('|', ' ', $Torrent['TagList']);
    if ($TorrentID) {
        $DB->query("
			INSERT INTO top10_history_torrents
				(HistoryID, Rank, TorrentID, TitleString, TagString)
			VALUES
				($HistoryID, $i, $TorrentID, '" . db_string($TitleString) . "', '" . db_string($TagString) . "')");
        $i++;
    }
}
