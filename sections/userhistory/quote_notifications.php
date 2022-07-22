<?php
if (!empty($LoggedUser['DisableForums'])) {
    error(403);
}

$UnreadSQL = 'AND q.UnRead';
if (isset($_GET['showall']) && $_GET['showall']) {
    $UnreadSQL = '';
}

if (isset($_GET['catchup']) && $_GET['catchup']) {
    $DB->query("UPDATE users_notify_quoted SET UnRead = '0' WHERE UserID = '$LoggedUser[ID]'");
    $Cache->delete_value('notify_quoted_' . $LoggedUser['ID']);
    header('Location: userhistory.php?action=quote_notifications');
    die();
}

if (isset($LoggedUser['PostsPerPage'])) {
    $PerPage = $LoggedUser['PostsPerPage'];
} else {
    $PerPage = CONFIG['POSTS_PER_PAGE'];
}
list($Page, $Limit) = Format::page_limit($PerPage);

// Get $Limit last quote notifications
// We deal with the information about torrents and requests later on...
$sql = "
	SELECT
		SQL_CALC_FOUND_ROWS
		q.Page,
		q.PageID,
		q.PostID,
		q.QuoterID,
		q.Date,
		q.UnRead,
		f.ID as ForumID,
		f.Name as ForumName,
		t.Title as ForumTitle,
		a.Name as ArtistName,
		c.Name as CollageName
	FROM users_notify_quoted AS q
		LEFT JOIN forums_topics AS t ON t.ID = q.PageID
		LEFT JOIN forums AS f ON f.ID = t.ForumID
		LEFT JOIN artists_group AS a ON a.ArtistID = q.PageID
		LEFT JOIN collages AS c ON c.ID = q.PageID
	WHERE q.UserID = $LoggedUser[ID]
		AND (q.Page != 'forums' OR " . Forums::user_forums_sql() . ")
		AND (q.Page != 'collages' OR c.Deleted = '0')
		$UnreadSQL
	ORDER BY q.Date DESC
	LIMIT $Limit";
$DB->query($sql);
$Results = $DB->to_array(false, MYSQLI_ASSOC, false);
$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();

$TorrentGroups = $Requests = array();
foreach ($Results as $Result) {
    if ($Result['Page'] == 'torrents') {
        $TorrentGroups[] = $Result['PageID'];
    } elseif ($Result['Page'] == 'requests') {
        $Requests[] = $Result['PageID'];
    }
}

$TorrentGroups = Torrents::get_groups($TorrentGroups, true, true, false);
$Requests = Requests::get_requests($Requests);

//Start printing page
View::show_header(Lang::get('userhistory.header_quote_notifications'), '', 'PageUserHistoryQuoteNotification');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= Lang::get('userhistory.quote_notifications') ?>
            <?= $NumResults && !empty($UnreadSQL) ? " ($NumResults" . Lang::get('userhistory.new_right_bracket') : '' ?>
        </h2>
        <div class="BodyNavLinks">
            <br />
            <? if ($UnreadSQL) { ?>
                <a href="userhistory.php?action=quote_notifications&amp;showall=1" class="brackets"><?= Lang::get('userhistory.show_all_quotes') ?></a>
            <? } else { ?>
                <a href="userhistory.php?action=quote_notifications" class="brackets"><?= Lang::get('userhistory.show_unread_quotes') ?></a>
            <? } ?>
            <a href="userhistory.php?action=subscriptions" class="brackets"><?= Lang::get('userhistory.show_subscriptions') ?></a>
            <a href="userhistory.php?action=quote_notifications&amp;catchup=1" class="brackets"><?= Lang::get('userhistory.catch_up') ?></a>
            <!-- <br /><br /> -->
            <?
            $Pages = Format::get_pages($Page, $NumResults, CONFIG['TOPICS_PER_PAGE'], 9);
            echo $Pages;
            ?>
        </div>
    </div>
    <? if (!$NumResults) { ?>
        <div class="center"><?= Lang::get('userhistory.no_quotes_before') ?><?= ($UnreadSQL ? Lang::get('userhistory.space_new') : '') ?><?= Lang::get('userhistory.no_quotes_after') ?></div>
    <? } ?>
    <br />
    <?
    foreach ($Results as $Result) {
        switch ($Result['Page']) {
            case 'forums':
                $Links = Lang::get('userhistory.forums') . ': <a href="forums.php?action=viewforum&amp;forumid=' . $Result['ForumID'] . '">' . display_str($Result['ForumName']) . '</a> &gt; ' .
                    '<a href="forums.php?action=viewthread&amp;threadid=' . $Result['PageID'] . '"  data-tooltip="' . display_str($Result['ForumTitle']) . '">' . Format::cut_string($Result['ForumTitle'], 75) . '</a>';
                $JumpLink = 'forums.php?action=viewthread&amp;threadid=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                break;
            case 'artist':
                $Links = Lang::get('userhistory.artist') . ': <a href="artist.php?id=' . $Result['PageID'] . '">' . display_str($Result['ArtistName']) . '</a>';
                $JumpLink = 'artist.php?id=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                break;
            case 'collages':
                $Links = Lang::get('userhistory.collage') . ': <a href="collages.php?id=' . $Result['PageID'] . '">' . display_str($Result['CollageName']) . '</a>';
                $JumpLink = 'collages.php?action=comments&amp;collageid=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                break;
            case 'requests':
                if (!isset($Requests[$Result['PageID']])) {
                    // Deleted request
                    continue 2;
                }
                $Request = $Requests[$Result['PageID']];
                $CategoryName = $Categories[$Request['CategoryID'] - 1];
                $Links = Lang::get('userhistory.request') . ': ';
                $Links .= '<a href="requests.php?action=view&amp;id=' . $Result['PageID'] . '">' . $Request['Title'] . "</a>";
                $JumpLink = 'requests.php?action=view&amp;id=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                break;
            case 'torrents':
                if (!isset($TorrentGroups[$Result['PageID']])) {
                    // Deleted or moved torrent group
                    continue 2;
                }
                $GroupInfo = $TorrentGroups[$Result['PageID']];
                $Links = Lang::get('userhistory.torrent') . ': ' . Artists::display_artists($GroupInfo['Artists']) . '<a href="torrents.php?id=' . $GroupInfo['ID'] . '" dir="ltr">' . $GroupInfo['Name'] . '</a>';
                if ($GroupInfo['Year'] > 0) {
                    $Links .= " [" . $GroupInfo['Year'] . "]";
                }
                if ($GroupInfo['ReleaseType'] > 0) {
                    $Links .= " [" . Lang::get('torrents.release_types')[$GroupInfo['ReleaseType']] . "]";
                }
                $JumpLink = 'torrents.php?id=' . $GroupInfo['ID'] . '&postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                break;
            default:
                continue 2;
        }
    ?>
        <div class="TableContainer">
            <table class="TableForumPost Table">
                <tr class="Table-rowHeader notify_<?= $Result['Page'] ?>">
                    <td class="Table-cell" colspan="2">
                        <div class="TableForumPostHeader">
                            <div class="TableForumPostHeader-info">
                                <?= $Links ?>
                                &gt; <?= Lang::get('userhistory.quoted_by') ?> <?= Users::format_username($Result['QuoterID'], false, false, false, false) . ' ' . time_diff($Result['Date']) ?>
                                <?= ($Result['UnRead'] ? Lang::get('userhistory.span_new') : '') ?>
                                <a data-tooltip="Jump to quote" href="<?= $JumpLink ?>">
                                    <?= icon('Forum/jump-to-last-read') ?>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    <? } ?>
</div>
<? View::show_footer(); ?>