<?php



$UserID = $LoggedUser['ID'];


if (empty($_GET['action'])) {
    $Section = 'inbox';
    $_GET['action'] = 'inbox';
} else {
    $Section = $_GET['action']; // either 'inbox' or 'sentbox'
}
if (!in_array($Section, array('inbox', 'sentbox'))) {
    error(404);
}

list($Page, $Limit) = Format::page_limit(CONFIG['MESSAGES_PER_PAGE']);

View::show_header(Lang::get('inbox', 'inbox'), '', 'PageInboxHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-title"><?= ($Section === 'sentbox' ? Lang::get('inbox', 'sentbox') : Lang::get('inbox', 'inbox')) ?></h2>
        <div class="BodyNavLinks">
            <?
            if ($Section === 'inbox') { ?>
                <a href="<?= Inbox::get_inbox_link('sentbox'); ?>" class="brackets"><?= Lang::get('inbox', 'sentbox') ?></a>
            <? } elseif ($Section === 'sentbox') { ?>
                <a href="<?= Inbox::get_inbox_link(); ?>" class="brackets"><?= Lang::get('inbox', 'inbox') ?></a>
            <? }

            ?>
            <?

            $Sort = empty($_GET['sort']) || $_GET['sort'] !== 'unread' ? 'Date DESC' : "cu.Unread = '1' DESC, DATE DESC";

            $sql = "
	SELECT
		SQL_CALC_FOUND_ROWS
		c.ID,
		c.Subject,
		cu.Unread,
		cu.Sticky,
		cu.ForwardedTo,
		cu2.UserID,";
            $sql .= $Section === 'sentbox' ? ' cu.SentDate ' : ' cu.ReceivedDate ';
            $sql .= "AS Date
	FROM pm_conversations AS c
		LEFT JOIN pm_conversations_users AS cu ON cu.ConvID = c.ID AND cu.UserID = '$UserID'
		LEFT JOIN pm_conversations_users AS cu2 ON cu2.ConvID = c.ID AND cu2.UserID != '$UserID' AND cu2.ForwardedTo = 0
		LEFT JOIN users_main AS um ON um.ID = cu2.UserID";

            if (!empty($_GET['search']) && $_GET['searchtype'] === 'message') {
                $sql .= ' JOIN pm_messages AS m ON c.ID = m.ConvID';
            }
            $sql .= ' WHERE ';
            if (!empty($_GET['search'])) {
                $Search = db_string($_GET['search']);
                if ($_GET['searchtype'] === 'user') {
                    $sql .= "um.Username LIKE '$Search' AND ";
                } elseif ($_GET['searchtype'] === 'subject') {
                    $Words = explode(' ', $Search);
                    $sql .= "c.Subject LIKE '%" . implode("%' AND c.Subject LIKE '%", $Words) . "%' AND ";
                } elseif ($_GET['searchtype'] === 'message') {
                    $Words = explode(' ', $Search);
                    $sql .= "m.Body LIKE '%" . implode("%' AND m.Body LIKE '%", $Words) . "%' AND ";
                }
            }
            $sql .= $Section === 'sentbox' ? ' cu.InSentbox' : ' cu.InInbox';
            $sql .= " = '1'";

            $sql .= "
	GROUP BY c.ID
	ORDER BY cu.Sticky, $Sort
	LIMIT $Limit";
            $Results = $DB->query($sql);
            $DB->query('SELECT FOUND_ROWS()');
            list($NumResults) = $DB->next_record();
            $DB->set_query_id($Results);
            $Count = $DB->record_count();

            $Pages = Format::get_pages($Page, $NumResults, CONFIG['MESSAGES_PER_PAGE'], 9);
            echo "\t\t$Pages\n";
            ?>
        </div>
    </div>

    <div class="BodyContent">
        <? if ($Count == 0 && empty($_GET['search'])) { ?>
            <h2><?= Lang::get('inbox', 'your') ?><?= ($Section === 'sentbox' ? Lang::get('inbox', 'sentbox') : Lang::get('inbox', 'inbox')) ?><?= Lang::get('inbox', 'is_empty') ?></h2>
        <? } else { ?>
            <form class="Form SearchInbox u-vstack" name="<?= ($Section === 'sentbox' ? 'sentbox' : 'inbox') ?>" action="inbox.php" method="get" id="searchbox">
                <div class="SearchInbox-row">
                    <input type="hidden" name="action" value="<?= $Section ?>" />
                    <div class="Radio">
                        <input class="Input" type="radio" name="searchtype" value="user" id="radio-user" <?= (empty($_GET['searchtype']) || $_GET['searchtype'] === 'user' ? ' checked="checked"' : '') ?> />
                        <label class="Radio-label" for="radio-user"><?= Lang::get('inbox', 'user') ?></label>
                    </div>
                    <div class="Radio">
                        <input class="Input" type="radio" name="searchtype" value="subject" id="radio-subject" <?= (!empty($_GET['searchtype']) && $_GET['searchtype'] === 'subject' ? ' checked="checked"' : '') ?> />
                        <label class="Radio-label" for="radio-subject"><?= Lang::get('inbox', 'subject') ?></label>
                    </div>
                    <div class="Radio">
                        <input class="Input" type="radio" name="searchtype" value="message" id="radio-message" <?= (!empty($_GET['searchtype']) && $_GET['searchtype'] === 'message' ? ' checked="checked"' : '') ?> />
                        <label class="Radio-label" for="radio-message"><?= Lang::get('inbox', 'message') ?></label>
                    </div>
                    <span class="SearchInbox-actions">
                        <?          // provide a temporary toggle for sorting PMs
                        $ToggleTitle = 'Temporary toggle switch for sorting PMs. To permanently change the sorting behavior, edit the setting in your profile.';
                        $BaseURL = "inbox.php?action={$_GET['action']}";

                        if ($_GET['sort'] === 'unread') { ?>
                            <a href="<?= $BaseURL ?>" class="brackets" data-tooltip="<?= $ToggleTitle ?>"><?= Lang::get('inbox', 'list_latest_first') ?></a>
                        <?      } else { ?>
                            <a href="<?= $BaseURL ?>&sort=unread" class="brackets" data-tooltip="<?= $ToggleTitle ?>"><?= Lang::get('inbox', 'list_unread_first') ?></a>
                        <?      } ?>
                    </span>
                </div>
                <div class="SearchInbox-row">
                    <input class="Input" type="text" name="text" placeholder="<?= (!empty($_GET['search']) ? display_str($_GET['search']) : Lang::get('inbox', 'placeholder_search') . ($Section === 'sentbox' ? Lang::get('inbox', 'sentbox') : Lang::get('inbox', 'inbox'))) ?>" />
                </div>
            </form>
            <form class="u-vstack manage_form" name="messages" action="inbox.php" method="post" id="messageform">
                <input type="hidden" name="action" value="masschange" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <div>
                    <input class="Button" type="submit" name="read" value="<?= Lang::get('inbox', 'mark_as_read') ?>" />&nbsp;
                    <input class="Button" type="submit" name="unread" value="<?= Lang::get('inbox', 'mark_as_unread') ?>" />&nbsp;
                    <input class="Button" type="submit" name="delete" value="<?= Lang::get('inbox', 'delete_messages') ?>" />
                </div>

                <div class="TableContainer">
                    <table class="TableUserInbox Table">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" width="10px"><input type="checkbox" onclick="toggleChecks('messageform', this);" /></td>
                            <td class="Table-cell" width="50%"><?= Lang::get('inbox', 'subject') ?></td>
                            <td class="Table-cell"><?= ($Section === 'sentbox' ? Lang::get('inbox', 'receiver') : Lang::get('inbox', 'sender')) ?></td>
                            <td class="Table-cell"><?= Lang::get('inbox', 'date') ?></td>
                            <? if (check_perms('users_mod')) { ?>
                                <td class="Table-cell"><?= Lang::get('inbox', 'forwarded_to') ?></td>
                            <?      } ?>
                        </tr>
                        <?
                        if ($Count == 0) { ?>
                            <tr class="Table-row">
                                <td class="Table-cell" colspan="5"><?= Lang::get('inbox', 'no_results') ?></td>
                            </tr>
                            <?  } else {
                            while (list($ConvID, $Subject, $Unread, $Sticky, $ForwardedID, $SenderID, $Date) = $DB->next_record()) {
                                if ($Unread === '1') {
                                    $RowClass = 'unreadpm';
                                } else {
                                    $Row = $Row === 'a' ? 'b' : 'a';
                                    $RowClass = "row$Row";
                                }
                            ?>
                                <tr class="Table-row">
                                    <td class="Table-cell Table-cellCenter"><input type="checkbox" name="messages[]=" value="<?= $ConvID ?>" /></td>
                                    <td class="Table-cell">
                                        <?
                                        echo "\t\t\t\t\t\t"; // for proper indentation of HTML
                                        if ($Unread) {
                                            echo '<strong>';
                                        }
                                        if ($Sticky) {
                                            echo 'Sticky: ';
                                        }
                                        echo "\n";
                                        ?>
                                        <a href="inbox.php?action=viewconv&amp;id=<?= $ConvID ?>"><?= $Subject ?></a>
                                        <?
                                        echo "\t\t\t\t\t\t"; // for proper indentation of HTML
                                        if ($Unread) {
                                            echo "</strong>\n";
                                        } ?>
                                    </td>
                                    <td class="Table-cell"><?= Users::format_username($SenderID, true, true, true, true) ?></td>
                                    <td class="Table-cell"><?= time_diff($Date) ?></td>
                                    <? if (check_perms('users_mod')) { ?>
                                        <td class="Table-cell"><?= (($ForwardedID && $ForwardedID != $LoggedUser['ID']) ? Users::format_username($ForwardedID, false, false, false) : '') ?></td>
                                    <?          } ?>
                                </tr>
                        <?
                                $DB->set_query_id($Results);
                            }
                        } ?>
                    </table>
                </div>
                <div>
                    <input class="Button" type="submit" name="read" value="<?= Lang::get('inbox', 'mark_as_read') ?>" />&nbsp;
                    <input class="Button" type="submit" name="unread" value="<?= Lang::get('inbox', 'mark_as_unread') ?>" />&nbsp;
                    <input class="Button" type="submit" name="delete" value="<?= Lang::get('inbox', 'delete_messages') ?>" />
                </div>
            </form>
        <? } ?>
        <div class="BodyNavLinks">
            <? echo "\t\t$Pages\n"; ?>
        </div>
    </div>
</div>
<?
View::show_footer();
?>