<?
View::show_header(t('server.wiki.create_an_article'), '', 'PageWikiCreate');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav">
            <?= t('server.common.wiki') ?>
        </div>
    </div>
    <form class="create_form" name="wiki_article" action="wiki.php" method="post">
        <input type="hidden" name="action" value="create" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <table class="Form-rowList Table" variant="header">
            <tr class="Form-rowHeader">
                <td class="Form-title"><?= t('server.wiki.article_create') ?></td>
            </tr>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.wiki.title') ?>:</td>
                <td class="Form-inputs">
                    <input class="Input" type="text" name="title" size="92" maxlength="100" />
                </td>
            </tr>
            <? /* if ($_GET['alias']) { ?>
                <input type="hidden" name="alias" value="<?=display_str(alias($_GET['alias']))?>" />
<? } else { ?>
                <h3>Alias</h3>
                <p>An exact search string or name that should lead to this article. (More can be added later)</p>
                <input class="Input" type="text" name="alias" size="50" maxlength="50" />
<? } */ ?>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.wiki.body') ?>:</td>
                <td class="Form-items">
                    <?
                    new TEXTAREA_PREVIEW('body', 'body', '', 91, 22, true, true);
                    ?>
                </td>
            </tr>
            <?

            if (check_perms('admin_manage_wiki')) { ?>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.wiki.article_access') ?>:
                    </td>
                    <td class="Form-inputs">
                        <div><?= t('server.wiki.article_restrict_read') ?></div> <select class="Input" name="minclassread"><?= class_list() ?></select>
                        <div><?= t('server.wiki.article_restrict_edit') ?></div> <select class="Input" name="minclassedit"><?= class_list() ?></select>
                        <div><?= t('server.wiki.article_access_detail') ?></div>
                    </td>
                </tr>
            <?  } ?>
            <tr class="Form-row">
                <td class="Form-label"><?= t('server.wiki.article_language') ?>:</td>
                <td class="Form-inputs">
                    <?= t('server.wiki.article_language_detail') ?>
                    <select class="Input" name="language" id="language">
                        <option class="Select-option" value="chs" selected="selected"><?= t('server.wiki.chinese') ?></option>
                        <option class="Select-option" value="en"><?= t('server.wiki.english') ?></option>
                    </select>
                    <div>
                        <input placeholder="<?= t('server.wiki.article_chinese') ?>" class="Input" type="text" size="92" name="fatherLink" id="fatherLink">
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
