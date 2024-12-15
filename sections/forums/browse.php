<?

use Gazelle\Manager\Donation;

$LastRead = Forums::get_last_read($Forums);
View::show_header(t('server.forums.forums'), '', 'PageForumHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.forums.forums') ?></div>
        <div class="BodyNavLinks"><a href="forums.php?action=catchup&amp;forumid=all&amp;auth=<?= $LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.forums.catch_up') ?></a></div>
    </div>
    <div class="BodyContent">
        <?
        $Row = 'a';
        $LastCategoryID = 0;
        $OpenTable = false;
        foreach ($Forums as $Forum) {
            list($ForumID, $CategoryID, $ForumName, $ForumDescription, $MinRead, $MinWrite, $MinCreate, $NumTopics, $NumPosts, $LastPostID, $LastAuthorID, $LastTopicID, $LastTime, $SpecificRules, $LastTopic, $Locked, $Notice, $Sticky) = array_values($Forum);
            if (!Forums::check_forumperm($ForumID)) {
                continue;
            }
            if ($ForumID == CONFIG['DONOR_FORUM']) {
                $donation = new Donation;
                $ForumDescription = $donation->forumDescription();
            }
            $TooltipTheme = $ForumID == CONFIG['DONOR_FORUM'] ? 'gold' : '';
            $Row = $Row === 'a' ? 'b' : 'a';
            $ForumDescription = display_str($ForumDescription);

            if ($CategoryID != $LastCategoryID) {
                $Row = 'b';
                $LastCategoryID = $CategoryID;
                if ($OpenTable) {
        ?>
                    </table>
    </div>
</div>
</div>
<?
                }
?>
<div class="Group">
    <div class="Group-header">
        <div class="Group-headerTitle">
            <?= $ForumCats[$CategoryID] ?>
        </div>
    </div>
    <div class="Group-body">
        <div class="TableContainer">
            <table class="TableForum Table">
                <tr class="Table-rowHeader">
                    <td class="TableForum-cellReadStatus Table-cell"></td>
                    <td class="TableForum-cellForumName Table-cell"><?= t('server.forums.forum') ?></td>
                    <td class="TableForum-cellPost Table-cell"><?= t('server.forums.last_post') ?></td>
                    <td class="TableForum-cellTopics TableForum-cellValue Table-cell"><?= t('server.forums.topics') ?></td>
                    <td class="TableForum-cellPosts TableForum-cellValue Table-cell"><?= t('server.forums.posts') ?></td>
                </tr>
            <?
                $OpenTable = true;
            }

            $Read = Forums::is_unread($Locked, $Notice, $Sticky, $LastPostID, $LastRead, $LastTopicID, $LastTime) ? 'unread' : 'read';
            ?>
            <tr class="TableForum-row Table-row">
                <td class="TableForum-cellReadStatus Table-cell <?= $Read ?>" data-tooltip="<?= ucfirst($Read) ?>" data-tooltip-theme="<?= $TooltipTheme ?>">
                    <?= icon("Forum/${Read}") ?>
                </td>
                <td class="TableForum-cellForumName Table-cell">
                    <a href="forums.php?action=viewforum&amp;forumid=<?= $ForumID ?>" data-tooltip="<?= display_str($ForumDescription) ?>" data-tooltip-theme="<?= $TooltipTheme ?>"><?= display_str($ForumName) ?></a>
                </td>
                <?
                if ($NumPosts == 0) { ?>
                    <td class="TableForum-cellPost Table-cell">
                        <?= t('server.forums.there_are_no_topics') ?><?= (($MinCreate <= $LoggedUser['Class']) ? '<a href="forums.php?action=new&amp;forumid=' . $ForumID . '">' . t('server.forums.create_one') . '</a>' : '') ?>
                    </td>
                    <td class="TableForum-cellTopics TableForum-cellValue Table-cell">0</td>
                    <td class="TableForum-cellPosts TableForum-cellValue Table-cell">0</td>
                <?
                } else { ?>
                    <td class="TableForum-cellPost Table-cell">
                        <div class="TableForum-post">
                            <a href="forums.php?action=viewthread&amp;threadid=<?= $LastTopicID ?>" data-title-plain="<?= display_str($LastTopic) ?>" <?= ((strlen($LastTopic) > 50) ? "data-tooltip='" . display_str($LastTopic) . "'" : "") ?>><?= display_str(Format::cut_string($LastTopic, 50, 1)) ?></a>
                            <? if (!empty($LastRead[$LastTopicID])) { ?>
                                <a class="TableForum-jumpToLastRead" data-tooltip="<?= t('server.forums.jump_to_last_read') ?>" data-tooltip-theme="<?= $TooltipTheme ?>" href="forums.php?action=viewthread&amp;threadid=<?= $LastTopicID ?>&amp;page=<?= $LastRead[$LastTopicID]['Page'] ?>#post<?= $LastRead[$LastTopicID]['PostID'] ?>">
                                    <?= icon('Forum/jump-to-last-read'); ?>
                                </a>
                            <? } ?>
                            <span class="TableForum-lastPoster">
                                <?= t('server.forums.by') ?>
                                <span> </span>
                                <?= Users::format_username($LastAuthorID, false, false, false) ?>
                                <span> </span>
                                <?= time_diff($LastTime, 1) ?>
                            </span>
                        </div>
                    </td>
                    <td class="TableForum-cellTopics TableForum-cellValue Table-cell">
                        <?= number_format($NumTopics) ?>
                    </td>
                    <td class="TableForum-cellPosts TableForum-cellValue Table-cell">
                        <?= number_format($NumPosts) ?>
                    </td>
                <?
                } ?>
            </tr>
        <? } ?>
            </table>
        </div>
    </div>
</div>
</div>
</div>

<? View::show_footer(); ?>