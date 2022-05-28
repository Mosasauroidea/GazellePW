<?

/**************************************************************************/
/*-- Invite tree class -----------------------------------------------------



***************************************************************************/

class INVITE_TREE {
    var $UserID = 0;
    var $Visible = true;

    // Set things up
    function __construct($UserID, $Options = array()) {
        $this->UserID = $UserID;
        if (isset($Options['visible']) && $Options['visible'] === false) {
            $this->Visible = false;
        }
    }

    function make_tree() {
        $QueryID = G::$DB->get_query_id();

        $UserID = $this->UserID;
?>
        <div class="invitetree pad">
            <?
            G::$DB->query("
			SELECT TreePosition, TreeID, TreeLevel
			FROM invite_tree
			WHERE UserID = $UserID");
            if (!G::$DB->has_results()) {
                return;
            }
            list($TreePosition, $TreeID, $TreeLevel) = G::$DB->next_record(MYSQLI_NUM, false);

            G::$DB->query("
			SELECT TreePosition
			FROM invite_tree
			WHERE TreeID = $TreeID
				AND TreeLevel = $TreeLevel
				AND TreePosition > $TreePosition
			ORDER BY TreePosition ASC
			LIMIT 1");
            if (G::$DB->has_results()) {
                list($MaxPosition) = G::$DB->next_record(MYSQLI_NUM, false);
            } else {
                $MaxPosition = false;
            }
            $TreeQuery = G::$DB->query("
			SELECT
				it.UserID,
				Enabled,
				PermissionID,
				Donor,
				Uploaded,
				Downloaded,
				Paranoia,
				TreePosition,
				TreeLevel
			FROM invite_tree AS it
				JOIN users_main AS um ON um.ID = it.UserID
				JOIN users_info AS ui ON ui.UserID = it.UserID
			WHERE TreeID = $TreeID
				AND TreePosition > $TreePosition" .
                ($MaxPosition ? " AND TreePosition < $MaxPosition" : '') . "
				AND TreeLevel > $TreeLevel
			ORDER BY TreePosition");

            $PreviousTreeLevel = $TreeLevel;

            // Stats for the summary
            $MaxTreeLevel = $TreeLevel; // The deepest level (this changes)
            $OriginalTreeLevel = $TreeLevel; // The level of the user we're viewing
            $BaseTreeLevel = $TreeLevel + 1; // The level of users invited by our user
            $Count = 0;
            $Branches = 0;
            $DisabledCount = 0;
            $DonorCount = 0;
            $ParanoidCount = 0;
            $TotalUpload = 0;
            $TotalDownload = 0;
            $TopLevelUpload = 0;
            $TopLevelDownload = 0;

            $ClassSummary = array();
            global $Classes;
            foreach ($Classes as $ClassID => $Val) {
                $ClassSummary[$ClassID] = 0;
            }

            // We store this in an output buffer, so we can show the summary at the top without having to loop through twice
            ob_start();
            while (list($ID, $Enabled, $Class, $Donor, $Uploaded, $Downloaded, $Paranoia, $TreePosition, $TreeLevel) = G::$DB->next_record(MYSQLI_NUM, false)) {

                // Do stats
                $Count++;

                if ($TreeLevel > $MaxTreeLevel) {
                    $MaxTreeLevel = $TreeLevel;
                }

                if ($TreeLevel == $BaseTreeLevel) {
                    $Branches++;
                    $TopLevelUpload += $Uploaded;
                    $TopLevelDownload += $Downloaded;
                }

                $ClassSummary[$Class]++;
                if ($Enabled == 2) {
                    $DisabledCount++;
                }
                if ($Donor) {
                    $DonorCount++;
                }

                // Manage tree depth
                if ($TreeLevel > $PreviousTreeLevel) {
                    for ($i = 0; $i < $TreeLevel - $PreviousTreeLevel; $i++) {
                        echo "\n\n<ul class=\"invitetree\">\n\t<li>\n";
                    }
                } elseif ($TreeLevel < $PreviousTreeLevel) {
                    for ($i = 0; $i < $PreviousTreeLevel - $TreeLevel; $i++) {
                        echo "\t</li>\n</ul>\n";
                    }
                    echo "\t</li>\n\t<li>\n";
                } else {
                    echo "\t</li>\n\t<li>\n";
                }
                $UserClass = $Classes[$Class]['Level'];
            ?>
                <strong><?= Users::format_username($ID, true, true, ($Enabled != 2 ? false : true), true) ?></strong>
                <?
                if (check_paranoia(array('uploaded', 'downloaded'), $Paranoia, $UserClass)) {
                    $TotalUpload += $Uploaded;
                    $TotalDownload += $Downloaded;
                ?>
                    &nbsp;Uploaded: <strong><?= Format::get_size($Uploaded) ?></strong>
                    &nbsp;Downloaded: <strong><?= Format::get_size($Downloaded) ?></strong>
                    &nbsp;Ratio: <strong><?= Format::get_ratio_html($Uploaded, $Downloaded) ?></strong>
                <?
                } else {
                    $ParanoidCount++;
                ?>
                    &nbsp;Hidden
                <?
                }
                ?>

            <?
                $PreviousTreeLevel = $TreeLevel;
                G::$DB->set_query_id($TreeQuery);
            }

            $Tree = ob_get_clean();
            for ($i = 0; $i < $PreviousTreeLevel - $OriginalTreeLevel; $i++) {
                $Tree .= "\t</li>\n</ul>\n";
            }

            if ($Count) {

            ?>
                <p style="font-weight: bold;">
                    <?= Lang::get('user', 'this_tree_has_n_entries_n_branches_and_a_depth_of_n_1') ?><?= number_format($Count) ?><?= Lang::get('user', 'this_tree_has_n_entries_n_branches_and_a_depth_of_n_2') ?><?= number_format($Branches) ?><?= Lang::get('user', 'this_tree_has_n_entries_n_branches_and_a_depth_of_n_3') ?><?= number_format($MaxTreeLevel - $OriginalTreeLevel) ?><?= Lang::get('user', 'this_tree_has_n_entries_n_branches_and_a_depth_of_n_4') ?>
                <?
                $ClassStrings = array();
                foreach ($ClassSummary as $ClassID => $ClassCount) {
                    if ($ClassCount == 0) {
                        continue;
                    }
                    $LastClass = Users::make_class_string($ClassID);
                    if ($ClassCount > 1) {
                        if ($LastClass == 'Torrent Celebrity') {
                            $LastClass = 'Torrent Celebrities';
                        } else {
                            $LastClass .= 's';
                        }
                    }
                    $LastClass = "$ClassCount $LastClass (" . number_format(($ClassCount / $Count) * 100) . '%)';

                    $ClassStrings[] = $LastClass;
                }
                if (count($ClassStrings) > 1) {
                    array_pop($ClassStrings);
                    echo implode(', ', $ClassStrings);
                    echo Lang::get('user', 'space_and_space') . $LastClass;
                } else {
                    echo $LastClass;
                }
                echo '. ';
                echo $DisabledCount;
                echo ($DisabledCount == 1) ? Lang::get('user', 'n_users_are_disabled_1') : Lang::get('user', 'n_users_are_disabled_2');
                echo Lang::get('user', 'n_users_are_disabled_3');
                if ($DisabledCount == 0) {
                    echo '0%)';
                } else {
                    echo number_format(($DisabledCount / $Count) * 100) . '%)';
                }
                echo Lang::get('user', 'comma_space_and_space');
                echo $DonorCount;
                echo ($DonorCount == 1) ? Lang::get('user', 'n_users_have_donated_1') : Lang::get('user', 'n_users_have_donated_2');
                echo Lang::get('user', 'n_users_have_donated_3');
                if ($DonorCount == 0) {
                    echo '0%)';
                } else {
                    echo number_format(($DonorCount / $Count) * 100) . '%)';
                }
                echo Lang::get('user', 'period_space_p');

                echo '<p style="font-weight: bold;">';
                echo Lang::get('user', 'the_total_amount_uploaded_by_the_entire_tree_was') . Format::get_size($TotalUpload);
                echo Lang::get('user', 'the_total_amount_downloaded_was') . Format::get_size($TotalDownload);
                echo Lang::get('user', 'and_the_total_ratio_is') . Format::get_ratio_html($TotalUpload, $TotalDownload) . Lang::get('user', 'period_space');
                echo '</p>';

                echo '<p style="font-weight: bold;">';
                echo Lang::get('user', 'the_total_amount_uplaoded_by_direct_invitees_was_') . Format::get_size($TopLevelUpload);
                echo Lang::get('user', 'the_total_amount_downloaded_was') . Format::get_size($TopLevelDownload);
                echo Lang::get('user', 'and_the_total_ratio_is') . Format::get_ratio_html($TopLevelUpload, $TopLevelDownload) . Lang::get('user', 'period_space');

                echo Lang::get('user', 'these_numbers_include_the_stats_of_paranoid_users_and') . "\n\t\t</p>\n";

                if ($ParanoidCount) {
                    echo '<p style="font-weight: bold;">';
                    echo $ParanoidCount;
                    echo ($ParanoidCount == 1) ? Lang::get('user', 'space_user_space_bracket') : Lang::get('user', 'space_users_space_bracket');
                    echo number_format(($ParanoidCount / $Count) * 100);
                    echo '%) ';
                    echo ($ParanoidCount == 1) ? Lang::get('user', 'space_is') : Lang::get('user', 'space_are');
                    echo Lang::get('user', 'too_paranoid_to_have_their_stats_shown_here_and');
                    echo ($ParanoidCount == 1) ? Lang::get('user', 'space_was') : Lang::get('user', 'space_were');
                    echo Lang::get('user', 'not_factored_into_the_stats_for_the_total_tree');
                    echo '</p>';
                }
            }
                ?>
                <br />
                <?= $Tree ?>
        </div>
<?
        G::$DB->set_query_id($QueryID);
    }
}
?>