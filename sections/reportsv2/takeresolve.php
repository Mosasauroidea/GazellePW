<?
/*
 * This is the backend of the AJAXy reports resolve (When you press the shiny submit button).
 * This page shouldn't output anything except in error. If you do want output, it will be put
 * straight into the table where the report used to be. Currently output is only given when
 * a collision occurs or a POST attack is detected.
 */

if (!check_perms('admin_reports')) {
    error(403);
}
authorize();


//Don't escape: Log message, Admin message
$Escaped = db_array($_POST, array('log_message', 'admin_message', 'raw_name'));

//If we're here from the delete torrent page instead of the reports page.
if (!isset($Escaped['from_delete'])) {
    $Report = true;
} elseif (!is_number($Escaped['from_delete'])) {
    echo 'Hax occurred in from_delete';
} else {
    $Report = false;
}

$PMMessage = $_POST['uploader_pm'];

if (is_number($Escaped['reportid'])) {
    $ReportID = $Escaped['reportid'];
} else {
    echo 'Hax occurred in the reportid';
    die();
}

if ($Escaped['pm_type'] != 'Uploader') {
    $Escaped['uploader_pm'] = '';
}

$UploaderID = (int)$Escaped['uploaderid'];
if (!is_number($UploaderID)) {
    echo 'Hax occurring on the uploaderid';
    die();
}

$Warning = (int)$Escaped['warning'];
if (!is_number($Warning)) {
    echo 'Hax occurring on the warning';
    die();
}

$CategoryID = $Escaped['categoryid'];
if (!isset($CategoryID)) {
    echo 'Hax occurring on the categoryid';
    die();
}

$TorrentID = $Escaped['torrentid'];
$RawName = $Escaped['raw_name'];

if (isset($Escaped['delete']) && $Cache->get_value("torrent_$TorrentID" . '_lock')) {
    echo "You requested to delete the torrent $TorrentID, but this is currently not possible because the upload process is still running. Please try again later.";
    die();
}

if (($Escaped['resolve_type'] == 'manual' || $Escaped['resolve_type'] == 'dismiss') && $Report) {
    if ($Escaped['comment']) {
        $Comment = $Escaped['comment'];
    } else {
        if ($Escaped['resolve_type'] == 'manual') {
            $Comment = 'Report was resolved manually.';
        } elseif ($Escaped['resolve_type'] == 'dismiss') {
            $Comment = 'Report was dismissed as invalid.';
        }
    }
    if ($Escaped['resolve_type'] == 'dismiss') {
        $Comment = 'dismissed ' . $Comment;
    }
    $DB->query("
		UPDATE reportsv2
		SET
			Status = 'Resolved',
			LastChangeTime = '" . sqltime() . "',
			ModComment = '$Comment',
			ResolverID = '" . $LoggedUser['ID'] . "'
		WHERE ID = '$ReportID'
			AND Status != 'Resolved'");

    if ($DB->affected_rows() > 0) {
        $Cache->delete_value('num_torrent_reportsv2');
        $Cache->delete_value("reports_torrent_$TorrentID");
    } else {
        //Someone beat us to it. Inform the staffer.
?>
        <table class="layout" cellpadding="5">
            <tr>
                <td>
                    <a href="reportsv2.php?view=report&amp;id=<?= $ReportID ?>">Somebody has already resolved this report</a>
                    <input class="Button" type="button" value="Clear" onclick="ClearReport(<?= $ReportID ?>);" />
                </td>
            </tr>
        </table>
    <?
    }
    die();
}

if (!isset($Escaped['resolve_type'])) {
    echo 'No resolve type';
    die();
} elseif (array_key_exists($_POST['resolve_type'], $Types[$CategoryID])) {
    $ResolveType = $Types[$CategoryID][$_POST['resolve_type']];
} elseif (array_key_exists($_POST['resolve_type'], $Types['master'])) {
    $ResolveType = $Types['master'][$_POST['resolve_type']];
} else {
    //There was a type but it wasn't an option!
    echo 'HAX (Invalid Resolve Type)';
    die();
}

$DB->query("
	SELECT ID
	FROM torrents
	WHERE ID = $TorrentID");
$TorrentExists = ($DB->has_results());
if (!$TorrentExists) {
    $DB->query("
		UPDATE reportsv2
		SET Status = 'Resolved',
			LastChangeTime = '" . sqltime() . "',
			ResolverID = '" . $LoggedUser['ID'] . "',
			ModComment = 'Report already dealt with (torrent deleted).'
		WHERE ID = $ReportID");

    $Cache->decrement('num_torrent_reportsv2');
}

if ($Report) {
    //Resolve with a parallel check
    $DB->query("
		UPDATE reportsv2
		SET Status = 'Resolved',
			LastChangeTime = '" . sqltime() . "',
			ResolverID = '" . $LoggedUser['ID'] . "'
		WHERE ID = $ReportID
			AND Status != 'Resolved'");
}

//See if it we managed to resolve
if ($DB->affected_rows() > 0 || !$Report) {
    $DB->query("
			SELECT GroupID
			FROM torrents
			WHERE ID = $TorrentID");
    list($GroupID) = $DB->next_record();
    //We did, lets do all our shit
    if ($Report) {
        $Cache->decrement('num_torrent_reportsv2');
    }

    if (isset($Escaped['upload'])) {
        $Upload = true;
    } else {
        $Upload = false;
    }

    if ($_POST['resolve_type'] === 'folders_bad') {
        $DB->query("
			INSERT IGNORE INTO torrents_bad_folders
				(TorrentID, UserID, TimeAdded)
			VALUES
				($TorrentID, " . $LoggedUser['ID'] . ", '" . sqltime() . "')");
        $Cache->delete_value("torrents_details_$GroupID");
        $Cache->delete_value("torrent_group_$GroupID");
        $SendPM = true;
    } elseif ($_POST['resolve_type'] === 'filename') {
        $DB->query("
			INSERT IGNORE INTO torrents_bad_files
				(TorrentID, UserID, TimeAdded)
			VALUES
				($TorrentID, " . $LoggedUser['ID'] . ", '" . sqltime() . "')");

        $Cache->delete_value("torrents_details_$GroupID");
        $Cache->delete_value("torrent_group_$GroupID");
        $SendPM = true;
    } elseif ($_POST['resolve_type'] === 'upload_contest') {
        $DB->query("
			INSERT INTO upload_contest
			VALUES ($TorrentID, $UploaderID)");
    }
    if ($_POST['custom_trumpable']) {
        $DB->query("
			INSERT IGNORE INTO torrents_custom_trumpable
			VALUES
				($TorrentID, " . $LoggedUser['ID'] . ", '" . sqltime() . "', '" . db_string(trim($_POST['custom_trumpable'])) . "')");

        $Cache->delete_value("torrents_details_$GroupID");
        $Cache->delete_value("torrent_group_$GroupID");
    }
    //Log and delete
    if (isset($Escaped['delete']) && check_perms('users_mod')) {
        $DB->query("
			SELECT Username
			FROM users_main
			WHERE ID = $UploaderID");
        list($UpUsername) = $DB->next_record();
        $Log = "Torrent $TorrentID ($RawName) uploaded by $UpUsername was deleted by " . $LoggedUser['Username'];
        $Log .= ($Escaped['resolve_type'] == 'custom' ? '' : ' for the reason: ' . $ResolveType['title'] . ".");
        if (isset($Escaped['log_message']) && $Escaped['log_message'] != '') {
            $Log .= ' ( ' . $Escaped['log_message'] . ' )';
        }
        $DB->query("
			SELECT GroupID, hex(info_hash), FilePath, Size
			FROM torrents
			WHERE ID = $TorrentID");
        list($GroupID, $InfoHash, $FilePath, $Size) = $DB->next_record();
        Torrents::delete_torrent($TorrentID, 0, $ResolveType['reason']);

        //$InfoHash = unpack("H*", $InfoHash);
        $Log .= ' (' . strtoupper($InfoHash) . ')';
        Misc::write_log($Log);
        $Log = 'deleted ' . $FilePath . '(' . number_format($Size / (1024 * 1024 * 1024), 2) .  ' GB) for the reason: ' . $ResolveType['title'] . '. ( ' . $Escaped['log_message'] . ' )';
        Torrents::write_group_log($GroupID, $TorrentID, $LoggedUser['ID'], $Log, 0);
        $TrumpID = 0;
        if ($Escaped['resolve_type'] === 'trump') {
            if (preg_match('/torrentid=([0-9]+)/', $Escaped['log_message'], $Matches) === 1) {
                $TrumpID = $Matches[1];
            }
        }

        Torrents::send_pm($TorrentID, $UploaderID, $RawName, $Log, $TrumpID, (!$Escaped['uploader_pm'] && $Warning <= 0 && !isset($Escaped['delete']) && !$SendPM));
    } else {
        $Log = "No log message (torrent wasn't deleted).";
    }

    //Warnings / remove upload
    if ($Upload) {
        $Cache->begin_transaction("user_info_heavy_$UploaderID");
        $Cache->update_row(false, array('DisableUpload' => '1'));
        $Cache->commit_transaction(0);

        $DB->query("
			UPDATE users_info
			SET DisableUpload = '1'
			WHERE UserID = $UploaderID");
    }

    if ($Warning > 0) {
        $WarnLength = $Warning * (7 * 24 * 60 * 60);
        $Reason = "Uploader of torrent ($TorrentID) $RawName which was resolved with the preset: " . $ResolveType['title'] . '.';
        if ($Escaped['admin_message']) {
            $Reason .= ' (' . $Escaped['admin_message'] . ').';
        }
        if ($Upload) {
            $Reason .= ' (Upload privileges removed).';
        }

        Tools::warn_user($UploaderID, $WarnLength, $Reason);
    } else {
        //This is a bitch for people that don't warn but do other things, it makes me sad.
        $AdminComment = '';
        if ($Upload) {
            //They removed upload
            $AdminComment .= 'Upload privileges removed by ' . $LoggedUser['Username'];
            $AdminComment .= "\nReason: Uploader of torrent ($TorrentID) " . db_string($RawName) . ' which was resolved with the preset: ' . $ResolveType['title'] . ". (Report ID: $ReportID)";
        }
        if ($Escaped['admin_message']) {
            //They did nothing of note, but still want to mark it (Or upload and mark)
            $AdminComment .= ' (' . $Escaped['admin_message'] . ')';
        }
        if ($AdminComment) {
            $AdminComment = date('Y-m-d H:i:s') . " - Report Comment: $AdminComment on torrent $TorrentID by " . $LoggedUser['Username'] . "\n\n";

            $DB->query("
				UPDATE users_info
				SET AdminComment = CONCAT('" . db_string($AdminComment) . "', AdminComment)
				WHERE UserID = '" . db_string($UploaderID) . "'");
        }
    }

    //PM
    if ($Escaped['uploader_pm'] || $Warning > 0 || isset($Escaped['delete']) || $SendPM || $_POST['custom_trumpable']) {
        if (isset($Escaped['delete'])) {
            $PM = t('server.reportsv2.your_above_torrent_was_reported_and_has_been_deleted', ['Values' => [
                site_url() . "torrents.php?torrentid=$TorrentID"
            ]])  . "\n\n";
        } else {
            $PM = t('server.reportsv2.your_above_torrent_was_reported_but_not_been_deleted', ['Values' => [
                site_url() . "torrents.php?torrentid=$TorrentID"
            ]]) . "\n\n";
        }

        $Preset = $_POST['custom_trumpable'] ? $_POST['custom_trumpable'] . "\n\n" . t('server.reportsv2.your_torrent_is_now_displayed_on_better_php_and_trumpable', ['Values' => [
            site_url()
        ]]) : $ResolveType['resolve_options']['pm'];

        if ($Preset != '') {
            $PM .= t('server.reportsv2.pm_reason') . ": $Preset\n\n";
        }

        if ($Warning > 0) {
            $PM .= t('server.reportsv2.this_has_resulted_in_an_n_week_warning', ['Values' => [
                site_url() . "wiki.php?action=article&amp;id=114]$Warning"
            ]]) .  "\n\n";
        }

        if ($Upload) {
            $PM .= t('server.reportsv2.this_has_resulted_in_the_loss_of_your_upload_privilege', ['Values' => [
                ($Warning > 0 ? t('server.reportsv2.also_space') : '')
            ]]) . "\n\n";
        }

        if ($Log) {
            $PM .= t('server.reportsv2.log_message') . ": $Log\n\n";
        }

        if ($Escaped['uploader_pm']) {
            $PM .= t('server.inbox.message_from', ['Values' => [$LoggedUser['Username']]]) .  ": $PMMessage\n\n";
        }

        $PM .= t('server.reportsv2.report_was_handled_by_user', ['Values' => [$LoggedUser['Username']]]);

        Misc::send_pm($UploaderID, 0, "种子处理提示: " . $Escaped['raw_name'], $PM);
    }

    G::$Cache->delete_value("reports_torrent_$TorrentID");
    $GroupID = Torrents::torrentid_to_groupid($TorrentID);
    G::$Cache->delete_value("torrent_group_$GroupID");
    G::$Cache->delete_value("torrents_details_$GroupID");

    // Now we've done everything, update the DB with values
    if ($Report) {
        $DB->query("
			UPDATE reportsv2
			SET
				Type = '" . $Escaped['resolve_type'] . "',
				LogMessage = '" . db_string($Log) . "',
				ModComment = '" . $Escaped['comment'] . "'
			WHERE ID = $ReportID");
    }
} else {
    // Someone beat us to it. Inform the staffer.
    ?>
    <a href="reportsv2.php?view=report&amp;id=<?= $ReportID ?>">Somebody has already resolved this report</a>
    <input class="Button" type="button" value="Clear" onclick="ClearReport(<?= $ReportID ?>);" />
<?
}
