<?php
View::show_header(t('server.index.blog_note'), 'bbcode', 'PageBlogHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.index.blog_note') ?>
        </div>
        <div class="BodyNavLinks">
            <? if (check_perms('admin_manage_blog')) { ?>
                <a href="blog.php?action=neweditblog"><?= t('server.blog.create_a_blog_post') ?></a>
            <?  } ?>
        </div>
    </div>
    <?
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

    <div class="LayoutBody PostList PostListBlog">
        <?
        foreach ($Blog as $BlogItem) {
            list($BlogID, $Author, $AuthorID, $Title, $Body, $BlogTime, $ThreadID) = $BlogItem;
        ?>
            <div class="Post" id="blog<?= $BlogID ?>">
                <div class="Post-header">
                    <div class="Post-headerLeft">
                        <span class="Post-headerTitle"><?= $Title ?></span>
                        - <?= time_diff($BlogTime); ?>
                    </div>
                    <div class="Post-headerActions">
                        <?php if ($ThreadID) { ?>
                            <a href="forums.php?action=viewthread&amp;threadid=<?= $ThreadID ?>"><?= t('server.index.discuss') ?></a>
                        <? } ?>
                        <? if (check_perms('admin_manage_blog')) { ?>
                            - <a href="blog.php?action=neweditblog&amp;id=<?= $BlogID ?>" class="brackets"><?= t('server.common.edit') ?></a>
                            - <a href="blog.php?action=deleteblog&amp;id=<?= $BlogID ?>&amp;auth=<?= G::$LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.common.delete') ?></a>
                            - <a href="blog.php?action=deadthread&amp;id=<?= $BlogID ?>&amp;auth=<?= G::$LoggedUser['AuthKey'] ?>" class="brackets"><?= t('server.blog.remove_link') ?></a>
                        <?    } else { ?>
                            -
                        <? } ?>
                    </div>
                </div>
                <div class="Post-body Box-body HtmlText PostArticle">
                    <?= Text::full_format($Body) ?>
                </div>
            </div>
        <? } ?>
    </div>
</div>
<?php
View::show_footer();
?>