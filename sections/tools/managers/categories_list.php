<?php

if (!check_perms('admin_manage_forums')) {
    error(403);
}

View::show_header(t('server.tools.forum_management'));
$DB->prepared_query('
	SELECT ID, Name, Sort, IFNULL(f.Count, 0) as Count
	FROM forums_categories as fc
	LEFT JOIN (SELECT CategoryID, COUNT(*) AS Count FROM forums GROUP BY CategoryID) AS f ON f.CategoryID = fc.ID
	ORDER BY Sort');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.tools.forum_management') ?></div>
        <div class="BodyHeader-subNav"><?= t('server.tools.forum_category_control_panel') ?></div>
        <div class="BodyNavLinks">
            <a href="tools.php?action=forum" class="brackets"><?= t('server.tools.forum_manager') ?></a>
        </div>
    </div>
    <table class="Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.tools.sort') ?></td>
            <td class="Table-cell"><?= t('server.tools.name') ?></td>
            <td class="Table-cell"><?= t('server.tools.forum_category_control_panel_forums') ?></td>
            <td class="Table-cell"><?= t('server.tools.operation') ?></td>
        </tr>
        <?
        $Row = 'b';
        while (list($ID, $Name, $Sort, $Count) = $DB->fetch_record()) {
        ?>
            <tr class="Table-row">
                <form class="manage_form" name="forums" action="" method="post">
                    <input type="hidden" name="id" value="<?= $ID ?>" />
                    <input type="hidden" name="action" value="categories_alter" />
                    <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                    <td class="Table-cell">
                        <input class="Input" type="text" size="3" name="sort" value="<?= $Sort ?>" />
                    </td>
                    <td class="Table-cell">
                        <input class="Input" type="text" size="100" name="name" value="<?= $Name ?>" />
                    </td>
                    <td class="Table-cell">
                        <?= $Count ?>
                    </td>
                    <td class="Table-cell">
                        <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?></button>
                        <?php if ($Count === 0) { ?>
                            <button class="Button" type="submit" name="submit" value="Delete" onclick="return confirm('<?= t('client.common.are_you_sure_cannot_undone') ?>')"><?= t('server.common.delete') ?></button>
                        <?php } ?>
                    </td>

                </form>
            </tr>
        <?
        }
        ?>
    </table>

    <form class="create_form" name="forum" action="" method="post">
        <input type="hidden" name="action" value="categories_alter" />
        <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
        <div class="Form-rowList" variant="header">
            <div class="Form-rowHeader">
                <?= t('server.tools.create_category') ?>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.sort') ?>
                </div>
                <div class="Form-inputs">
                    <input class="Input is-small" type="text" size="3" name="sort" />
                </div>
            </div>
            <div class="Form-row">
                <div class="Form-label">
                    <?= t('server.tools.name') ?>
                </div>
                <div class="Form-inputs">
                    <input class="Input" type="text" size="100" name="name" />
                </div>
            </div>
            <div class="Form-row">
                <button class="Button" type="submit" value="Create"><?= t('server.common.new') ?></button>
            </div>
        </div>
    </form>
</div>
<? View::show_footer(); ?>