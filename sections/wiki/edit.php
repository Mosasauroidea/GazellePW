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

View::show_header(t('server.wiki.article_edit') . ' ' . $Title, '', 'PageWikiEdit');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.common.wiki') ?>
        </div>
        <div class="BodyHeader-subNav">
            <a href="wiki.php?id=<?= $ArticleID ?>"><?= $Title ?></a>
        </div>
    </div>
    <div class="Form-rowList" variant="header">
        <form class="edit_form" name="wiki_article" action="wiki.php" method="post">
            <input type="hidden" name="action" value="edit" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="id" value="<?= $ArticleID ?>" />
            <input type="hidden" name="revision" value="<?= $Revision ?>" />
            <table class="Form-rowList">
                <tr class="Form-rowHeader">
                    <td class="Form-title"><?= t('server.wiki.article_edit') ?></td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.wiki.title') ?>:
                    <td>
                    <td class="Form-inputs"><input class="Input" type="text" name="title" size="92" maxlength="100" value="<?= $Title ?>" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.wiki.body') ?>:</td>
                    <td class="Form-items">
                        <?
                        new TEXTAREA_PREVIEW('body', 'body', $Body, 91, 22, true, true);
                        ?>
                    </td>
                </tr>
                <?

                if (check_perms('admin_manage_wiki')) {
                ?>
                    <tr class="Form-row">
                        <td class="Form-label">
                            <strong><?= t('server.wiki.article_access') ?>:</strong>
                        </td>
                        <td class="Form-inputs">
                            <div><?= t('server.wiki.article_restrict_read') ?>:</div> <select class="Input" name="minclassread"><?= class_list($Read) ?></select>
                            <div><?= t('server.wiki.article_restrict_edit') ?>:</div> <select class="Input" name="minclassedit"><?= class_list($Edit) ?></select>
                            <div><?= t('server.wiki.article_access_detail') ?></div>
                        </td>
                    </tr>
                <?  } ?>

                <tr class="Form-row">
                    <td class="Form-label"><?= t('server.wiki.article_language') ?>:</td>
                    <td class="Form-inputs">
                        <?= t('server.wiki.article_language_detail') ?>
                        <select class="Input" name="language" id="language">
                            <option class="Select-option" value="chs" <?= $Lang == "chs" ? " selected" : "" ?>><?= t('server.wiki.chinese') ?></option>
                            <option class="Select-option" value="en" <?= $Lang == "en" ? " selected" : "" ?>><?= t('server.wiki.english') ?></option>
                        </select>
                        <div>
                            <input placeholder="<?= t('server.wiki.article_chinese') ?>" class="Input" type="text" size="92" name="fatherLink" id="fatherLink" <?= $FatherID ? "value=\"" . CONFIG['SITE_URL'] . "/wiki.php?action=article&id=$FatherID\"" : "" ?>>
                        </div>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td>
                        <input class="Button" type="submit" value="<?= t('server.common.submit') ?>" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<?
View::show_footer();
