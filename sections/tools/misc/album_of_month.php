<?
if (!check_perms('users_mod')) {
    error(403);
}

//Make sure the form was sent
if (isset($_POST['GroupID'])) {
    authorize();

    //Album of the month forum ID
    $ForumID = 43;

    $GroupID = trim($_POST['GroupID']);

    if (!is_number($GroupID)) {
        error('You did not enter a valid GroupID');
    }



    $FreeLeechType = (int) $_POST['freeleechtype'];
    $FreeLeechReason = (int) $_POST['freeleechreason'];
    if (!in_array($FreeLeechType, array(0, 1, 2)) || !in_array($FreeLeechReason, array(0, 1, 2, 3))) {
        error('Invalid freeleech type or freeleech reason');
    } else {
        $DB->prepared_query('
			SELECT
				tg.ID,
				tg.ArtistID,
				tg.Name,
				tg.WikiImage,
				ag.Name AS Artist
			FROM torrents_group AS tg
				LEFT JOIN artists_group AS ag ON tg.ArtistID = ag.ArtistID
			WHERE tg.id = ?', $GroupID);

        $Album = $DB->next_record();

        //Make sure album exists
        if (is_number($Album['ID'])) {
            //Remove old albums with type = 0 (so we remove the previous AotM)
            $DB->prepared_query('DELETE FROM featured_albums WHERE Type = 0');
            $Cache->delete_value('album_of_the_month');

            //Freeleech torrents
            if (isset($_POST['FLTorrents'])) {
                $DB->prepared_query('
					SELECT ID
					FROM torrents
					WHERE GroupID = ?', $Album['ID']);
                $TorrentIDs = $DB->collect('ID');
                if (isset($_POST['NLOver']) && $FreeLeechType == 1) {
                    // Only use this checkbox if freeleech is selected
                    $Size = (int) $_POST['size'];
                    $Units = db_string($_POST['scale']);

                    if (empty($Size) || !in_array($Units, array('k', 'm', 'g'))) {
                        $Err = 'Invalid size or units';
                    } else {
                        $Bytes = Format::get_bytes($Size . $Units);

                        $DB->query("
							SELECT ID
							FROM torrents
							WHERE ID IN (" . implode(', ', $TorrentIDs) . ")
						  	AND Size > '$Bytes'");
                        $LargeTorrents = $DB->collect('ID');
                        $TorrentIDs = array_diff($TorrentIDs, $LargeTorrents);
                    }
                }
                if (isset($_POST['limit-time'])) {
                    $FreeDate = db_string($_POST['free-date']);
                    $FreeTime = db_string(substr($_POST['free-time'], 0, 2));
                }
                if (sizeof($TorrentIDs) > 0) {
                    Torrents::freeleech_torrents($TorrentIDs, $FreeLeechType, $FreeLeechReason);
                    if (isset($_POST['limit-time'])) {
                        foreach ($TorrentIDs as $TorrentID) {
                            $DB->query("INSERT INTO `freetorrents_timed`(`TorrentID`, `EndTime`) VALUES ($TorrentID,'$FreeDate $FreeTime:00') ON DUPLICATE KEY UPDATE EndTime=VALUES(EndTime)");
                        }
                    }
                }

                if (isset($LargeTorrents) && sizeof($LargeTorrents) > 0) {
                    Torrents::freeleech_torrents($LargeTorrents, 2, $FreeLeechReason);
                    if (isset($_POST['limit-time'])) {
                        foreach ($LargeTorrents as $TorrentID) {
                            $DB->query("INSERT INTO `freetorrents_timed`(`TorrentID`, `EndTime`) VALUES ($TorrentID,'$FreeDate $FreeTime:00') ON DUPLICATE KEY UPDATE EndTime=VALUES(EndTime)");
                        }
                    }
                }
            }

            //Get post title (album title)
            if ($Album['ArtistID'] != '0') {
                $Title = $Album['Artist'] . ' - ' . $Album['Name'];
            } else {
                $Title = $Album['Name'];
            }

            //Get post body
            if (isset($_POST['Body']) && $_POST['Body'] != '') {
                $Body = $_POST['Body'];
            } else {
                $Body = '[size=4]' . $Title . '[/size]' . "\n\n";
                if (!empty($Album['WikiImage']))
                    $Body .= '[img]' . $Album['WikiImage'] . '[/img]';
            }

            //Create forum post
            $ThreadID = Misc::create_thread($ForumID, $LoggedUser[ID], $Title, $Body);

            //Add album of the month
            $DB->prepared_query('
				INSERT INTO featured_albums
					(GroupID,ThreadID,Started,Type)
				VALUES
					(?, ?, ?, ?)', db_string($GroupID), $ThreadID, sqltime(), 0);


            //Redirect to home page
            header("Location: /");
            //What to do if we don't have a GroupID
        } else {
            //Uh oh, something went wrong
            error(t('server.tools.plz_supply_a_valid_album_id'));
        }
    }
    //Form wasn't sent -- Show form
} else {

    //Show our beautiful header
    View::show_header(t('server.tools.album_of_the_month'));

?>
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.album_of_the_month') ?></h2>
    </div>

    <div class="thin BoxBody">
        <form class="create_form" name="album" method="post" action="">
            <div class="pad">
                <input type="hidden" name="action" value="monthalbum" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <h3><?= t('server.tools.album_id') ?></h3>
                <input class="Input" type="text" name="GroupID" id="groupid" /> <br />
                <h3><?= t('server.tools.body') ?></h3>
                <div>(<?= t('server.tools.leave_blank_to_auto_generate') ?>)</div>
                <textarea class="Input" name="Body" cols="95" rows="15"></textarea><br /><br />
                <input type="checkbox" name="FLTorrents" checked />&nbsp;<?= t('server.tools.mark_torrents_as') ?>&nbsp;
                <select class="Input" name="freeleechtype">
                    <option class="Select-option" value="1" selected><?= t('server.tools.fl') ?></option>
                    <option class="Select-option" value="2"><?= t('server.tools.nl') ?></option>
                    <option class="Select-option" value="0"><?= t('server.tools.normal') ?></option>
                </select>
                <script>
                    $(document).ready(() => {
                        $("#limit-time").click(() => {
                            if ($("#limit-time")[0].checked) {
                                $("#input-free-date,#input-free-time").show()
                                const d = new Date()
                                $("#input-free-date")[0].value = d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).substr(-2) + "-" + ("0" + d.getDate()).substr(-2)
                                $("#input-free-time")[0].value = ("0" + d.getHours()).substr(-2) + ":" + ("0" + d.getMinutes()).substr(-2)
                            } else {
                                $("#input-free-date,#input-free-time").hide()
                            }
                        })
                    })
                </script>
                <input type="checkbox" id="limit-time" name="limit-time" <?= $Torrent['FreeEndTime'] ? " checked=\"checked\"" : "" ?> />&nbsp;<label for="limit-time" style="display: inline;"><?= t('server.tools.timer') ?></label>&nbsp;
                <input id="input-free-date" name="free-date" type="date" style="display:none;" /><input id="input-free-time" name="free-time" type="time" style="display:none;" />
                &nbsp;<?= t('server.tools.for_reason') ?>&nbsp;<select class="Input" name="freeleechreason">
                    <? $FL = array('N/A', 'Staff Pick', 'Perma-FL');
                    foreach ($FL as $Key => $FLType) { ?>
                        <option class="Select-option" value="<?= $Key ?>" <?= $FLType == 'Staff Pick' ? 'selected' : '' ?>><?= $FLType ?></option>
                    <?      } ?>
                </select><br /><br />
                <input type="checkbox" name="NLOver" checked />&nbsp;<?= t('server.tools.nl_torrents_over') ?>
                <input class="Input" type="text" name="size" value="<?= isset($_POST['size']) ? $_POST['size'] : '1' ?>" size=1 />
                <select class="Input" name="scale">
                    <option class="Select-option" value="k">KB</option>
                    <option class="Select-option" value="m">MB</option>
                    <option class="Select-option" value="g" selected>GB</option>
                </select><?= t('server.tools.nl_torrents_over_after') ?><br /><br />

                <div class="center">
                    <input class="Button" type="submit" name="submit" value="<?= t('server.global.submit') ?>" />
                </div>
            </div>
        </form>
    </div>
<?

    View::show_footer();
}

?>