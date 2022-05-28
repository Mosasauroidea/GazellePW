<?php

//------------- Delete dead torrents ------------------------------------//

sleep(10);
// expire in 28 days
$DB->query("
		SELECT
			t.ID,
			t.GroupID,
			tg.Name,
			t.UserID,
			HEX(t.info_hash) AS InfoHash,
			t.dead_time
		FROM torrents as t
			JOIN torrents_group AS tg ON tg.ID = t.GroupID 
		WHERE
			(t.last_action < '" . time_minus(3600 * 24 * 28) . "' AND t.last_action != 0 AND t.dead_time < t.last_action)");
$Torrents = $DB->to_array(false, MYSQLI_NUM, false);
echo 'Found ' . count($Torrents) . " inactive torrents to be marked dead.\n";

$LogEntries = $DeleteNotes = array();

// Exceptions for inactivity deletion
$InactivityExceptionsMade = array(
    //UserID => expiry time of exception
);
$i = 0;
foreach ($Torrents as $Torrent) {
    list($ID, $GroupID, $Name, $UserID, $InfoHash, $Dead) = $Torrent;
    if (array_key_exists($UserID, $InactivityExceptionsMade) && (time() < $InactivityExceptionsMade[$UserID])) {
        // don't delete the torrent!
        continue;
    }

    $DB->query("UPDATE torrents SET dead_time = '" . sqltime() . "' WHERE ID = " . $ID);

    $ArtistName = Artists::display_artists(Artists::get_artist($GroupID), false, false, false, $UserID);
    if ($ArtistName) {
        $Name = "$ArtistName - $Name";
    }
    $Result = $DB->query("
			SELECT
				uid
			FROM xbt_snatched
			WHERE fid = '$ID'");
    $Snatched = $DB->to_array();
    if (!array_key_exists($UserID, $DeleteNotses)) {
        $DeleteNotes[$UserID] = array('Count' => 0, 'Msg' => '');
    }
    $DeleteNotes[$UserID]['Msg'] .= "\n[url=" . site_url() . "torrents.php?torrentid=$ID]" . $Name . "[/url]";
    $DeleteNotes[$UserID]['Count']++;

    $LogEntries[] = db_string("Torrent $ID ($Name) (" . strtoupper($InfoHash) . ") was marked dead for inactivity (unseeded)");

    ++$i;
}
echo "$i torrents scanned for inactivity.\n";

foreach ($DeleteNotes as $UserID => $MessageInfo) {
    $Singular = (($MessageInfo['Count'] == 1) ? true : false);
    Misc::send_pm_with_tpl($UserID, "torrent_inactivity", ["Singular" => $Singular, "Count" => $MessageInfo['Count'], "MessageInfo" => $MessageInfo['Msg']]);
}


if (count($LogEntries) > 0) {
    $Values = "('" . implode("', '$sqltime'), ('", $LogEntries) . "', '$sqltime')";
    $DB->query("
			INSERT INTO log (Message, Time)
			VALUES $Values");
    echo "\nMark $i dead torrents for inactivity\n";
}
