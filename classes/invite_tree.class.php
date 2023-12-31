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
        <div class="invitetree BoxBody">
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
				AND TreePosition >= $TreePosition" .
                ($MaxPosition ? " AND TreePosition < $MaxPosition" : '') . "
				AND TreeLevel >= $TreeLevel
			ORDER BY TreePosition");

            $PreviousTreeLevel = $TreeLevel - 1;

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
                        if ($TreeLevel == 0) {
                            echo "\n\n<ul class=\"MenuList invitetree\">\n\t<li>\n";
                        } else {
                            echo "\n\n<ul class=\"MenuList SubMenu invitetree\">\n\t<li>\n";
                        }
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
                    <?= t('server.user.this_tree_has_n_entries_n_branches_and_a_depth_of_n', ['Values' => [
                        number_format($Count),
                        number_format($Branches),
                        number_format($MaxTreeLevel - $OriginalTreeLevel)
                    ]]) ?>
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
                    echo t('server.user.space_and_space') . $LastClass;
                } else {
                    echo $LastClass;
                }
                echo '. ';
                echo $DisabledCount;
                echo t('server.user.n_users_are_disabled', ['Values' => [
                    t('server.user.n_users_are_disabled_count', ['Count' => $DisabledCount])
                ]]);
                if ($DisabledCount == 0) {
                    echo '0%)';
                } else {
                    echo number_format(($DisabledCount / $Count) * 100) . '%)';
                }
                echo t('server.user.comma_space_and_space');
                echo $DonorCount;
                echo t('server.user.n_users_have_donated', ['Values' => [
                    t('server.user.n_users_have_donated_count', ['Count' => $DonorCount])
                ]]);
                if ($DonorCount == 0) {
                    echo '0%)';
                } else {
                    echo number_format(($DonorCount / $Count) * 100) . '%)';
                }
                echo t('server.user.period_space_p');

                echo '<p style="font-weight: bold;">';
                echo t('server.user.the_total_amount_uploaded_by_the_entire_tree_was') . Format::get_size($TotalUpload);
                echo t('server.user.the_total_amount_downloaded_was') . Format::get_size($TotalDownload);
                echo t('server.user.and_the_total_ratio_is') . Format::get_ratio_html($TotalUpload, $TotalDownload) . t('server.user.period_space');
                echo '</p>';

                echo '<p style="font-weight: bold;">';
                echo t('server.user.the_total_amount_uplaoded_by_direct_invitees_was_') . Format::get_size($TopLevelUpload);
                echo t('server.user.the_total_amount_downloaded_was') . Format::get_size($TopLevelDownload);
                echo t('server.user.and_the_total_ratio_is') . Format::get_ratio_html($TopLevelUpload, $TopLevelDownload) . t('server.user.period_space');

                echo t('server.user.these_numbers_include_the_stats_of_paranoid_users_and') . "\n\t\t</p>\n";

                if ($ParanoidCount) {
                    echo '<p style="font-weight: bold;">';
                    echo $ParanoidCount;
                    echo ($ParanoidCount == 1) ? t('server.user.space_user_space_bracket') : t('server.user.space_users_space_bracket');
                    echo number_format(($ParanoidCount / $Count) * 100);
                    echo '%) ';
                    echo ($ParanoidCount == 1) ? t('server.user.space_is') : t('server.user.space_are');
                    echo t('server.user.too_paranoid_to_have_their_stats_shown_here_and');
                    echo ($ParanoidCount == 1) ? t('server.user.space_was') : t('server.user.space_were');
                    echo t('server.user.not_factored_into_the_stats_for_the_total_tree');
                    echo '</p>';
                }
            }
                ?>
        </div>
        <div class="BoxBody">
            <?= $Tree ?>
        </div>
<?
        G::$DB->set_query_id($QueryID);
    }
}
?>