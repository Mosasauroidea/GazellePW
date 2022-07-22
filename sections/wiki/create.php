<?
View::show_header(Lang::get('wiki.create_an_article'), '', 'PageWikiCreate');
?>
<div class="LayoutBody">
    <div class="Form-rowList" variant="header">
        <div class="Form-rowHeader">
            <div class="Form-title"><?= Lang::get('wiki.article_create') ?></div>
        </div>
        <form class="create_form" name="wiki_article" action="wiki.php" method="post">
            <input type="hidden" name="action" value="create" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <div class="Post-body Box-body HtmlText">
                <h3><?= Lang::get('wiki.title') ?></h3>
                <input class="Input" type="text" name="title" size="92" maxlength="100" />
                <? /* if ($_GET['alias']) { ?>
                <input type="hidden" name="alias" value="<?=display_str(alias($_GET['alias']))?>" />
<? } else { ?>
                <h3>Alias</h3>
                <p>An exact search string or name that should lead to this article. (More can be added later)</p>
                <input class="Input" type="text" name="alias" size="50" maxlength="50" />
<? } */ ?>
                <h3><?= Lang::get('wiki.body') ?></h3>
                <?
                $ReplyText = new TEXTAREA_PREVIEW('body', 'body', '', 91, 22, true, true);

                if (check_perms('admin_manage_wiki')) { ?>
                    <div class="Post-bodyActions" variant="alignLeft">
                        <strong><?= Lang::get('wiki.article_access') ?>:</strong>
                        <div><?= Lang::get('wiki.article_restrict_read') ?></div> <select class="Input" name="minclassread"><?= class_list() ?></select>
                        <div><?= Lang::get('wiki.article_restrict_edit') ?></div> <select class="Input" name="minclassedit"><?= class_list() ?></select>
                        <div><?= Lang::get('wiki.article_access_detail') ?></div>
                    </div>
                <?  } ?>
                <div id="wiki_create_language_box">
                    <div class="Post-bodyActions" variant="alignLeft">
                        <strong><?= Lang::get('wiki.article_language') ?>:</strong>
                        <select class="Input" name="language" id="language">
                            <option class="Select-option" value="chs" selected="selected"><?= Lang::get('wiki.chinese') ?></option>
                            <option class="Select-option" value="en"><?= Lang::get('wiki.english') ?></option>
                            <input placeholder="<?= Lang::get('wiki.article_chinese') ?>" class="Input" type="text" size="92" name="fatherLink" id="fatherLink">
                        </select>
                    </div>
                </div>
                <div class="Post-bodyActions">
                    <?= Lang::get('wiki.article_language_detail') ?>
                </div>
                <div class="Post-bodyActions">
                    <input class="Button" type="submit" value="<?= Lang::get('global.submit') ?>" />
                </div>
            </div>
        </form>
    </div>
</div>
<?
View::show_footer();
