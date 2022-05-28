<?
if (!check_perms('users_mod')) {
    error(403);
}

if (isset($_POST['doit'])) {
    authorize();

    if (isset($_POST['oldtags'])) {
        $OldTagIDs = $_POST['oldtags'];
        foreach ($OldTagIDs as $OldTagID) {
            if (!is_number($OldTagID)) {
                error(403);
            }
        }
        $OldTagIDs = implode(', ', $OldTagIDs);

        $DB->query("
			UPDATE tags
			SET TagType = 'other'
			WHERE ID IN ($OldTagIDs)");
    }

    if ($_POST['newtag']) {
        $TagName = Misc::sanitize_tag($_POST['newtag']);

        $DB->query("
			SELECT ID
			FROM tags
			WHERE Name LIKE '$TagName'");
        list($TagID) = $DB->next_record();

        if ($TagID) {
            $DB->query("
				UPDATE tags
				SET TagType = 'genre'
				WHERE ID = $TagID");
        } else { // Tag doesn't exist yet - create tag
            $DB->query("
				INSERT INTO tags
					(Name, UserID, TagType, Uses)
				VALUES
					('$TagName', " . $LoggedUser['ID'] . ", 'genre', 0)");
            $TagID = $DB->inserted_id();
        }
    }

    $Cache->delete_value('genre_tags');
}

View::show_header(Lang::get('tools', 'h2_official_tags_manager'));
?>
<div class="BodyHeader">
    <h2 class="BodyHeader-nav"><?= Lang::get('tools', 'h2_official_tags_manager') ?></h2>
</div>
<div style="text-align: center;">
    <div style="display: inline-block;">
        <form class="manage_form" name="tags" method="post" action="">
            <input type="hidden" name="action" value="official_tags" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="doit" value="1" />
            <table class="TableTags">
                <tr class="Table-rowHeader">
                    <td class="Table-cell Tabel-cellCenter"><?= Lang::get('tools', 'remove') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools', 'tag') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools', 'uses') ?></td>
                    <td class="Table-cell">&nbsp;&nbsp;&nbsp;</td>
                    <td class="Table-cell Table-cellCenter"><?= Lang::get('tools', 'remove') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools', 'tag') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools', 'uses') ?></td>
                    <td class="Table-cell">&nbsp;&nbsp;&nbsp;</td>
                    <td class="Table-cell Table-cellCenter"><?= Lang::get('tools', 'remove') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools', 'tag') ?></td>
                    <td class="Table-cell"><?= Lang::get('tools', 'uses') ?></td>
                </tr>
                <?
                $i = 0;
                $DB->query("
	SELECT ID, Name, Uses
	FROM tags
	WHERE TagType = 'genre'
	ORDER BY Name ASC");
                $TagCount = $DB->record_count();
                $Tags = $DB->to_array();
                for ($i = 0; $i < $TagCount / 3; $i++) {
                    list($TagID1, $TagName1, $TagUses1) = $Tags[$i];
                    list($TagID2, $TagName2, $TagUses2) = $Tags[ceil($TagCount / 3) + $i];
                    list($TagID3, $TagName3, $TagUses3) = $Tags[2 * ceil($TagCount / 3) + $i];
                ?>
                    <tr class="Table-row">
                        <td class="Table-cell Table-cellCenter"><input type="checkbox" name="oldtags[]" value="<?= $TagID1 ?>" /></td>
                        <td class="Table-cell"><?= $TagName1 ?></td>
                        <td class="Table-cell Table-cellCenter"><?= number_format($TagUses1) ?></td>
                        <td class="Table-cell">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td class="Table-cell Table-cellCenter">
                            <? if ($TagID2) { ?>
                                <input type="checkbox" name="oldtags[]" value="<?= $TagID2 ?>" />
                            <?      } ?>
                        </td>
                        <td class="Table-cell"><?= $TagName2 ?></td>
                        <td class="Table-cell Table-cellCenter"><?= number_format($TagUses2) ?></td>
                        <td class="Table-cell">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td class="Table-cell Table-cellCenter">
                            <? if ($TagID3) { ?>
                                <input type="checkbox" name="oldtags[]" value="<?= $TagID3 ?>" />
                            <?      } ?>
                        </td>
                        <td class="Table-cell"><?= $TagName3 ?></td>
                        <td class="Table-cell Table-cellCenter"><?= number_format($TagUses3) ?></td>
                    </tr>
                <?
                }
                ?>
                <tr class="Table-row">
                    <td class="Table-cell" colspan="11">
                        <label for="newtag"><?= Lang::get('tools', 'new_official_tag') ?>: </label><input class="Input" type="text" name="newtag" />
                    </td>
                </tr>
                <tr class="Table-row">
                    <td class="Table-cell" colspan="11" style="text-align: center;">
                        <input class="Button" type="submit" value="Submit changes" />
                    </td>
                </tr>

            </table>
        </form>
    </div>
</div>
<? View::show_footer(); ?>