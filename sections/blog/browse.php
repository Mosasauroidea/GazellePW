<?php
View::show_header('Blog', 'bbcode', 'PageBlogHome');
?>
<div class="LayoutBody">
    <?
    if (check_perms('admin_manage_blog')) {
        $BlogID = 0;
        $Title = '';
        $Body = '';
        $ThreadID = null;
        if (!empty($_GET['action']) && $_GET['action'] === 'editblog' && !empty($_GET['id'])) {
            $BlogID = intval($_GET['id']);
            $DB->prepared_query("
            SELECT Title, Body, ThreadID
            FROM blog
            WHERE ID = ?", $BlogID);
            list($Title, $Body, $ThreadID) = $DB->fetch_record(0, 1);
            $ThreadID = $ThreadID ?? 0;
        }
    ?>
        <div class="Form-rowList" variant="header" id="blog_create_edit_box">
            <div class="Form-rowHeader">
                <div style="width:100%" class="Form-title"><?= empty($_GET['action']) ? t('server.blog.create_a_blog_post') : t('server.blog.edit_blog_post') ?>
                    <span class="floatright">
                        <a class="Link" href="#" onclick="globalapp.toggleAny(event, '.BlogCreate', { updateText: true })">
                            <span class="u-toggleAny-show u-hidden"><?= t('server.global.show') ?></span>
                            <span class="u-toggleAny-hide"><?= t('server.global.hide') ?></span>
                        </a>
                    </span>
                </div>
            </div>
            <form class="BlogCreate <?= empty($_GET['action']) ? 'create_form' : 'edit_form' ?>" name="blog_post" action="blog.php" method="post">
                <div class="Post-body Box-body HtmlText">
                    <input type="hidden" name="action" value="<?= empty($_GET['action']) ? 'takenewblog' : 'takeeditblog' ?>" />
                    <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
                    <?php if (!empty($_GET['action']) && $_GET['action'] == 'editblog') { ?>
                        <input type="hidden" name="blogid" value="<?= $BlogID; ?>" />
                    <?php } ?>
                    <div>
                        <h3><?= t('server.blog.title') ?></h3>
                        <input class="Input" type="text" name="title" size="95" <?= !empty($Title) ? ' value="' . display_str($Title) . '"' : ''; ?> /><br />
                    </div>
                    <div>
                        <h3><?= t('server.blog.body') ?></h3>
                        <div>
                            <?php new TEXTAREA_PREVIEW('body', 'blog_content', display_str($Body), 60, 8, true, true, false); ?>
                        </div>
                    </div>
                    <div class="Post-bodyActions" variant="alignLeft">
                        <div>
                            <input type="checkbox" value="1" name="important" id="important" checked="checked" /><label for="important"><?= t('server.blog.important') ?></label>
                        </div>

                        <div>
                            <input id="subscribebox" type="checkbox" name="subscribe" <?= !empty($HeavyInfo['AutoSubscribe']) ? ' checked="checked"' : ''; ?> tabindex="2" />

                            <label for="subscribebox"><?= t('server.global.subscribe') ?></label>
                        </div>
                        <div>
                            <span><?= t('server.blog.thread_id') ?></span>
                            <input class="Input is-small" type="text" name="thread" size="8" <?= $ThreadID !== null ? ' value="' . display_str($ThreadID) . '"' : ''; ?> /><?= t('server.blog.thread_id_note') ?>
                        </div>
                    </div>

                    <div class="Post-bodyActions">
                        <input class="Button" type="submit" value="<?= !isset($_GET['action']) ? t('server.blog.create_a_blog_post') : t('server.blog.edit_blog_post'); ?>" />
                    </div>
                </div>
            </form>
        </div>
    <?php
    }

    if (!isset($_GET['action']) || $_GET['action'] !== 'editblog') {
    ?>
        <?php
        if (!$Blog = $Cache->get_value('blog')) {
            $DB->prepared_query("
		SELECT
			b.ID,
			um.Username,
			b.UserID,
			b.Title,
			b.Body,
			b.Time,
			b.ThreadID
		FROM blog AS b
			LEFT JOIN users_main AS um ON b.UserID = um.ID
		ORDER BY Time DESC
		LIMIT 20");
            $Blog = $DB->to_array();
            $Cache->cache_value('blog', $Blog, 1209600);
        }

        if (count($Blog) > 0 && G::$LoggedUser['LastReadBlog'] < $Blog[0][0]) {
            $Cache->begin_transaction('user_info_heavy_' . G::$LoggedUser['ID']);
            $Cache->update_row(false, array('LastReadBlog' => $Blog[0][0]));
            $Cache->commit_transaction(0);
            $DB->prepared_query("
		UPDATE users_info
		SET LastReadBlog = ?
		WHERE UserID = ?", $Blog[0][0], G::$LoggedUser['ID']);
            G::$LoggedUser['LastReadBlog'] = $Blog[0][0];
        }
        ?>

        <div class="PostList PostListBlog">
            <?
            foreach ($Blog as $BlogItem) {
                list($BlogID, $Author, $AuthorID, $Title, $Body, $BlogTime, $ThreadID) = $BlogItem;
            ?>
                <div class="Post Box" id="blog<?= $BlogID ?>">
                    <div class="Post-header Box-header">
                        <div class="Post-headerLeft">
                            <span class="Post-headerTitle"><?= $Title ?></span> - <?= t('server.blog.posted') ?>
                            <?= time_diff($BlogTime); ?> <?= t('server.blog.by') ?>
                            <a href="user.php?id=<?= $AuthorID ?>"><?= $Author ?></a>
                        </div>
                        <div class="Post-headerActions">
                            <?php if (check_perms('admin_manage_blog')) { ?>
                                <a href=" blog.php?action=editblog&amp;id=<?= $BlogID ?>" class="brackets"><?= t('server.global.edit') ?></a>
                                <a href="blog.php?action=deleteblog&amp;id=<?= $BlogID ?>&amp;auth=<?= G::$LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.global.delete') ?></a>
                            <?php    } ?>
                            <?php if ($ThreadID) { ?>
                                <a href="forums.php?action=viewthread&amp;threadid=<?= $ThreadID ?>"><?= t('server.index.discuss') ?></a>
                                <?php
                                if (check_perms('admin_manage_blog')) { ?>
                                    <a href="blog.php?action=deadthread&amp;id=<?= $BlogID ?>&amp;auth=<?= G::$LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.blog.remove_link') ?></a>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="Post-body Box-body HtmlText PostArticle">
                        <?= Text::full_format($Body) ?>
                    </div>
                </div>
            <? } ?>
        </div>
    <? } ?>
</div>
<?php
View::show_footer();
?>