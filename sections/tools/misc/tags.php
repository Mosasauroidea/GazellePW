<?
View::show_header(t('server.tools.batch_tag_editor'), 'validate');
if (!check_perms('users_mod')) {
    error(403);
}

// validation functions
$Val->SetFields('tag', true, 'string', t('server.tools.enter_a_single_tag_to_search_for'), array('maxlength' => '200', 'minlength' => '2'));
$Val->SetFields('replace', true, 'string', t('server.tools.enter_a_single_replacement_tag'), array('maxlength' => '200', 'minlength' => '2'));

echo $Val->GenerateJS('tagform');

// some constants to make programmers' lives easier
define('MODE_RENAME', 0);
define('MODE_MERGE', 1);

?>
<div class="LayoutBody">

    <br />
    <?
    if (isset($_GET['tag']) || isset($_GET['replace'])) {

        // validate input
        $Err = $Val->ValidateForm($_GET);
        if ($Err) {
            echo '
			<div class="BoxBody center">
				<strong>' . t('server.tools.error') . ':</strong> ' . $Err . '
			</div>';
        } else {
            $Tag = Misc::sanitize_tag($_GET['tag']);
            $Replacement = Misc::sanitize_tag($_GET['replace']);

            // trying to merge tag with itself would create big problems
            if ($Tag == $Replacement) {
                echo "
				<div class=\"BoxBody center\">
					<strong>" . t('server.tools.error') . ": </strong>" . t('server.tools.cannot_merge_tag_with_itself', ['Values' => [$Tag]]) . "
				</div>
			</div>";
                View::show_footer();
                exit;
            }

            // 1) make sure tag exists
            $DB->query("
			SELECT ID
			FROM tags
			WHERE Name = '$Tag'
			LIMIT 1;");
            if (!$DB->has_results()) {
                echo "
				<div class=\"BoxBody center\">
					<strong>" . t('server.tools.error') . ": </strong>" . t('server.tools.no_such_tag_found') . ": $Tag
				</div>
			</div>";
                View::show_footer();
                exit;
            }
            list($TagID) = $DB->next_record();

            // 2) check if replacement exists
            $DB->query("
			SELECT ID
			FROM tags
			WHERE Name = '$Replacement'
			LIMIT 1;");
            if (!$DB->has_results()) {
                $Mode = MODE_RENAME;
            } else {
                $Mode = MODE_MERGE;
            }
            list($ReplacementID) = $DB->next_record();

            if ($_GET['list']) {
                $AffectedTorrents = array();
                // 3) get a list of affected torrents
                $DB->query("
				SELECT
					tg.ID,
					ag.ArtistID,
					ag.Name,
					tg.Name
				FROM torrents_group AS tg
					LEFT JOIN artists_group AS ag ON ag.ArtistID = tg.ArtistID
					JOIN torrents_tags AS t ON t.GroupID = tg.ID
				WHERE t.TagID = $TagID;");
                while (list($TorrentID, $ArtistID, $ArtistName, $TorrentName) = $DB->next_record()) {
                    $Row = ($ArtistName ? "<a href=\"artist.php?id=$ArtistID\">$ArtistName</a> - " : '');
                    $Row .= "<a href=\"torrents.php?id=$TorrentID\">" . display_str($TorrentName) . '</a>';
                    $AffectedTorrents[] = $Row;
                }

                // 4) get a list of affected requests
                $DB->query("
				SELECT
					ra.RequestID,
					ag.ArtistID,
					ag.Name,
					r.Title
				FROM requests AS r
					LEFT JOIN requests_artists AS ra ON r.ID = ra.RequestID
					LEFT JOIN artists_group AS ag ON ag.ArtistID = ra.ArtistID
					JOIN requests_tags AS t ON t.RequestID = r.ID
				WHERE t.TagID = $TagID;");
                while (list($RequestID, $ArtistID, $ArtistName, $RequestName) = $DB->next_record()) {
                    $Row = ($ArtistName ? "<a href=\"artist.php?id=$ArtistID\">$ArtistName</a> - " : '');
                    $Row .= "<a href=\"requests.php?action=viewrequest&amp;id=$RequestID\">" . display_str($RequestName) . '</a>';
                    $AffectedRequests[] = $Row;
                }
            }

            $TotalAffected = 0;
            if ($Mode == MODE_RENAME) {
                // EASY! just rename the tag
                // 5) rename the tag
                $DB->query("
				UPDATE tags
				SET Name = '$Replacement'
				WHERE ID = $TagID
				LIMIT 1;");
                $TotalAffected = $DB->affected_rows();
            } else {
                // HARD! merge two tags together and update usage
                // 5) remove dupe tags from torrents
                //  (torrents that have both "old tag" and "replacement tag" set)
                $DB->query("
				SELECT GroupID
				FROM torrents_tags
				WHERE TagID = $ReplacementID;");
                if ($DB->has_results()) {
                    $Query = "
					DELETE FROM torrents_tags
					WHERE TagID = $TagID
						AND GroupID IN (";
                    while (list($GroupID) = $DB->next_record()) {
                        $Query .= "$GroupID,";
                        Torrents::update_hash($GroupID);
                    }
                    $Query = substr($Query, 0, -1) . ');';
                    $DB->query($Query);
                    $TotalAffected = $DB->affected_rows();
                }

                // 6) replace old tag in torrents
                $DB->query("
				UPDATE torrents_tags
				SET TagID = $ReplacementID
				WHERE TagID = $TagID;");
                $UsageChange = $DB->affected_rows();

                // 7) remove dupe tags from artists
                $DB->query("
				SELECT ArtistID
				FROM artists_tags
				WHERE TagID = $ReplacementID;");
                if ($DB->has_results()) {
                    $Query = "
					DELETE FROM artists_tags
					WHERE TagID = $TagID
						AND ArtistID IN (";
                    while (list($ArtistID) = $DB->next_record()) {
                        $Query .= "$ArtistID,";
                    }
                    $Query = substr($Query, 0, -1) . ');';
                    $DB->query($Query);
                    $TotalAffected += $DB->affected_rows();
                }

                // 8) replace old tag in artists
                $DB->query("
				UPDATE artists_tags
				SET TagID = $ReplacementID
				WHERE TagID = $TagID;");
                $UsageChange += $DB->affected_rows();

                // 9) remove dupe tags from requests
                $DB->query("
				SELECT RequestID
				FROM requests_tags
				WHERE TagID = $ReplacementID;");
                if ($DB->has_results()) {
                    $Query = "
					DELETE FROM requests_tags
					WHERE TagID = $TagID
						AND RequestID IN (";
                    while (list($RequestID) = $DB->next_record()) {
                        $Query .= "$RequestID,";
                    }
                    $Query = substr($Query, 0, -1) . ');';
                    $DB->query($Query);
                    $TotalAffected += $DB->affected_rows();
                }

                // 10) replace old tag in requests
                $DB->query("
				UPDATE requests_tags
				SET TagID = $ReplacementID
				WHERE TagID = $TagID;");
                $UsageChange += $DB->affected_rows();
                $TotalAffected += $UsageChange;

                // 11) finally, remove old tag completely
                $DB->query("
				DELETE FROM tags
				WHERE ID = $TagID
				LIMIT 1");

                // 12) update usage count for replacement tag
                $DB->query("
				UPDATE tags
				SET Uses = Uses + $UsageChange
				WHERE ID = $ReplacementID
				LIMIT 1");
            } // if ($Mode == MODE_MERGE)

            echo "\n" . '<div class="BoxBody center"><strong>' . t('server.tools.success_exclamation_mark') . '</strong>' . t('server.tools.affected_entries') . ': ' . number_format($TotalAffected) . '</div>';

            if ($_GET['list']) {
    ?>
                <br>
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.affected_torrent_groups') ?></td>
                    </tr>
                    <?
                    if (count($AffectedTorrents)) {
                        foreach ($AffectedTorrents as $Row) {
                            echo "\n\t\t<tr class='Table-row'><td class='Table-cell'>$Row</td></tr>";
                        }
                    }
                    ?>
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.affected_requests') ?></td>
                    </tr>
                    <?
                    if (count($AffectedRequests)) {
                        foreach ($AffectedRequests as $Row) {
                            echo "\n\t\t<tr class='Table-row'><td class='Table-cell'>$Row</td></tr>";
                        }
                    }
                    ?>
                </table>
    <?
            }
        } // if (!$Err)

    } // if ( isset($_GET['tag']) || isset($_GET['replace']) )

    echo '</div>';

    View::show_footer();
    ?>