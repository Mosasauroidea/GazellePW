<?
/*
User subscription page
*/

if (isset($LoggedUser['PostsPerPage'])) {
    $PerPage = $LoggedUser['PostsPerPage'];
} else {
    $PerPage = CONFIG['POSTS_PER_PAGE'];
}
list($Page, $Limit) = Format::page_limit($PerPage);

View::show_header(t('server.userhistory.subscriptions'), 'subscriptions,comments,bbcode', 'PageUserHistorySubscription');

$ShowUnread = (!isset($_GET['showunread']) && !isset($HeavyInfo['SubscriptionsUnread']) || isset($HeavyInfo['SubscriptionsUnread']) && !!$HeavyInfo['SubscriptionsUnread'] || isset($_GET['showunread']) && !!$_GET['showunread']);
$ShowCollapsed = false;

// The monster sql query:
/*
 * Fields:
 * Page (artist, collages, requests, torrents or forums)
 * PageID (ArtistID, CollageID, RequestID, GroupID, TopicID)
 * PostID (of the last read post)
 * ForumID
 * ForumName
 * Name (for artists and collages; carries the topic title for forum subscriptions)
 * LastPost (PostID of the last post)
 * LastPostTime
 * LastReadBody
 * LastReadEditedTime
 * LastReadUserID
 * LastReadUsername
 * LastReadAvatar
 * LastReadEditedUserID
 */
$DB->query("
	(SELECT
		SQL_CALC_FOUND_ROWS
		s.Page,
		s.PageID,
		lr.PostID,
		null AS ForumID,
		null AS ForumName,
		IF(s.Page = 'artist', a.Name, co.Name) AS Name,
		c.ID AS LastPost,
		c.AddedTime AS LastPostTime,
		c_lr.Body AS LastReadBody,
		c_lr.EditedTime AS LastReadEditedTime,
		um.ID AS LastReadUserID,
		um.Username AS LastReadUsername,
		ui.Avatar AS LastReadAvatar,
		c_lr.EditedUserID AS LastReadEditedUserID
	FROM users_subscriptions_comments AS s
		LEFT JOIN users_comments_last_read AS lr ON lr.UserID = $LoggedUser[ID] AND lr.Page = s.Page AND lr.PageID = s.PageID
		LEFT JOIN artists_group AS a ON s.Page = 'artist' AND a.ArtistID = s.PageID
		LEFT JOIN collages AS co ON s.Page = 'collages' AND co.ID = s.PageID
		LEFT JOIN comments AS c ON c.ID = (
					SELECT MAX(ID)
					FROM comments
					WHERE Page = s.Page
						AND PageID = s.PageID
				)
		LEFT JOIN comments AS c_lr ON c_lr.ID = lr.PostID
		LEFT JOIN users_main AS um ON um.ID = c_lr.AuthorID
		LEFT JOIN users_info AS ui ON ui.UserID = um.ID
	WHERE s.UserID = $LoggedUser[ID] AND s.Page IN ('artist', 'collages', 'requests', 'torrents') AND (s.Page != 'collages' OR co.Deleted = '0')" . ($ShowUnread ? ' AND c.ID > IF(lr.PostID IS NULL, 0, lr.PostID)' : '') . "
	GROUP BY s.PageID)
	UNION ALL
	(SELECT 'forums', s.TopicID, lr.PostID, f.ID, f.Name, t.Title, p.ID, p.AddedTime, p_lr.Body, p_lr.EditedTime, um.ID, um.Username, ui.Avatar, p_lr.EditedUserID
	FROM users_subscriptions AS s
		LEFT JOIN forums_last_read_topics AS lr ON lr.UserID = $LoggedUser[ID] AND s.TopicID = lr.TopicID
		LEFT JOIN forums_topics AS t ON t.ID = s.TopicID
		LEFT JOIN forums AS f ON f.ID = t.ForumID
		LEFT JOIN forums_posts AS p ON p.ID = (
					SELECT MAX(ID)
					FROM forums_posts
					WHERE TopicID = s.TopicID
				)
		LEFT JOIN forums_posts AS p_lr ON p_lr.ID = lr.PostID
		LEFT JOIN users_main AS um ON um.ID = p_lr.AuthorID
		LEFT JOIN users_info AS ui ON ui.UserID = um.ID
	WHERE s.UserID = $LoggedUser[ID]" .
    ($ShowUnread ? " AND p.ID > IF(t.IsLocked = '1' AND t.IsSticky = '0'" . ", p.ID, IF(lr.PostID IS NULL, 0, lr.PostID))" : '') .
    ' AND ' . Forums::user_forums_sql() . "
	GROUP BY t.ID)
	ORDER BY LastPostTime DESC
	LIMIT $Limit");
$Results = $DB->to_array(false, MYSQLI_ASSOC, false);
$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();

$Debug->log_var($Results, 'Results');

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

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= t('server.userhistory.subscriptions') ?>
            <?= t('server.userhistory.with_unread_posts_number', ['Values' => [
                $ShowUnread ? ($NumResults ? t('server.userhistory.left_bracket') . $NumResults . t('server.userhistory.new_right_bracket') : '') : ''
            ]]) ?>
        </h2>
        <div class="BodyNavLinks">
            <?
            if (!$ShowUnread) {
            ?>
                <br /><br />
                <a href="userhistory.php?action=subscriptions&amp;showunread=1" class="brackets"><?= t('server.userhistory.only_display_subscriptions_with_unread_replies') ?></a>
            <?
            } else {
            ?>
                <br /><br />
                <a href="userhistory.php?action=subscriptions&amp;showunread=0" class="brackets"><?= t('server.userhistory.show_all_subscriptions') ?></a>
            <?
            }
            ?>
            <a href="userhistory.php?action=catchup&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.userhistory.catch_up') ?></a>
            <a href="userhistory.php?action=posts&amp;userid=<?= $LoggedUser['ID'] ?>" class="brackets"><?= t('server.userhistory.go_to_post_history') ?></a>
            <a href="userhistory.php?action=quote_notifications" class="brackets"><?= t('server.userhistory.quote_notifications') ?></a>
        </div>
    </div>
    <div class="BodyContent">
        <?
        if (!$NumResults) {
        ?>
            <div class="center">
                <?= t('server.userhistory.no_subscriptions') ?><?= $ShowUnread ? t('server.userhistory.with_unread_posts') : '' ?>
            </div>
        <?
        } else {
        ?>
            <div class="BodyNavLinks">
                <?
                $Pages = Format::get_pages($Page, $NumResults, $PerPage, 11);
                echo $Pages;
                ?>
            </div>
            <?
            foreach ($Results as $Result) {
                switch ($Result['Page']) {
                    case 'artist':
                        $Links = t('server.userhistory.artist') . ': ' . Artists::display_artist($Result);
                        $JumpLink = 'artist.php?id=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                        break;
                    case 'collages':
                        $Links = t('server.userhistory.collage') . ': <a href="collages.php?id=' . $Result['PageID'] . '">' . display_str($Result['Name']) . '</a>';
                        $JumpLink = 'collages.php?action=comments&collageid=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                        break;
                    case 'requests':
                        if (!isset($Requests[$Result['PageID']])) {
                            break;
                        }
                        $Request = $Requests[$Result['PageID']];

                        $CategoryName = $Categories[$CategoryID - 1];

                        $Links = t('server.userhistory.request') . ': ';
                        $Links .= '<a href="requests.php?action=view&amp;id=' . $Result['PageID'] . '">' . Torrents::group_name($Request, false) . "</a>";
                        $JumpLink = 'requests.php?action=view&amp;id=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                        break;
                    case 'torrents':
                        if (!isset($TorrentGroups[$Result['PageID']])) {
                            break;
                        }
                        $GroupInfo = $TorrentGroups[$Result['PageID']];
                        $Links = t('server.index.moviegroups') . ': ' . Torrents::group_name($GroupInfo);
                        $JumpLink = 'torrents.php?id=' . $GroupInfo['ID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                        break;
                    case 'forums':
                        $Links = t('server.userhistory.forums') . ': <a href="forums.php?action=viewforum&amp;forumid=' . $Result['ForumID'] . '">' . display_str($Result['ForumName']) . '</a> &gt; ' .
                            '<a href="forums.php?action=viewthread&amp;threadid=' . $Result['PageID'] .
                            '"  data-tooltip="' . display_str($Result['Name']) . '">' .
                            display_str(Format::cut_string($Result['Name'], 75)) .
                            '</a>';
                        $JumpLink = 'forums.php?action=viewthread&amp;threadid=' . $Result['PageID'] . '&amp;postid=' . $Result['PostID'] . '#post' . $Result['PostID'];
                        break;
                    default:
                        error(0);
                }
            ?>
                <div class="TableContainer">
                    <table class="TableForumPost Table <?= (!Users::has_avatars_enabled() ? ' noavatar' : '') ?>">
                        <tr class="Table-rowHeader notify_<?= $Result['Page'] ?>">
                            <td class="Table-cell" colspan="<?= Users::has_avatars_enabled() ? 2 : 1 ?>">
                                <div class="TableForumPostHeader">
                                    <div class="TableForumPostHeader-info">
                                        <?= $Links . ($Result['PostID'] < $Result['LastPost'] ? t('server.userhistory.span_new') : '') ?>
                                        <a class="last_read" data-tooltip="<?= t('server.common.jump_to_last_read') ?>" href="<?= $JumpLink ?>">
                                            <?= icon('Forum/jump-to-last-read') ?>
                                        </a>
                                    </div>
                                    <div class="TableForumPostHeader-actions">
                                        <? if ($Result['Page'] == 'forums') { ?>
                                            <span id="bar<?= $Result['PostID'] ?>">
                                                <a href="#" onclick="Subscribe(<?= $Result['PageID'] ?>); return false;" id="subscribelink<?= $Result['PageID'] ?>" class="brackets"><?= t('server.common.unsubscribe') ?></a>
                                            </span>
                                        <? } else { ?>
                                            <span id="bar_<?= $Result['Page'] . $Result['PostID'] ?>">
                                                <a href="#" onclick="SubscribeComments('<?= $Result['Page'] ?>', <?= $Result['PageID'] ?>); return false;" id="subscribelink_<?= $Result['Page'] . $Result['PageID'] ?>" class="brackets"><?= t('server.common.unsubscribe') ?></a>
                                            </span>
                                        <? } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <? if (!empty($Result['LastReadBody'])) { /* if a user is subscribed to a topic/comments but hasn't accessed the site ever, LastReadBody will be null - in this case we don't display a post. */ ?>
                            <tr class="TableForumPost-cellContent Table-row <?= $ShowCollapsed ? ' hidden' : '' ?>">
                                <? if (Users::has_avatars_enabled()) { ?>
                                    <td class="TableForumPost-cellAvatar Table-cell">
                                        <?= Users::show_avatar($Result['LastReadAvatar'], $Result['LastReadUserID'], $Result['LastReadUsername'], $HeavyInfo['DisableAvatars']) ?>
                                    </td>
                                <?          } ?>
                                <td class="TableForumPost-cellBody Table-cell">
                                    <div class="TableForumPostBody">
                                        <div class="TableForumPostBody-text HtmlText PostArticle">
                                            <?= Text::full_format($Result['LastReadBody']) ?>
                                        </div>
                                        <div class="TableForumPostBody-actions">
                                            <? if ($Result['LastReadEditedUserID']) { ?>
                                                <br /><br />
                                                <span class="last_edited"><?= t('server.userhistory.last_edited_by') ?>
                                                    <?= Users::format_username($Result['LastReadEditedUserID'], false, false, false) ?> <?= time_diff($Result['LastReadEditedTime']) ?></span>
                                            <?          } ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?      } ?>
                    </table>
                </div>
            <?  } ?>
            <div class="BodyNavLinks">
                <?= $Pages ?>
            </div>
        <? } ?>
    </div>
</div>
<?
View::show_footer();
