<?
if (!isset($_GET['id']) || !is_number($_GET['id'])) {
    error(404);
}
$ArticleID = (int)$_GET['id'];

$Article = Wiki::get_article($ArticleID);
list($Revision, $Title, $Body, $Read, $Edit, $Date, $Author,,,, $Lang, $FatherID) = array_shift($Article);
if ($Edit > $LoggedUser['EffectiveClass']) {
    error('You do not have access to edit this article.');
}

View::show_header('Edit ' . $Title, '', 'PageWikiEdit');
?>
<div class="LayoutBody">
    <div class="Form-rowList" variant="header">
        <div class="Form-rowHeader">
            <div class="Form-title"><?= t('server.wiki.article_edit') ?></div>
        </div>
        <form class="edit_form" name="wiki_article" action="wiki.php" method="post">
            <input type="hidden" name="action" value="edit" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="id" value="<?= $ArticleID ?>" />
            <input type="hidden" name="revision" value="<?= $Revision ?>" />
            <div class="Post-body Box-body HtmlText">
                <h3><?= t('server.wiki.title') ?></h3>
                <input class="Input" type="text" name="title" size="92" maxlength="100" value="<?= $Title ?>" />
                <h3><?= t('server.wiki.body') ?></h3>
                <?
                $ReplyText = new TEXTAREA_PREVIEW('body', 'body', $Body, 91, 22, true, true);

                if (check_perms('admin_manage_wiki')) {
                ?>
                    <div class="Post-bodyActions" variant="alignLeft">
                        <strong><?= t('server.wiki.article_access') ?>:</strong>
                        <div><?= t('server.wiki.article_restrict_read') ?>:</div> <select class="Input" name="minclassread"><?= class_list($Read) ?></select>
                        <div><?= t('server.wiki.article_restrict_edit') ?>:</div> <select class="Input" name="minclassedit"><?= class_list($Edit) ?></select>
                        <div><?= t('server.wiki.article_access_detail') ?></div>
                    </div>
                <?  } ?>

                <div id="wiki_create_language_box">
                    <div class="Post-bodyActions" variant="alignLeft">
                        <strong><?= t('server.wiki.article_language') ?>:</strong>
                        <select class="Input" name="language" id="language">
                            <option class="Select-option" value="chs" <?= $Lang == "chs" ? " selected" : "" ?>><?= t('server.wiki.chinese') ?></option>
                            <option class="Select-option" value="en" <?= $Lang == "en" ? " selected" : "" ?>><?= t('server.wiki.english') ?></option>
                        </select>
                        <input placeholder="<?= t('server.wiki.article_chinese') ?>" class="Input" type="text" size="92" name="fatherLink" id="fatherLink" <?= $FatherID ? "value=\"" . CONFIG['SITE_URL'] . "/wiki.php?action=article&id=$FatherID\"" : "" ?>>
                    </div>
                </div>
                <div class="Post-bodyActions">
                    <?= t('server.wiki.article_language_detail') ?>
                </div>
                <div class="Post-bodyActions">
                    <input class="Button" type="submit" value="<?= t('server.global.submit') ?>" />
                </div>
            </div>
        </form>
    </div>
</div>
<?
View::show_footer();
