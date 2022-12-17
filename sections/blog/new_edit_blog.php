<?
if (!check_perms('admin_manage_blog')) {
    error(403);
}
View::show_header(t('server.blog.create_a_blog_post'), 'bbcode', 'PageBlogHome');
$IsNew = empty($_GET['id']);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.index.blog_note') ?>
        </div>
    </div>
    <?
    $BlogID = 0;
    $Title = '';
    $Body = '';
    $ThreadID = null;
    if (!$IsNew) {
        $BlogID = intval($_GET['id']);
        $DB->prepared_query("
		SELECT Title, Body, ThreadID
		FROM blog
		WHERE ID = ?", $BlogID);
        list($Title, $Body, $ThreadID) = $DB->fetch_record(0, 1);
        $ThreadID = $ThreadID ?? 0;
    }
    ?>

    <form class="Form BlogCreate <?= $IsNew ? 'create_form' : 'edit_form' ?>" name="blog_post" action="blog.php" method="post">
        <input type="hidden" name="action" value="<?= $IsNew ? 'takenewblog' : 'takeeditblog' ?>" />
        <input type="hidden" name="auth" value="<?= G::$LoggedUser['AuthKey'] ?>" />
        <? if (!$IsNew) { ?>
            <input type="hidden" name="blogid" value="<?= $BlogID; ?>" />
        <? } ?>
        <div class="Form-rowList" variant="header" id="blog_create_edit_box">
            <div class="Form-rowHeader">
                <div class="Form-title">
                    <?= $IsNew ? t('server.blog.create_a_blog_post') : t('server.blog.edit_blog_post') ?>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.blog.title') ?></div>
                <div class="Form-inputs">
                    <input class="Input" type="text" name="title" size="95" <?= !empty($Title) ? ' value="' . display_str($Title) . '"' : ''; ?> />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.blog.body') ?></div>
                <div class="Form-items">
                    <?php new TEXTAREA_PREVIEW('body', 'blog_content', display_str($Body), 60, 8, true, true, false); ?>
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label"><?= t('server.blog.thread_id') ?></div>
                <div class="Form-inputs">
                    <input class="Input is-small" type="text" name="thread" size="8" <?= $ThreadID !== null ? ' value="' . display_str($ThreadID) . '"' : ''; ?> /><?= t('server.blog.thread_id_note') ?>
                </div>
            </div>
            <div class="Form-row">
                <div>
                    <input type="checkbox" value="1" name="important" id="important" checked="checked" /><label for="important"><?= t('server.blog.important') ?></label>
                </div>
                <div>
                    <input id="subscribebox" type="checkbox" name="subscribe" <?= !empty($HeavyInfo['AutoSubscribe']) ? ' checked="checked"' : ''; ?> tabindex="2" />
                    <label for="subscribebox"><?= t('server.common.subscribe') ?></label>
                </div>
                <input class="Button" type="submit" value="<?= $IsNew ? t('server.blog.create_a_blog_post') : t('server.blog.edit_blog_post'); ?>" />
            </div>
        </div>
    </form>
</div>
<?php
View::show_footer();
