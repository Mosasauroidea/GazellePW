<?
if (!check_perms('users_mod')) {
    error(403);
}

if ($_POST['submit'] == 'modify') {
    $SubName = $_POST['subname'];
    $TagID = $_POST['tagid'];
    $Type = $_POST['type'];
    if (empty($TagID)) {
        error(0);
    }
    authorize();
    if (empty($SubName) || empty($Type)) {
        error(0);
    }
    $SubName = db_string($SubName);
    $DB->query(
        "UPDATE tags SET SubName = '$SubName', TagType='$Type' 
			WHERE ID = $TagID"
    );
    $Cache->delete_value('genre_tags');
    Tags::clear_all_cache();
}

if (isset($_POST['newalias'])) {
    $badtag = $_POST['badtag'];
    $aliastag = $_POST['aliastag'];

    $DB->prepared_query("
			INSERT INTO tag_aliases (BadTag, AliasTag)
			VALUES (?, ?)", $badtag, $aliastag);
}

if (isset($_POST['changealias']) && is_number($_POST['aliasid'])) {
    $aliasid = $_POST['aliasid'];
    $badtag = $_POST['badtag'];
    $aliastag = $_POST['aliastag'];

    if ($_POST['save']) {
        $DB->prepared_query("
				UPDATE tag_aliases
				SET BadTag = ?, AliasTag = ?
				WHERE ID = ?", $badtag, $aliastag, $aliasid);
    }
    if ($_POST['delete']) {
        $DB->prepared_query("
				DELETE FROM tag_aliases
				WHERE ID = ?", $aliasid);
    }
}


$DB->query("
	SELECT ID, Name, SubName, Uses, TagType
	FROM tags
	ORDER BY TagType ASC, Uses DESC");
$TagCount = $DB->record_count();
$Tags = $DB->to_array();
View::show_header(t('server.tools.tags_manager'));
$orderby = ($_GET['order'] === 'badtags' ? 'BadTag' : 'AliasTag');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.tools.tags_manager') ?></h2>
    </div>
    <div class="BodyContent">
        <div class="BodyNavLinks">
            <a href="tools.php?action=manage_tags&amp;order=goodtags" class="brackets"><?= t('server.tools.sort_by_good_tags') ?></a>
            <a href="tools.php?action=manage_tags&amp;order=badtags" class="brackets"><?= t('server.tools.sort_by_bad_tags') ?></a>
        </div>
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.tools.tag_aliases') ?>
                </div>
            </div>
            <div class="Group-body">
                <table class="TableTagAlias Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.proper_tag') ?></td>
                        <td class="Table-cell"><?= t('server.tools.renamed_from') ?></td>
                        <? if (check_perms('users_mod')) { ?>
                            <td class="Table-cell"><?= t('server.tools.operations') ?></td>
                        <?  } ?>
                    </tr>
                    <!-- <tr /> -->
                    <tr class="Table-row">
                        <form class="add_form" name="aliases" method="post" action="">
                            <input type="hidden" name="newalias" value="1" />
                            <td class="Table-cell">
                                <input class="Input" type="text" name="aliastag" />
                            </td>
                            <td class="Table-cell">
                                <input class="Input" type="text" name="badtag" />
                            </td>
                            <? if (check_perms('users_mod')) { ?>
                                <td class="Table-cell">
                                    <input class="Button" type="submit" value="Add alias" />
                                </td>
                            <?  } ?>
                        </form>
                    </tr>
                    <?
                    $DB->prepared_query("
	SELECT ID, BadTag, AliasTag
	FROM tag_aliases
	ORDER BY $orderby");
                    while (list($ID, $BadTag, $AliasTag) = $DB->next_record()) {
                    ?>
                        <tr>
                            <form class="manage_form" name="aliases" method="post" action="">
                                <input type="hidden" name="changealias" value="1" />
                                <input type="hidden" name="aliasid" value="<?= $ID ?>" />
                                <td class="Table-cell">
                                    <input class="Input" type="text" name="aliastag" value="<?= $AliasTag ?>" />
                                </td>
                                <td class="Table-cell">
                                    <input class="Input" type="text" name="badtag" value="<?= $BadTag ?>" />
                                </td>
                                <? if (check_perms('users_mod')) { ?>
                                    <td class="Table-cell">
                                        <input class="Button" type="submit" name="save" value="Save alias" />
                                        <input class="Button" type="submit" name="delete" value="Delete alias" />
                                    </td>
                                <?  } ?>
                            </form>
                        </tr>
                    <?
                    } ?>
                </table>
            </div>
        </div>
        <div class="Group">
            <div class="Group-header">
                <div class="Group-headerTitle">
                    <?= t('server.tools.h2_official_tags_manager') ?>
                </div>
            </div>
            <div class="Group-body">
                <table class="Table">
                    <tr class="Table-rowHeader">
                        <td class="Table-cell"><?= t('server.tools.tag') ?></td>
                        <td class="Table-cell"><?= t('server.tools.sub_tag') ?></td>
                        <td class="Table-cell"><?= t('server.tools.uses') ?></td>
                        <td class="Table-cell"><?= t('server.tools.tag_type') ?></td>
                        <td class="Table-cell Table-cellRight"><?= t('server.common.actions') ?></td>
                        <?

                        for ($i = 0; $i < $TagCount; $i++) {
                            list($TagID1, $TagName1, $TagSubName1, $TagUses1, $TagType1) = $Tags[$i];
                        ?>
                    <tr class="Table-row">
                        <form class="manage_form" name="tags" method="post" action="">
                            <input type="hidden" name="action" value="manage_tags" />
                            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                            <td class="Table-cell"><?= $TagName1 ?></td>
                            <td class="Table-cell">
                                <input class="Input is-small" type="text" value="<?= $TagSubName1 ?>" name="subname" />
                                <input type="hidden" value="<?= $TagID1 ?>" name="tagid" />
                            </td>
                            <td class="Table-cell"><?= number_format($TagUses1) ?></td>
                            <td class="Table-cell">
                                <select class="Input" name="type">
                                    <option value="genre" <?= $TagType1 == 'genre' ? "selected='selected'" : '' ?>><?= t('server.tools.official_tag') ?></option>
                                    <option value="other" <?= $TagType1 == 'other' ? "selected='selected'" : '' ?>><?= t('server.tools.other_tag') ?></option>
                                </select>
                            </td>
                            <td class="Table-cell Table-cellRight">
                                <button class="Button" type="submit" name="submit" value="modify"><?= t('server.common.modify') ?></button>
                            </td>
                        </form>
                    </tr>
                <?
                        }
                ?>
                </table>
            </div>
        </div>
        <form action="tools.php" method="get" name="tagform" id="tagform" onsubmit="return formVal();">
            <input type="hidden" name="action" value="edit_tags" />

            <table class="Table Form-rowList">
                <tr class="Form-rowHeader">
                    <td>
                        <?= t('server.tools.merge_rename_tags') ?>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.tools.tag') ?>:
                    </td>
                    <td class="Form-inputs">
                        <input class="Input is-small" type="text" name="tag" id="tag" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                        <?= t('server.tools.rename_to_merge_with_tag') ?>:
                    </td>
                    <td class="Form-inputs">
                        <input class="Input is-small" type="text" name="replace" id="replace" />
                    </td>
                </tr>
                <tr class="Form-row">
                    <td class="Form-label">
                    </td>
                    <td class="Form-inputs">
                        <input type="checkbox" name="list" id="list" checked="checked" /> <label for="list"><?= t('server.tools.list_affected_rows') ?></label>
                    </td>
                </tr>
                <tr class="Form-row">
                    <td>
                        <input class="Button" type="submit" value="Rename/Merge Tags" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<? View::show_footer(); ?>