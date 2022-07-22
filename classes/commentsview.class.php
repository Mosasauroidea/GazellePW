<?
class CommentsView {
    /**
     * Render a thread of comments
     * @param array $Thread An array as returned by Comments::load
     * @param int $LastRead PostID of the last read post
     * @param string $Baselink Link to the site these comments are on
     */
    public static function render_comments($Thread, $LastRead, $Baselink) {
        foreach ($Thread as $Post) {
            list($PostID, $AuthorID, $AddedTime, $CommentBody, $EditedUserID, $EditedTime, $EditedUsername) = array_values($Post);
            self::render_comment($AuthorID, $PostID, $CommentBody, $AddedTime, $EditedUserID, $EditedTime, $Baselink . "&amp;postid=$PostID#post$PostID", ($PostID > $LastRead));
        }
    }

    /**
     * Render one comment
     * @param int $AuthorID
     * @param int $PostID
     * @param string $Body
     * @param string $AddedTime
     * @param int $EditedUserID
     * @param string $EditedTime
     * @param string $Link The link to the post elsewhere on the site
     * @param string $Header The header used in the post
     * @param bool $Tools Whether or not to show [Edit], [Report] etc.
     * @todo Find a better way to pass the page (artist, collages, requests, torrents) to this function than extracting it from $Link
     */
    public static function render_comment($AuthorID, $PostID, $Body, $AddedTime, $EditedUserID, $EditedTime, $Link, $Unread = false, $Header = '', $Tools = true) {
        $UserInfo = Users::user_info($AuthorID);
        $Header = '<strong>' . Users::format_username($AuthorID, true, true, true, true, false, false, false, true) . '</strong> ' . time_diff($AddedTime) . $Header;
?>
        <div class="TableContainer">
            <table class="TableForumPost Table <?= (!Users::has_avatars_enabled() ? ' noavatar' : '') . ($Unread ? ' forum_unread' : '') ?>" id="post<?= $PostID ?>">
                <tr class="Table-rowHeader">
                    <td class="Table-cell" colspan="<?= (Users::has_avatars_enabled() ? 2 : 1) ?>">
                        <div class="TableForumPostHeader">
                            <div class="TableForumPostHeader-info">
                                <a class="TableForumPost-postId" href="<?= $Link ?>">#<?= $PostID ?></a>
                                <?= $Header ?>
                            </div>
                            <div class="TableForumPostHeader-actions" id="bar<?= $PostID ?>">
                                <? if ($Tools) { ?>
                                    <a href="#quickpost" onclick="Quote('<?= $PostID ?>','<?= $UserInfo['Username'] ?>', true);" class="brackets"><?= Lang::get('forums.quote') ?></a>
                                    <? if ($AuthorID == G::$LoggedUser['ID'] || check_perms('site_moderate_forums')) { ?>
                                        - <a href="#post<?= $PostID ?>" onclick="Edit_Form('<?= $PostID ?>','<?= $Key ?>');" class="brackets"><?= Lang::get('global.edit') ?></a>
                                    <? } ?>
                                    <? if (check_perms('site_moderate_forums')) { ?>
                                        - <a href="#post<?= $PostID ?>" onclick="Delete('<?= $PostID ?>');" class="brackets"><?= Lang::get('global.delete') ?></a>
                                    <? } ?>
                                    - <a href="reports.php?action=report&amp;type=comment&amp;id=<?= $PostID ?>" class="brackets"><?= Lang::get('forums.report') ?></a>
                                    <? if (check_perms('users_warn') && $AuthorID != G::$LoggedUser['ID'] && G::$LoggedUser['Class'] >= $UserInfo['Class']) { ?>
                                        <form class="manage_form hidden" name="user" id="warn<?= $PostID ?>" action="comments.php" method="post">
                                            <input type="hidden" name="action" value="warn" />
                                            <input type="hidden" name="postid" value="<?= $PostID ?>" />
                                        </form>
                                        - <a href="#" onclick="$('#warn<?= $PostID ?>').raw().submit(); return false;" class="brackets"><?= Lang::get('forums.warn') ?></a>
                                    <? } ?>
                                <? } ?>
                            </div>
                    </td>
                </tr>
                <tr class="TableForumPost-cellContent Table-row">
                    <? if (Users::has_avatars_enabled()) { ?>
                        <td class="TableForumPost-cellAvatar Table-cell">
                            <?= Users::show_avatar($UserInfo['Avatar'], $AuthorID, $UserInfo['Username'], G::$LoggedUser['DisableAvatars']) ?>
                        </td>
                    <?      } ?>
                    <td class="TableForumPost-cellBody Table-cell">
                        <div class="TableForumPostBody" id="content<?= $PostID ?>">
                            <div class="TableForumPostBody-text HtmlText PostArticle">
                                <?= Text::full_format($Body) ?>
                            </div>
                            <div class="TableForumPostBody-actions">
                                <? if ($EditedUserID) { ?>
                                    <div class="TableForumPostBody-divider"></div>
                                    <span>
                                        <? if (check_perms('site_admin_forums')) { ?>
                                            <a href="#content<?= $PostID ?>" onclick="LoadEdit('<?= substr($Link, 0, strcspn($Link, '.')) ?>', <?= $PostID ?>, 1); return false;">&laquo;</a>
                                        <?          } ?>
                                        <?= Lang::get('forums.last_edited_by_before') ?><?= Users::format_username($EditedUserID, false, false, false) ?><?= Lang::get('forums.last_edited_by_after') ?> <?= time_diff($EditedTime, 2, true, true) ?>
                                    </span>
                                <?      } ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
<?  }
}
