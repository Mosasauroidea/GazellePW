<?
if (!check_perms('users_mod')) {
    error(403);
}

//Make sure the form was sent
if (isset($_POST['GroupID'])) {
    authorize();

    $ForumID = $CONFIG['FEATURED_MOVIE_FORUM'];

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
                tg.WikiBody,
                tg.MainWikiBody,
				tg.SubName,
                tg.Year
			FROM torrents_group AS tg
			WHERE tg.id = ?', $GroupID);

        $Movie = $DB->next_record();

        //Make sure album exists
        if (is_number($Movie['ID'])) {
            //Remove old albums with type = 0 (so we remove the previous AotM)
            $DB->prepared_query('DELETE FROM featured_albums WHERE Type = 0');
            $Cache->delete_value('featured_movie');

            //Freeleech torrents
            if (isset($_POST['FLTorrents'])) {
                $DB->prepared_query('
					SELECT ID
					FROM torrents
					WHERE GroupID = ?', $Movie['ID']);
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
            //Add album of the month
            $DB->prepared_query('
				INSERT INTO featured_albums
					(GroupID,ThreadID,Started,Type)
				VALUES
					(?, ?, ?, ?)', db_string($GroupID), 0, sqltime(), 0);
            $FeatureID = G::$DB->inserted_id();

            $Title = "#$FeatureID " . Torrents::group_name($Movie, false);

            $Body = '[url=torrents.php?id=' . $GroupID . ']' . Torrents::group_name($Movie, false) .  "[/url]\n\n";
            //Get post body
            if (isset($_POST['Body']) && $_POST['Body'] != '') {
                $Body .= "[quote]" . $_POST['Body'] . "[/quote]\n\n";
            }
            if (!empty($Movie['WikiImage']))
                $Body .= '[img]' . $Movie['WikiImage'] . "[/img]\n\n";
            $Body .= Lang::choose_content($Movie['MainWikiBody'], $Movie['WikiBody']) .  "\n\n";

            //Create forum post
            $ThreadID = Misc::create_thread($ForumID, $LoggedUser['ID'], $Title, $Body);

            $DB->prepared_query("UPDATE featured_albums SET ThreadID = ?", $ThreadID);


            $Cache->delete_value("forums_$ForumID");


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
    View::show_header(t('server.tools.featured_movie'));

?>
    <div class="LayoutBody">
        <div class="BodyHeader">
            <h2 class="BodyHeader-nav"><?= t('server.tools.featured_movie') ?></h2>
        </div>

        <form class="create_form" name="album" method="post" action="">
            <input type="hidden" name="action" value="featuremovie" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <div class="Form-rowList" variant="header">
                <div class="Form-rowHeader">
                    <?= t('server.tools.featured_movie') ?>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.album_id') ?></div>
                    <div class="Form-inputs">
                        <input class="Input" type="text" name="GroupID" id="groupid" />
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label"><?= t('server.tools.pick_reason') ?></div>
                    <div class="Form-items">
                        <? new TEXTAREA_PREVIEW("Body") ?>
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label">
                    </div>
                    <div class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" name="FLTorrents" checked />
                            <label class="Checkbox-label"> <?= t('server.tools.mark_torrents_as') ?></label>
                        </div>
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
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" id="limit-time" name="limit-time" <?= $Torrent['FreeEndTime'] ? " checked=\"checked\"" : "" ?> />
                            <label class="Checkbox-label" for="limit-time" style="display: inline;"><?= t('server.tools.timer') ?></label>&nbsp;
                        </div>
                        <input id="input-free-date" name="free-date" type="date" style="display:none;" />
                        <input id="Input input-free-time" name="free-time" type="time" style="display:none;" />
                        <?= t('server.tools.for_reason') ?>
                        <select class="Input" name="freeleechreason">
                            <? $FL = array('N/A', 'Staff Pick', 'Perma-FL');
                            foreach ($FL as $Key => $FLType) { ?>
                                <option class="Select-option" value="<?= $Key ?>" <?= $FLType == 'Staff Pick' ? 'selected' : '' ?>><?= $FLType ?></option>
                            <?      } ?>
                        </select>
                    </div>
                </div>
                <div class="Form-row">
                    <div class="Form-label">
                    </div>
                    <div class="Form-inputs">
                        <div class="Checkbox">
                            <input class="Input" type="checkbox" name="NLOver" />
                            <label class="Checkbox-label"><?= t('server.tools.nl_torrents_over') ?></label>
                        </div>
                        <input class="Input is-small" type="number" name="size" value="<?= isset($_POST['size']) ? $_POST['size'] : '1' ?>" size=1 />
                        <select class="Input" name="scale">
                            <option class="Select-option" value="g" selected>GB</option>
                        </select><?= t('server.tools.nl_torrents_over_after') ?>
                    </div>
                </div>
                <div class="Form-row">
                    <input class="Button" type="submit" name="submit" value="<?= t('server.common.submit') ?>" />
                </div>
        </form>
    </div>
<?

    View::show_footer();
}

?>