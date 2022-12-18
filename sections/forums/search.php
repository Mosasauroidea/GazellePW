<?
//TODO: Clean up this fucking mess
/*
Forums search result page
*/

list($Page, $Limit) = Format::page_limit(CONFIG['POSTS_PER_PAGE']);

if (isset($_GET['type']) && $_GET['type'] === 'body') {
    $Type = 'body';
} else {
    $Type = 'title';
}

// What are we looking for? Let's make sure it isn't dangerous.
if (isset($_GET['search'])) {
    $Search = trim($_GET['search']);
} else {
    $Search = '';
}

$ThreadAfterDate = db_string($_GET['thread_created_after']);
$ThreadBeforeDate = db_string($_GET['thread_created_before']);

if ((!empty($ThreadAfterDate) && !is_valid_date($ThreadAfterDate)) || (!empty($ThreadBeforeDate) && !is_valid_date($ThreadBeforeDate))) {
    error("Incorrect topic created date");
}

$PostAfterDate = db_string($_GET['post_created_after']);
$PostBeforeDate = db_string($_GET['post_created_before']);

if ((!empty($PostAfterDate) && !is_valid_date($PostAfterDate)) || (!empty($PostBeforeDate) && !is_valid_date($PostBeforeDate))) {
    error("Incorrect post created date");
}

// Searching for posts by a specific user
if (!empty($_GET['user'])) {
    $User = trim($_GET['user']);
    $DB->query("
		SELECT ID
		FROM users_main
		WHERE Username = '" . db_string($User) . "'");
    list($AuthorID) = $DB->next_record();
    if ($AuthorID === null) {
        $AuthorID = 0;
        //this will cause the search to return 0 results.
        //workaround in line 276 to display that the username was wrong.
    }
} else {
    $User = '';
}

// Are we looking in individual forums?
if (isset($_GET['forums']) && is_array($_GET['forums'])) {
    $ForumArray = array();
    foreach ($_GET['forums'] as $Forum) {
        if (is_number($Forum)) {
            $ForumArray[] = $Forum;
        }
    }
    if (count($ForumArray) > 0) {
        $SearchForums = implode(', ', $ForumArray);
    }
}

// Searching for posts in a specific thread
if (!empty($_GET['threadid']) && is_number($_GET['threadid'])) {
    $ThreadID = $_GET['threadid'];
    $Type = 'body';
    $SQL = "
		SELECT
			Title
		FROM forums_topics AS t
			JOIN forums AS f ON f.ID = t.ForumID
		WHERE t.ID = $ThreadID
			AND " . Forums::user_forums_sql();
    $DB->query($SQL);
    if (list($Title) = $DB->next_record()) {
        $Title = " &gt; <a href=\"forums.php?action=viewthread&amp;threadid=$ThreadID\">$Title</a>";
    } else {
        error(404);
    }
} else {
    $ThreadID = '';
}
$ForumCategories = [];
$LastCategoryID = -1;
foreach ($Forums as $Forum) {
    if (!Forums::check_forumperm($Forum['ID'])) {
        continue;
    }
    $ForumCategories[$Forum['CategoryID']][] = $Forum;
}

// Let's hope we got some results - start printing out the content.
View::show_header(t('server.forums.forums_greater_than_search'), 'bbcode,forum_search,datetime_picker', 'PageFormSearch');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><a href="forums.php"><?= t('server.forums.forums') ?></a> &gt; <?= t('server.forums.search') ?><?= $Title ?></h2>
    </div>
    <form class="Form SearchPage Box SearchForum" name="forums" action="" method="get">
        <input type="hidden" name="action" value="search" />
        <div class="SearchPageBody">
            <table class="Form-rowList">
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.forums.search_for') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="search" size="70" value="<?= display_str($Search) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.forums.posted_by') ?>:</td>
                    <td class="Form-inputs">
                        <input class="Input" type="text" name="user" placeholder="Username" size="70" value="<?= display_str($User) ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.forums.topic_created') ?>:</td>
                    <td class="Form-inputs">
                        <?= t('server.forums.after') ?>:
                        <input class="Input is-small" type="text" name="thread_created_after" id="thread_created_after" value="<?= $ThreadAfterDate ?>" />
                        <?= t('server.forums.before') ?>:
                        <input class="Input is-small" type="text" name="thread_created_before" id="thread_created_before" value="<?= $ThreadBeforeDate ?>" />
                    </td>
                </tr>
                <?
                if (empty($ThreadID)) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.forums.search_in') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Radio">
                                <input class="Input" type="radio" name="type" id="type_title" value="title" <? if ($Type == 'title') {
                                                                                                                echo ' checked="checked"';
                                                                                                            } ?> />
                                <label class="Radio-label" for="type_title"><?= t('server.forums.titles') ?></label>
                            </div>
                            <div class="Radio">
                                <input class="Input" type="radio" name="type" id="type_body" value="body" <? if ($Type == 'body') {
                                                                                                                echo ' checked="checked"';
                                                                                                            } ?> />
                                <label class="Radio-label" for="type_body"><?= t('server.forums.post_bodies') ?></label>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row <?= $Type == 'title' ? 'hidden' : '' ?>" id="post_created_row">
                        <td class="Form-label"><?= t('server.forums.post_created') ?>:</td>
                        <td class="Form-inputs">
                            <?= t('server.forums.after') ?>:
                            <input class="Input is-small" type="text" name="post_created_after" id="post_created_after" value="<?= $PostAfterDate ?>" />
                            <?= t('server.forums.before') ?>:
                            <input class="Input is-small" type="text" name="post_created_before" id="post_created_before" value="<?= $PostBeforeDate ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row" variant="alighLeft">
                        <td class="Form-label"><?= t('server.forums.search_forums') ?>:</td>
                        <td class="Form-items" id="forum_search_cat_list">
                            <?
                            // List of forums
                            foreach ($ForumCategories as $Category => $Forums) {
                                $CheckAll = true;
                                foreach ($Forums as $Forum) {
                                    if (!in_array($Forum['ID'], $_GET['forums'])) {
                                        $CheckAll = false;
                                        break;
                                    }
                                }

                            ?>
                                <div class="Form-inputs">
                                    <strong><?= $ForumCats[$Category] ?></strong> -
                                    <div class="Checkbox">
                                        <input class="Input" type="checkbox" id="forum_category_<?= $Category ?>" onchange="toggleAll('forum_category_' + <?= $Category ?>);" <?= $CheckAll ? ' checked="checked"' : '' ?> />
                                        <label class="Checkbox-label" for="toggle_category_<?= $Category ?>"><?= t('client.common.check_all') ?></label>
                                    </div>
                                    <?
                                    foreach ($Forums as $Forum) {
                                    ?>
                                        <div class="Checkbox">
                                            <input class="Input" type="checkbox" name="forums[]" value="<?= $Forum['ID'] ?>" data-category="forum_category_<?= $Category ?>" id="forum_<?= $Forum['ID'] ?>" <? if (isset($_GET['forums']) && in_array($Forum['ID'], $_GET['forums'])) {
                                                                                                                                                                                                                echo ' checked="checked"';
                                                                                                                                                                                                            } ?> />
                                            <label class="Checkbox-label" for="forum_<?= $Forum['ID'] ?>"><?= htmlspecialchars($Forum['Name']) ?></label>
                                        </div>
                                    <?

                                    }
                                    ?>
                                </div>
                            <?
                            }
                            ?>
                        </td>
                    </tr>
                <? } else { ?>

                    <input type="hidden" name="threadid" value="<?= $ThreadID ?>" />
                <? } ?>
            </table>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-actions">
                <button class="Button" type="submit" value="Search"><?= t('server.common.search') ?></button>
            </div>
        </div>
    </form>
    <div class="BodyNavLinks">
        <?

        // Break search string down into individual words
        $Words = explode(' ', db_string($Search));

        if ($Type == 'body') {

            $SQL = "
		SELECT
			SQL_CALC_FOUND_ROWS
			t.ID,
			" . (!empty($ThreadID) ? "SUBSTRING_INDEX(p.Body, ' ', 40)" : 't.Title') . ",
			t.ForumID,
			f.Name,
			p.AddedTime,
			p.ID,
			p.Body,
			t.CreatedTime
		FROM forums_posts AS p
			JOIN forums_topics AS t ON t.ID = p.TopicID
			JOIN forums AS f ON f.ID = t.ForumID
		WHERE " . Forums::user_forums_sql() . ' AND ';

            //In tests, this is significantly faster than LOCATE
            $SQL .= "p.Body LIKE '%";
            $SQL .= implode("%' AND p.Body LIKE '%", $Words);
            $SQL .= "%' ";

            //$SQL .= "LOCATE('";
            //$SQL .= implode("', p.Body) AND LOCATE('", $Words);
            //$SQL .= "', p.Body) ";

            if (isset($SearchForums)) {
                $SQL .= " AND f.ID IN ($SearchForums)";
            }
            if (isset($AuthorID)) {
                $SQL .= " AND p.AuthorID = '$AuthorID' ";
            }
            if (!empty($ThreadID)) {
                $SQL .= " AND t.ID = '$ThreadID' ";
            }
            if (!empty($ThreadAfterDate)) {
                $SQL .= " AND t.CreatedTime >= '$ThreadAfterDate'";
            }
            if (!empty($ThreadBeforeDate)) {
                $SQL .= " AND t.CreatedTime <= '$ThreadBeforeDate'";
            }
            if (!empty($PostAfterDate)) {
                $SQL .= " AND p.AddedTime >= '$PostAfterDate'";
            }
            if (!empty($PostBeforeDate)) {
                $SQL .= " AND p.AddedTime <= '$PostBeforeDate'";
            }

            $SQL .= "
		ORDER BY p.AddedTime DESC
		LIMIT $Limit";
        } else {
            $SQL = "
		SELECT
			SQL_CALC_FOUND_ROWS
			t.ID,
			t.Title,
			t.ForumID,
			f.Name,
			t.LastPostTime,
			'',
			'',
			t.CreatedTime
		FROM forums_topics AS t
			JOIN forums AS f ON f.ID = t.ForumID
		WHERE " . Forums::user_forums_sql() . ' AND ';
            $SQL .= "t.Title LIKE '%";
            $SQL .= implode("%' AND t.Title LIKE '%", $Words);
            $SQL .= "%' ";
            if (isset($SearchForums)) {
                $SQL .= " AND f.ID IN ($SearchForums)";
            }
            if (isset($AuthorID)) {
                $SQL .= " AND t.AuthorID = '$AuthorID' ";
            }
            if (!empty($ThreadAfterDate)) {
                $SQL .= " AND t.CreatedTime >= '$ThreadAfterDate'";
            }
            if (!empty($ThreadBeforeDate)) {
                $SQL .= " AND t.CreatedTime <= '$ThreadBeforeDate'";
            }
            $SQL .= "
		ORDER BY t.LastPostTime DESC
		LIMIT $Limit";
        }

        // Perform the query
        $Records = $DB->query($SQL);
        $DB->query('SELECT FOUND_ROWS()');
        list($Results) = $DB->next_record();
        $DB->set_query_id($Records);

        $Pages = Format::get_pages($Page, $Results, CONFIG['POSTS_PER_PAGE'], 9);
        echo $Pages;
        ?>
    </div>
    <div class="TableContainer">
        <table class="TableForum Table">
            <tr class="Table-rowHeader">
                <td class="TableForum-cellForumName Table-cell">
                    <?= t('server.forums.forum') ?>
                </td>
                <td class="TableForum-cellPost Table-cell">
                    <?= ((!empty($ThreadID)) ? 'Post begins' : t('server.forums.topic')) ?>
                </td>
                <td class="TableForum-cellCreatedAt TableForum-cellTime Table-cell">
                    <?= t('server.forums.topic_creation_time') ?>
                </td>
                <td class="TableForum-cellModifiedAt TableForum-cellTime Table-cell">
                    <?= t('server.forums.last_post_time') ?>
                </td>
            </tr>
            <? if (!$DB->has_results()) { ?>
                <tr class="TableForum-row Table-row">
                    <td class="TableForum-cellEmptyState Table-cell" colspan="4">Nothing found<?= ((isset($AuthorID) && $AuthorID == 0) ? t('server.forums.unknown_username') : '') ?>!</td>
                </tr>
            <? }

            $Row = 'a'; // For the pretty colours
            while (list($ID, $Title, $ForumID, $ForumName, $LastTime, $PostID, $Body, $ThreadCreatedTime) = $DB->next_record(MYSQLI_BOTH, false)) {
                $Row = $Row === 'a' ? 'b' : 'a';
                // Print results
            ?>
                <tr class="TableForum-row Table-row">
                    <td class="TableForum-cellForumName Table-cell">
                        <a href="forums.php?action=viewforum&amp;forumid=<?= $ForumID ?>"><?= $ForumName ?></a>
                    </td>
                    <td class="TableForum-cellPost Table-cell">
                        <div class="TableForum-post">
                            <? if (empty($ThreadID)) { ?>
                                <a href="forums.php?action=viewthread&amp;threadid=<?= $ID ?>"><?= Format::cut_string($Title, 80, true); ?></a>
                            <?  } else { ?>
                                <?= Format::cut_string($Title, 80, true); ?>
                            <?
                            }
                            if ($Type == 'body') { ?>
                                <a href="#" onclick="$('#post_<?= $PostID ?>_text').gtoggle(); return false;">(Show)</a>
                                <span> </span>
                                <span data-tooltip="Jump to post">
                                    <a class="TableForum-jumpToLastRead" href="forums.php?action=viewthread&amp;threadid=<?= $ID ?><? if (!empty($PostID)) {
                                                                                                                                        echo "&amp;postid=$PostID#post$PostID";
                                                                                                                                    } ?>">

                                        <?= icon('Forum/jump-to-last-read'); ?>
                                    </a>
                                </span>
                            <?  } ?>
                        </div>
                    </td>
                    <td class="TableForum-cellCreatedAt TableForum-cellTime Table-cell">
                        <?= time_diff($ThreadCreatedTime) ?>
                    </td>
                    <td class="TableForum-cellModifiedAt TableForum-cellTime Table-cell">
                        <?= time_diff($LastTime) ?>
                    </td>
                </tr>
                <? if ($Type == 'body') { ?>
                    <tr class="TableForum-row Table-row hidden" id="post_<?= $PostID ?>_text">
                        <td class="TableForum-cellPostBody Table-cell" colspan="4">
                            <div class="HtmlText">
                                <?= Text::full_format($Body) ?>
                            </div>
                        </td>
                    </tr>
                <? } ?>
            <? } ?>
        </table>
    </div>

    <div class="BodyNavLinks">
        <?= $Pages ?>
    </div>
</div>
<? View::show_footer(); ?>