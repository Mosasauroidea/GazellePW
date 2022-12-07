<?php
/*
User post history page
*/

if (!empty($LoggedUser['DisableForums'])) {
    error(403);
}

$UserID = empty($_GET['userid']) ? $LoggedUser['ID'] : $_GET['userid'];
if (!is_number($UserID)) {
    error(0);
}

if (isset($LoggedUser['PostsPerPage'])) {
    $PerPage = $LoggedUser['PostsPerPage'];
} else {
    $PerPage = CONFIG['POSTS_PER_PAGE'];
}

list($Page, $Limit) = Format::page_limit($PerPage);

$UserInfo = Users::user_info($UserID);
extract(array_intersect_key($UserInfo, array_flip(array('Username', 'Enabled', 'Title', 'Avatar', 'Donor', 'Warned'))));

View::show_header(t('server.userhistory.post_history_for', ['Values' => [$Username]]), 'subscriptions,comments,bbcode', 'PageUserHistoryPost');

$ViewingOwn = ($UserID == $LoggedUser['ID']);
$ShowUnread = ($ViewingOwn && (!isset($_GET['showunread']) || !!$_GET['showunread']));
$ShowGrouped = ($ViewingOwn && (!isset($_GET['group']) || !!$_GET['group']));
if ($ShowGrouped) {
    $sql = '
		SELECT
			SQL_CALC_FOUND_ROWS
			MAX(p.ID) AS ID
		FROM forums_posts AS p
			LEFT JOIN forums_topics AS t ON t.ID = p.TopicID';
    if ($ShowUnread) {
        $sql .= '
			LEFT JOIN forums_last_read_topics AS l ON l.TopicID = t.ID AND l.UserID = ' . $LoggedUser['ID'];
    }
    $sql .= "
			LEFT JOIN forums AS f ON f.ID = t.ForumID
		WHERE p.AuthorID = $UserID
			AND " . Forums::user_forums_sql();
    if ($ShowUnread) {
        $sql .= '
			AND ((t.IsLocked = \'0\' OR t.IsSticky = \'1\')
			AND (l.PostID < t.LastPostID OR l.PostID IS NULL))';
    }
    $sql .= "
		GROUP BY t.ID
		ORDER BY p.ID DESC
		LIMIT $Limit";
    $PostIDs = $DB->query($sql);
    $DB->query('SELECT FOUND_ROWS()');
    list($Results) = $DB->next_record();

    if ($Results > $PerPage * ($Page - 1)) {
        $DB->set_query_id($PostIDs);
        $PostIDs = $DB->collect('ID');
        $sql = "
			SELECT
				p.ID,
				p.AddedTime,
				p.Body,
				p.EditedUserID,
				p.EditedTime,
				ed.Username,
				p.TopicID,
				t.Title,
				t.LastPostID,
				l.PostID AS LastRead,
				t.IsLocked,
				t.IsSticky
			FROM forums_posts AS p
				LEFT JOIN users_main AS um ON um.ID = p.AuthorID
				LEFT JOIN users_info AS ui ON ui.UserID = p.AuthorID
				LEFT JOIN users_main AS ed ON ed.ID = p.EditedUserID
				JOIN forums_topics AS t ON t.ID = p.TopicID
				JOIN forums AS f ON f.ID = t.ForumID
				LEFT JOIN forums_last_read_topics AS l ON l.UserID = $UserID
						AND l.TopicID = t.ID
			WHERE p.ID IN (" . implode(',', $PostIDs) . ')
			ORDER BY p.ID DESC';
        $Posts = $DB->query($sql);
    }
} else {
    $sql = '
		SELECT
			SQL_CALC_FOUND_ROWS';
    if ($ShowGrouped) {
        $sql .= '
			*
		FROM (
			SELECT';
    }
    $sql .= '
			p.ID,
			p.AddedTime,
			p.Body,
			p.EditedUserID,
			p.EditedTime,
			ed.Username,
			p.TopicID,
			t.Title,
			t.LastPostID,';
    if ($UserID == $LoggedUser['ID']) {
        $sql .= '
			l.PostID AS LastRead,';
    }
    $sql .= "
			t.IsLocked,
			t.IsSticky
		FROM forums_posts AS p
			LEFT JOIN users_main AS um ON um.ID = p.AuthorID
			LEFT JOIN users_info AS ui ON ui.UserID = p.AuthorID
			LEFT JOIN users_main AS ed ON ed.ID = p.EditedUserID
			JOIN forums_topics AS t ON t.ID = p.TopicID
			JOIN forums AS f ON f.ID = t.ForumID
			LEFT JOIN forums_last_read_topics AS l ON l.UserID = $UserID AND l.TopicID = t.ID
		WHERE p.AuthorID = $UserID
			AND " . Forums::user_forums_sql();

    if ($ShowUnread) {
        $sql .= '
			AND (	(t.IsLocked = \'0\' OR t.IsSticky = \'1\')
					AND (l.PostID < t.LastPostID OR l.PostID IS NULL)
				) ';
    }

    if ($UserID != $LoggedUser['ID'] && !check_perms('forums_see_hidden')) {
        $sql .= "
            AND t.hiddenreplies = '0'
        ";
    }

    $sql .= '
		ORDER BY p.ID DESC';

    if ($ShowGrouped) {
        $sql .= '
			) AS sub
		GROUP BY TopicID
		ORDER BY ID DESC';
    }

    $sql .= " LIMIT $Limit";
    $Posts = $DB->query($sql);

    $DB->query('SELECT FOUND_ROWS()');
    list($Results) = $DB->next_record();

    $DB->set_query_id($Posts);
}

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?
            if ($ShowGrouped) {
                echo t('server.userhistory.grouped')
                    . ($ShowUnread ? t('server.userhistory.unread') : '')
                    . t('server.userhistory.post_history_for', ['Values' => [
                        Users::format_username($UserID)
                    ]]);
            } elseif ($ShowUnread) {
                echo t('server.userhistory.unread_post_history_for', ['Values' => [
                    Users::format_username($UserID)
                ]]);
            } else {
                echo t('server.userhistory.post_history_for', ['Values' => [
                    Users::format_username($UserID)
                ]]);
            }
            ?>
        </h2>
        <div class="BodyNavLinks">
            <br /><br />
            <?
            if ($ViewingOwn) {
                $UserSubscriptions = Subscriptions::get_subscriptions();

                if (!$ShowUnread) {
                    if ($ShowGrouped) { ?>
                        <a href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>&amp;showunread=0&amp;group=0" class="brackets"><?= t('server.userhistory.show_all_posts') ?></a>
                    <?      } else { ?>
                        <a href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>&amp;showunread=0&amp;group=1" class="brackets"><?= t('server.userhistory.show_all_posts_grouped') ?></a>
                    <?      } ?>
                    <a href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>&amp;showunread=1&amp;group=1" class="brackets"><?= t('server.userhistory.only_display_posts_with_unread_replies_grouped') ?></a>
                <?  } else { ?>
                    <a href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>&amp;showunread=0&amp;group=0" class="brackets"><?= t('server.userhistory.show_all_posts') ?></a>
                    <? if (!$ShowGrouped) { ?>
                        <a href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>&amp;showunread=1&amp;group=1" class="brackets"><?= t('server.userhistory.only_display_posts_with_unread_replies_grouped') ?></a>
                    <?      } else { ?>
                        <a href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>&amp;showunread=1&amp;group=0" class="brackets"><?= t('server.userhistory.only_display_posts_with_unread_replies') ?></a>
                <?      }
                }
                ?>
                <a href="userhistory.php?action=subscriptions" class="brackets"><?= t('server.userhistory.go_to_subscriptions') ?></a>
            <?
            } else {
            ?>
                <a href="forums.php?action=search&amp;type=body&amp;user=<?= $Username ?>" class="brackets"><?= t('server.userhistory.search') ?></a>
            <?
            }
            ?>
        </div>
    </div>
    <div class="BodyContent">
        <?
        if (empty($Results)) {
        ?>
            <div class="center">
                <?= t('server.userhistory.no_topics') ?><?= $ShowUnread ? t('server.userhistory.with_unread_posts') : '' ?>
            </div>
        <?
        } else {
        ?>
            <div class="BodyNavLinks">
                <?
                $Pages = Format::get_pages($Page, $Results, $PerPage, 11);
                echo $Pages;
                ?>
            </div>
            <?
            $QueryID = $DB->get_query_id();
            while (list($PostID, $AddedTime, $Body, $EditedUserID, $EditedTime, $EditedUsername, $TopicID, $ThreadTitle, $LastPostID, $LastRead, $Locked, $Sticky) = $DB->next_record()) {
            ?>
                <div class="TableContainer">
                    <table class="TableForumPost Table <?= !Users::has_avatars_enabled() ? ' noavatar' : '' ?>" id="post<?= $PostID ?>">
                        <tr class="Table-rowHeader">
                            <td class="Table-cell" colspan="<?= Users::has_avatars_enabled() ? 2 : 1 ?>">
                                <div class="TableForumPostHeader">
                                    <div class="TableForumPostHeader-info">
                                        <?= time_diff($AddedTime) ?>
                                        <?= t('server.userhistory.in') ?>
                                        <a href="forums.php?action=viewthread&amp;threadid=<?= $TopicID ?>&amp;postid=<?= $PostID ?>#post<?= $PostID ?>" data-tooltip="<?= display_str($ThreadTitle) ?>">
                                            <?= Format::cut_string($ThreadTitle, 200) ?>
                                        </a>
                                        <?
                                        if ($ViewingOwn) {
                                            if ((!$Locked || $Sticky) && (!$LastRead || $LastRead < $LastPostID)) { ?>
                                                <span class="u-colorWarning">(<?= t('server.userhistory.new') ?>!)</span>
                                            <?
                                            }
                                            ?>
                                            <? if (!empty($LastRead)) { ?>
                                                <a class="TableForum-jumpToLastRead" data-tooltip="<?= t('server.common.jump_to_last_read') ?>" href="forums.php?action=viewthread&amp;threadid=<?= $TopicID ?>&amp;postid=<?= $LastRead ?>#post<?= $LastRead ?>">
                                                    <?= icon("Forum/jump-to-last-read") ?>
                                                </a>
                                            <? }
                                        } else {
                                            ?>
                                            </span>
                                        <? }
                                        ?>
                                    </div>
                                    <div class="TableForumPostHeader-actions" id="bar<?= $PostID ?>">
                                        <? if ($ViewingOwn && !in_array($TopicID, $UserSubscriptions)) { ?>
                                            <a href="#" onclick="Subscribe(<?= $TopicID ?>); $('.subscribelink<?= $TopicID ?>').remove(); return false;" class="brackets subscribelink<?= $TopicID ?>"><?= t('server.common.subscribe') ?></a>
                                        <? } ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?
                        if (!$ShowGrouped) {
                        ?>
                            <tr class="TableForumPost-cellContent Table-row">
                                <? if (Users::has_avatars_enabled()) { ?>
                                    <td class="TableForumPost-cellAvatar Table-cell">
                                        <?= Users::show_avatar($Avatar, $UserID, $Username, $HeavyInfo['DisableAvatars']) ?>
                                    </td>
                                <?  } ?>
                                <td class="TableForumPost-cellBody Table-cell">
                                    <div class="TableForumPostBody" id="content<?= $PostID ?>">
                                        <div class="TableForumPostBody-text HtmlText PostArticle">
                                            <?= Text::full_format($Body) ?>
                                        </div>
                                        <div class="TableForumPostBody-actions">
                                            <? if ($EditedUserID) { ?>
                                                <br />
                                                <br />
                                                <span class="last_edited">
                                                    <? if (check_perms('site_moderate_forums')) { ?>
                                                        <a href="#content<?= $PostID ?>" onclick="LoadEdit(<?= $PostID ?>, 1);">&laquo;</a>
                                                    <?              } ?>
                                                    <?= t('server.userhistory.last_edited_by') ?>
                                                    <?= Users::format_username($EditedUserID, false, false, false) ?> <?= time_diff($EditedTime, 2, true, true) ?>
                                                </span>
                                            <?          } ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?      }
                        $DB->set_query_id($QueryID);
                        ?>
                    </table>
                </div>
            <?  } ?>
            <div class="BodyNavLinks">
                <?= $Pages ?>
            </div>
        <? } ?>
    </div>
</div>
<? View::show_footer(); ?>