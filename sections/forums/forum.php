<?php

/**********|| Page to show individual forums || ********************************\

Things to expect in $_GET:
    ForumID: ID of the forum curently being browsed
    page:   The page the user's on.
    page = 1 is the same as no page

 ********************************************************************************/

//---------- Things to sort out before it can start printing/generating content

// Check for lame SQL injection attempts
$ForumID = $_GET['forumid'];
if (!is_number($ForumID)) {
    error(0);
}

$IsDonorForum = $ForumID == CONFIG['DONOR_FORUM'] ? true : false;
$TooltipTheme = $ForumID == CONFIG['DONOR_FORUM'] ? "gold" : "";

if (isset($LoggedUser['PostsPerPage'])) {
    $PerPage = $LoggedUser['PostsPerPage'];
} else {
    $PerPage = CONFIG['POSTS_PER_PAGE'];
}

list($Page, $Limit) = Format::page_limit(CONFIG['TOPICS_PER_PAGE']);

//---------- Get some data to start processing

// Caching anything beyond the first page of any given forum is just wasting RAM.
// Users are more likely to search than to browse to page 2.
if ($Page == 1) {
    list($Forum,,, $Stickies) = $Cache->get_value("forums_$ForumID");
}
if (!isset($Forum) || !is_array($Forum)) {
    $DB->query("
		SELECT
			ID,
			Title,
			AuthorID,
			IsLocked,
			IsSticky,
			NumPosts,
			LastPostID,
			LastPostTime,
			LastPostAuthorID
		FROM forums_topics
		WHERE ForumID = '$ForumID'
		ORDER BY IsSticky DESC, Ranking = 0, Ranking ASC, LastPostTime DESC
		LIMIT $Limit"); // Can be cached until someone makes a new post
    $Forum = $DB->to_array('ID', MYSQLI_ASSOC, false);

    if ($Page == 1) {
        $DB->query("
			SELECT COUNT(ID)
			FROM forums_topics
			WHERE ForumID = '$ForumID'
				AND IsSticky = '1'");
        list($Stickies) = $DB->next_record();
        $Cache->cache_value("forums_$ForumID", array($Forum, '', 0, $Stickies), 0);
    }
}

if (!isset($Forums[$ForumID])) {
    error(404);
}
// Make sure they're allowed to look at the page
if (!check_perms('site_moderate_forums')) {
    if (isset($LoggedUser['CustomForums'][$ForumID]) && $LoggedUser['CustomForums'][$ForumID] === 0) {
        error(403);
    }
}


$ForumName = display_str($Forums[$ForumID]['Name']);
if (!Forums::check_forumperm($ForumID)) {
    error(403);
}
$Pages = Format::get_pages($Page, $Forums[$ForumID]['NumTopics'], CONFIG['TOPICS_PER_PAGE'], 9);

// Start printing
View::show_header($Forums[$ForumID]['Name'], '', $IsDonorForum ? 'donor' : '', 'PageForumShow');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <a href="forums.php"><?= t('server.forums.forums') ?></a> &gt; <?= $ForumName ?>
        </h2>
        <div class="BodyNavLinksWithExpand">
            <div class="BodyNavLinks">
                <? if (check_perms('site_moderate_forums')) { ?>
                    <a href="forums.php?action=edit_rules&amp;forumid=<?= $ForumID ?>" class="brackets"><?= t('server.forums.change_specific_rules') ?></a>
                <?  } ?>
                <? if (Forums::check_forumperm($ForumID, 'Write') && Forums::check_forumperm($ForumID, 'Create')) { ?>
                    <a href="forums.php?action=new&amp;forumid=<?= $ForumID ?>" class="brackets"><?= t('server.forums.new_thread') ?></a>
                <? } ?>
                <a href="#" onclick="$('#searchforum').gtoggle(); return false;" class="brackets">
                    <?= t('server.forums.search_this_forum') ?>
                </a>
                <a href="forums.php?action=catchup&amp;forumid=<?= $ForumID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.forums.catch_up') ?></a>
            </div>
            <div class="BodyNavLinks-expand hidden" id="searchforum">
                <form class="Form SearchPage Box FormForumSearch" name="forum" action="forums.php" method="get">
                    <div class="SearchPageHeader">
                        <div class="SearchPageHeader-title">
                            <?= t('server.forums.search_this_forum') ?>
                        </div>
                    </div>
                    <div class="SearchPageBody">
                        <table class="Form-rowList">
                            <tr class="Form-row">
                                <td class="Form-label">
                                    <input type="hidden" name="action" value="search" />
                                    <input type="hidden" name="forums[]" value="<?= $ForumID ?>" />
                                    <strong><?= t('server.forums.search_for') ?>:</strong>
                                </td>
                                <td class="Form-inputs">
                                    <input class="Input" type="text" id="searchbox" name="search" size="70" />
                                </td>
                            </tr>
                            <tr class="Form-row">
                                <td class="Form-label"><?= t('server.forums.search_in') ?>:</td>
                                <td class="Form-inputs">
                                    <div class="Radio">
                                        <input class="Input" type="radio" name="type" id="type_title" value="title" checked="checked" />
                                        <label class="Radio-label" for="type_title"><?= t('server.forums.titles') ?></label>
                                    </div>
                                    <div class="Radio">
                                        <input class="Input" type="radio" name="type" id="type_body" value="body" />
                                        <label class="Radio-label" for="type_body"><?= t('server.forums.post_bodies') ?></label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="Form-row">
                                <td class="Form-label"><?= t('server.forums.post_by') ?>:</td>
                                <td class="Form-inputs">
                                    <input class="Input" type="text" id="username" name="user" placeholder="<?= t('server.forums.username') ?>" size="70" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="SearchPageFooter">
                        <div class="SearchPageFooter-actions">
                            <button class="Button" type="submit" name="submit" value="Search"><?= t('server.common.search') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="ForumSpecialHeader">
        <? if (!empty($Forums[$ForumID]['SpecificRules'])) { ?>
            <?= t('server.forums.forum_specific_rules') ?>:&nbsp;
            <? foreach ($Forums[$ForumID]['SpecificRules'] as $ThreadIDs) {
                $Thread = Forums::get_thread_info($ThreadIDs);
                if ($Thread === null) {
                    error(404);
                }
            ?>
                &nbsp;<a href="forums.php?action=viewthread&amp;threadid=<?= $ThreadIDs ?>" class="brackets"><?= display_str($Thread['Title']) ?></a>
            <?      } ?>
        <?  } ?>

    </div>
    <? View::pages($Pages); ?>
    <div class="TableContainer">
        <table class="TableForum Table">
            <tr class="Table-rowHeader">
                <td class="TableForum-cellReadStatus Table-cell"></td>
                <td class="TableForum-cellPost Table-cell">
                    <?= t('server.forums.latest') ?>
                </td>
                <td class="TableForum-cellReplies TableForum-cellValue Table-cell">
                    <?= t('server.forums.replies') ?>
                </td>
                <td class="TableForum-cellAuthor TableForum-cellValue Table-cell">
                    <?= t('server.forums.author') ?>
                </td>
            </tr>
            <?
            // Check that we have content to process
            if (count($Forum) === 0) {
            ?>
                <tr class="TableForum-row Table-row">
                    <td class="TableForum-cellEmptyState Table-cell" colspan="4">
                        <?= t('server.forums.no_threads_in_forum') ?>
                    </td>
                </tr>
                <?
            } else {
                // forums_last_read_topics is a record of the last post a user read in a topic, and what page that was on
                $DB->query("
		SELECT
			l.TopicID,
			l.PostID,
			CEIL((
					SELECT COUNT(p.ID)
					FROM forums_posts AS p
					WHERE p.TopicID = l.TopicID
						AND p.ID <= l.PostID
				) / $PerPage
			) AS Page
		FROM forums_last_read_topics AS l
		WHERE l.TopicID IN (" . implode(', ', array_keys($Forum)) . ')
			AND l.UserID = \'' . $LoggedUser['ID'] . '\'');

                // Turns the result set into a multi-dimensional array, with
                // forums_last_read_topics.TopicID as the key.
                // This is done here so we get the benefit of the caching, and we
                // don't have to make a database query for each topic on the page
                $LastRead = $DB->to_array('TopicID');

                //---------- Begin printing

                $Row = 'a';
                foreach ($Forum as $Topic) {
                    list($TopicID, $Title, $AuthorID, $Locked, $Sticky, $PostCount, $LastID, $LastTime, $LastAuthorID) = array_values($Topic);
                    $Row = $Row === 'a' ? 'b' : 'a';
                    // Build list of page links
                    // Only do this if there is more than one page
                    $PageLinks = array();
                    $ShownEllipses = false;
                    $PagesText = '';
                    $TopicPages = ceil($PostCount / $PerPage);

                    if ($TopicPages > 1) {
                        $PagesText = ' (';
                        for ($i = 1; $i <= $TopicPages; $i++) {
                            if ($TopicPages > 4 && ($i > 2 && $i <= $TopicPages - 2)) {
                                if (!$ShownEllipses) {
                                    $PageLinks[] = '-';
                                    $ShownEllipses = true;
                                }
                                continue;
                            }
                            $PageLinks[] = "<a href=\"forums.php?action=viewthread&amp;threadid=$TopicID&amp;page=$i\">$i</a>";
                        }
                        $PagesText .= implode(' ', $PageLinks);
                        $PagesText .= ')';
                    }

                    // handle read/unread posts - the reason we can't cache the whole page
                    if ((!$Locked || $Sticky) && ((empty($LastRead[$TopicID]) || $LastRead[$TopicID]['PostID'] < $LastID) && strtotime($LastTime) > $LoggedUser['CatchupTime'])) {
                        $Read = 'unread';
                    } else {
                        $Read = 'read';
                    }
                    if ($Locked) {
                        $Read .= '_locked';
                    }
                    if ($Sticky) {
                        $Read .= '_sticky';
                    }
                ?>
                    <tr class="TableForum-row Table-row">
                        <td class="TableForum-cellReadStatus Table-cell <?= $Read ?>" data-tooltip="<?= ucwords(str_replace('_', ' ', $Read)) ?>" data-tooltip-theme="<?= $TooltipTheme ?>">
                            <?= icon("Forum/$Read") ?>
                        </td>
                        <td class="TableForum-cellPost Table-cell">
                            <div class="TableForum-post">
                                <?
                                $TopicLength = 200 - (2 * count($PageLinks));
                                unset($PageLinks);
                                $DisplayTitle = display_str($Title);

                                ?>
                                <a href="forums.php?action=viewthread&amp;threadid=<?= $TopicID ?>" data-title-plain="<?= $DisplayTitle ?>" <?= (strlen($Title) > $TopicLength ? "data-tooltip='" . $DisplayTitle . "'" : "") ?>><?= display_str(Format::cut_string($Title, $TopicLength, true)) ?></a>
                                <?= $PagesText ?>
                                <? if (!empty($LastRead[$TopicID])) { ?>
                                    <a class="TableForum-jumpToLastRead " data-tooltip="<?= t('server.forums.jump_to_last_read') ?>" data-tooltip="<?= $TooltipTheme ?>" href="forums.php?action=viewthread&amp;threadid=<?= $TopicID ?>&amp;page=<?= $LastRead[$TopicID]['Page'] ?>#post<?= $LastRead[$TopicID]['PostID'] ?>">
                                        <?= icon('Forum/jump-to-last-read'); ?>

                                    </a>
                                <?      } ?>
                                <span class="TableForum-lastPoster">
                                    <?= t('server.forums.by') ?>
                                    <span> </span>
                                    <?= Users::format_username($LastAuthorID, false, false, false, false, false, $IsDonorForum) ?>
                                    <span> </span>
                                    <?= time_diff($LastTime, 1) ?>
                                </span>
                            </div>
                        </td>
                        <td class="TableForum-cellReplies TableForum-cellValue Table-cell">
                            <?= number_format($PostCount - 1) ?>
                        </td>
                        <td class="TableForum-cellAuthor TableForum-cellValue Table-cell">
                            <?= Users::format_username($AuthorID, false, false, false, false, false, $IsDonorForum) ?>
                        </td>
                    </tr>
            <?  }
            } ?>
        </table>
    </div>
    <!--<div class="breadcrumbs">
    <a href="forums.php">Forums</a> &gt; <?= $ForumName ?>
</div>-->
    <? View::pages($Pages); ?>
</div>
<? View::show_footer(); ?>