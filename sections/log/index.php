<?
enforce_login();

if (!check_perms('site_view_full_log')) {
    error(403);
}
View::show_header(t('server.log.site_log'), '', 'PageLogHome');

include(CONFIG['SERVER_ROOT'] . '/sections/log/sphinx.php');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.log.site_log') ?></h2>
    </div>
    <form class="Form SearchPage Box SearchLog" name="log" action="" method="get">
        <div class="SearchPageBody">
            <table class="Form-rowList">
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.log.search_for') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="search" size="60" <?= (!empty($_GET['search']) ? ' value="' . display_str($_GET['search']) . '"' : '') ?> />
                    </td>

                </tr>
            </table>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-actions">
                <input class="Button" type="submit" value="<?= t('server.common.search') ?>" />
            </div>
        </div>
    </form>

    <? if ($TotalMatches > CONFIG['LOG_ENTRIES_PER_PAGE']) { ?>
        <div class="BodyNavLinks">
            <?
            $Pages = Format::get_pages($Page, $TotalMatches, CONFIG['LOG_ENTRIES_PER_PAGE'], 9);
            echo $Pages; ?>
        </div>
    <?  } ?>
    <div class="TableContainer">
        <table class="TableLogSearch Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell" style="width: 200px;"><strong><?= t('server.log.time') ?></strong></td>
                <td class="Table-cell"><strong><?= t('server.log.message') ?></strong></td>
            </tr>
            <? if ($QueryStatus) { ?>
                <tr class="Table-row">
                    <td class="Table-cell" colspan="2"><?= t('server.log.search_request_failed') ?> (<?= $QueryError ?>)<?= t('server.log.period') ?></td>
                </tr>
            <?  } elseif (!$DB->has_results()) { ?>
                <tr class="Table-row">
                    <td class="Table-cell" colspan="2"><?= t('server.log.nothing_found') ?></td>
                </tr>
                <?
            }
            $Usernames = array();
            while (list($ID, $Message, $LogTime) = $DB->next_record()) {
                $MessageParts = explode(' ', $Message);
                $Message = '';
                $Color = $Colon = false;
                $CheckedLog = false;
                for ($i = 0, $PartCount = sizeof($MessageParts); $i < $PartCount; $i++) {
                    if (
                        strpos($MessageParts[$i], site_url()) === 0
                        && $Offset = strlen(site_url())
                    ) {
                        $MessageParts[$i] = '<a href="' . substr($MessageParts[$i], $Offset) . '">' . substr($MessageParts[$i], $Offset) . '</a>';
                    }
                    switch ($MessageParts[$i]) {
                        case 'Torrent':
                        case 'torrent':
                            $TorrentID = $MessageParts[$i + 1];
                            if (is_numeric($TorrentID)) {
                                $Message = $Message . ' ' . $MessageParts[$i] . " <a href=\"torrents.php?torrentid=$TorrentID\">$TorrentID</a>";
                                $i++;
                            } else {
                                $Message = $Message . ' ' . $MessageParts[$i];
                            }
                            break;
                        case 'Request':
                            $RequestID = $MessageParts[$i + 1];
                            if (is_numeric($RequestID)) {
                                $Message = $Message . ' ' . $MessageParts[$i] . " <a href=\"requests.php?action=view&amp;id=$RequestID\">$RequestID</a>";
                                $i++;
                            } else {
                                $Message = $Message . ' ' . $MessageParts[$i];
                            }
                            break;
                        case 'Artist':
                        case 'artist':
                            $ArtistID = $MessageParts[$i + 1];
                            if (is_numeric($ArtistID)) {
                                $Message = $Message . ' ' . $MessageParts[$i] . " <a href=\"artist.php?id=$ArtistID\">$ArtistID</a>";
                                $i++;
                            } else {
                                $Message = $Message . ' ' . $MessageParts[$i];
                            }
                            break;
                        case 'group':
                        case 'Group':
                            $GroupID = $MessageParts[$i + 1];
                            if (is_numeric($GroupID)) {
                                $Message = $Message . ' ' . $MessageParts[$i] . " <a href=\"torrents.php?id=$GroupID\">$GroupID</a>";
                            } else {
                                $Message = $Message . ' ' . $MessageParts[$i];
                            }
                            $i++;
                            break;
                        case 'by':
                            $UserID = 0;
                            $User = '';
                            $URL = '';
                            if ($MessageParts[$i + 1] == 'user') {
                                $i++;
                                if (is_numeric($MessageParts[$i + 1])) {
                                    $UserID = $MessageParts[++$i];
                                }
                                $URL = "user $UserID (<a href=\"user.php?id=$UserID\">" . substr($MessageParts[++$i], 1, -1) . '</a>)';
                            } elseif (in_array($MessageParts[$i - 1], array('deleted', 'uploaded', 'edited', 'created', 'recovered'))) {
                                $User = $MessageParts[++$i];
                                if (substr($User, -1) == ':') {
                                    $User = substr($User, 0, -1);
                                    $Colon = true;
                                }
                                if (!isset($Usernames[$User])) {
                                    $DB->query("
							SELECT ID
							FROM users_main
							WHERE Username = _utf8 '" . db_string($User) . "'
							COLLATE utf8_bin");
                                    list($UserID) = $DB->next_record();
                                    $Usernames[$User] = $UserID ? $UserID : '';
                                } else {
                                    $UserID = $Usernames[$User];
                                }
                                $DB->set_query_id($Log);
                                $URL = $Usernames[$User] ? "<a href=\"user.php?id=$UserID\">$User</a>" . ($Colon ? ':' : '') : $User;
                            }
                            $Message = "$Message by $URL";
                            break;
                        case 'uploaded':
                            if ($Color === false) {
                                $Color = 'green';
                            }
                            $Message = $Message . ' ' . $MessageParts[$i];
                            break;
                        case 'deleted':
                            if ($Color === false || $Color === 'green') {
                                $Color = 'red';
                            }
                            $Message = $Message . ' ' . $MessageParts[$i];
                            break;
                        case 'edited':
                            if ($Color === false) {
                                $Color = 'blue';
                            }
                            $Message = $Message . ' ' . $MessageParts[$i];
                            break;
                        case 'un-filled':
                            if ($Color === false) {
                                $Color = '';
                            }
                            $Message = $Message . ' ' . $MessageParts[$i];
                            break;
                        case 'checked':
                        case 'unchecked':
                            if ($Color === false) {
                                $Color = 'yellow';
                            }
                            $Message = $Message . ' ' . $MessageParts[$i];
                            $CheckedLog = true;
                            break;
                        case 'marked':
                            if ($i == 1) {
                                $User = $MessageParts[$i - 1];
                                if (!isset($Usernames[$User])) {
                                    $DB->query("
							SELECT ID
							FROM users_main
							WHERE Username = _utf8 '" . db_string($User) . "'
							COLLATE utf8_bin");
                                    list($UserID) = $DB->next_record();
                                    $Usernames[$User] = $UserID ? $UserID : '';
                                    $DB->set_query_id($Log);
                                } else {
                                    $UserID = $Usernames[$User];
                                }
                                $URL = $Usernames[$User] ? "<a href=\"user.php?id=$UserID\">$User</a>" : $User;
                                $Message = $URL . " " . $MessageParts[$i];
                            } else {
                                $Message = $Message . ' ' . $MessageParts[$i];
                            }
                            break;
                        case 'Collage':
                            $CollageID = $MessageParts[$i + 1];
                            if (is_numeric($CollageID)) {
                                $Message = $Message . ' ' . $MessageParts[$i] . " <a href=\"collages.php?id=$CollageID\">$CollageID</a>";
                                $i++;
                            } else {
                                $Message = $Message . ' ' . $MessageParts[$i];
                            }
                            break;
                        default:
                            $Message = $Message . ' ' . $MessageParts[$i];
                    }
                }
                if (!$CheckedLog || check_perms('torrents_check_log')) {
                ?>
                    <tr class="Table-row" id="log_<?= $ID ?>">
                        <td class="Table-cell">
                            <?= time_diff($LogTime) ?>
                        </td>
                        <td class="Table-cell">
                            <span<? if ($Color) { ?> style="color: <?= $Color ?>;" <? } ?>><?= $Message ?></span>
                        </td>
                    </tr>
            <?
                }
            }
            ?>
        </table>
    </div>
    <? if ($TotalMatches > CONFIG['LOG_ENTRIES_PER_PAGE']) { ?>
        <div class="BodyNavLinks">
            <?= $Pages; ?>
        </div>
    <?  } ?>
</div>
<?
View::show_footer(); ?>