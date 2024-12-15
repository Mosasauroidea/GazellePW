<?php
class Reports {
    /**
     * This function formats a string containing a torrent's remaster information
     * to be used in Reports v2.
     *
     * @param string   $RemasterTitle - the title of the remaster information
     * @param string   $RemasterYear - the year of the remaster information
     */
    public static function format_reports_remaster_info($RemasterTitle, $RemasterYear) {
        if ($RemasterTitle) {
            $RemasterDisplayString = ' &lt;';
            if ($RemasterTitle != '' && $RemasterYear != '') {
                $RemasterDisplayString .= "$RemasterTitle - $RemasterYear";
            } elseif ($RemasterTitle != '' && $RemasterYear == '') {
                $RemasterDisplayString .= $RemasterTitle;
            } elseif ($RemasterTitle == '' && $RemasterYear != '') {
                $RemasterDisplayString .= $RemasterYear;
            }
            $RemasterDisplayString .= '&gt;';
        } else {
            $RemasterDisplayString = '';
        }
        return $RemasterDisplayString;
    }

    //Used to get reports info on a unison cache in both browsing pages and torrent pages.
    public static function get_reports($TorrentID) {
        $Reports = G::$Cache->get_value("reports_torrent_$TorrentID");
        if ($Reports === false) {
            $QueryID = G::$DB->get_query_id();
            G::$DB->query("
				SELECT
					ID,
					ReporterID,
					Type,
					UserComment,
					ReportedTime,
					UploaderReply,
					ReplyTime
				FROM reportsv2
				WHERE TorrentID = $TorrentID
					AND Status != 'Resolved'");
            $Reports = G::$DB->to_array(false, MYSQLI_ASSOC);
            G::$DB->set_query_id($QueryID);
            G::$Cache->cache_value("reports_torrent_$TorrentID", $Reports, 0);
        }
        if (!check_perms('admin_reports')) {
            $Return = array();
            foreach ($Reports as $Report) {
                if ($Report['Type'] !== 'edited') {
                    $Return[] = $Report;
                }
            }
            return $Return;
        }
        return $Reports;
    }

    public static function get_reports_messages(array $ReportIDs) {
        $Ret = [];
        if (count($ReportIDs) <= 0) {
            return $Ret;
        }
        G::$DB->prepared_query(
            "SELECT
					ReportID,
                    SentDate,
                    SenderID,
                    Body
				FROM reports_message
				WHERE ReportID in (" . implode(",", $ReportIDs) . ")"
        );
        $Reports = G::$DB->to_array(false, MYSQLI_ASSOC);
        foreach ($Reports as $Report) {
            $Ret[$Report['ReportID']][] = $Report;
        }
        return $Ret;
    }

    public static function add_reports_messages($ReportID, $UserID, $Body) {
        G::$DB->prepared_query(
            "INSERT INTO reports_message (ReportID,SentDate,SenderID,Body) VALUES (?, now(), ?, ?)",
            $ReportID,
            $UserID,
            $Body
        );
    }
}
