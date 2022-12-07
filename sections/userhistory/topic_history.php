<?php
/*
User topic history page
*/

if (!empty($LoggedUser['DisableForums'])) {
    error(403);
}

$UserID = empty($_GET['userid']) ? $LoggedUser['ID'] : $_GET['userid'];
if (!is_number($UserID)) {
    error(0);
}

$PerPage = CONFIG['TOPICS_PER_PAGE'];

list($Page, $Limit) = Format::page_limit($PerPage);

$UserInfo = Users::user_info($UserID);
$Username = $UserInfo['Username'];

View::show_header(t('server.userhistory.threads_started_by', ['Values' => [$Username]]), 'subscriptions,comments,bbcode', 'PageUserHistoryTopic');

$QueryID = $DB->prepared_query("
SELECT SQL_CALC_FOUND_ROWS
	t.ID,
	t.Title,
	t.CreatedTime,
	t.LastPostTime,
	f.ID,
	f.Name
FROM forums_topics AS t
	LEFT JOIN forums AS f ON f.ID = t.ForumID
WHERE t.AuthorID = ? AND " . Forums::user_forums_sql() . "
ORDER BY t.ID DESC
LIMIT {$Limit}", $UserID);


$DB->prepared_query('SELECT FOUND_ROWS()');
list($Results) = $DB->fetch_record();

$DB->set_query_id($QueryID);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= t('server.userhistory.threads_started_by', ['Values' => [
                Users::format_username($UserID)
            ]]) ?>
        </h2>
    </div>
    <?
    if (empty($Results)) {
    ?>
        <div class="center">
            <?= t('server.userhistory.no_topics') ?>
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
        <div class="TableContainer">
            <table class="TableForum Table">
                <tr class="TableForum-row Table-rowHeader">
                    <td class="TableForum-cellForumName Table-cell">
                        <?= t('server.userhistory.forum') ?>
                    </td>
                    <td class="TableForum-cellPost Table-cell">
                        <?= t('server.userhistory.topic') ?>
                    </td>
                    <td class="TableForum-cellCreatedAt TableForum-cellTime Table-cell">
                        <?= t('server.userhistory.topic_creation_time') ?>
                    </td>
                    <td class="TableForum-cellModifiedAt TableForum-cellTime Table-cell">
                        <?= t('server.userhistory.last_post_time') ?>
                    </td>
                </tr>
                <?
                $QueryID = $DB->get_query_id();
                while (list($TopicID, $Title, $CreatedTime, $LastPostTime, $ForumID, $ForumTitle) = $DB->fetch_record(1)) {
                ?>
                    <tr class="TableForum-row Table-row">
                        <td class="TableForum-cellForumName Table-cell">
                            <a href="forums.php?action=viewforum&forumid=<?= $ForumID ?>"><?= $ForumTitle ?></a>
                        </td>
                        <td class="TableForum-cellPost Table-cell">
                            <a href="forums.php?action=viewthread&threadid=<?= $TopicID ?>"><?= $Title ?>
                        </td>
                        <td class="TableForum-cellCreatedAt TableForum-cellTime Table-cell">
                            <?= time_diff($CreatedTime) ?>
                        </td>
                        <td class="TableForum-cellModifiedAt TableForum-cellTime Table-cell">
                            <?= time_diff($LastPostTime) ?>
                        </td>
                    </tr>
                <?  } ?>
            </table>
        </div>
        <div class="BodyNavLinks">
            <?= $Pages ?>
        </div>
    <? } ?>
</div>
<? View::show_footer(); ?>