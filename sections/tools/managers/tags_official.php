<?php
if (!check_perms('users_mod')) {
    error(403);
}

$tagMan = new \Gazelle\Manager\Tag;

$unofficialName = [];
$new = 0;
if (isset($_POST['doit'])) {
    authorize();

    $unofficalId   = [];
    if (isset($_POST['oldtags'])) {
        foreach ($_POST['oldtags'] as $tagId) {
            if (!$name = $tagMan->name($tagId)) {
                error(403);
            }
            $unofficialId[]   = $tagId;
            $unofficialName[] = $name;
        }
        $tagMan->unofficialize($unofficialId);
    }

    if ($_POST['newtag']) {
        $new = $tagMan->officialize($_POST['newtag'], $LoggedUser['ID']);
    }
    $Cache->delete_value('genre_tags');
}

View::show_header('Official Tags Manager');
?>
<div class="header">
    <div class="BodyNavLinks">
        <a href="tools.php?action=tags" class="brackets">Batch Tag Editor</a>
        <a href="tools.php?action=tags_aliases" class="brackets">Tag Aliases</a>
        <a href="tools.php?action=tags_official" class="brackets">Official Tags</a>
        <a href="tools.php" class="brackets">Back to toolbox</a>
    </div>
    <h2>Official Tags Manager</h2>
    <div class="BodyNavLinks">
        <a href="tools.php?action=tags_official&amp;order=name" class="brackets">Sort by name</a>
        <a href="tools.php?action=tags_official&amp;order=uses" class="brackets">Sort by uses</a>
    </div>
</div>
<?php if ($new) { ?>
    <div class="BoxBody center">
        <strong>Success:</strong> <?= htmlentities($_POST['newtag']) ?> officialized.
    </div>
<?php } elseif ($unofficialName) { ?>
    <div class="BoxBody center">
        <strong>Success:</strong> <?= htmlentities(implode(', ', $unofficialName)) ?> unofficialized.
    </div>
<?php } ?>
<div style="text-align: center;">
    <div style="display: inline-block;">
        <form class="manage_form" name="tags" method="post" action="">
            <input type="hidden" name="action" value="tags_official" />
            <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
            <input type="hidden" name="doit" value="1" />
            <table class="TableTag Table">
                <tr class="Table-rowHeader">
                    <?php
                    for ($col = 0; $col < TAG_OFFICIAL_COLUMNS; ++$col) {
                        if ($col) {
                    ?>
                            <td class="Table-cell">&nbsp;&nbsp;</td>
                        <?php } ?>
                        <td class="Table-cell" style="font-weight: bold; text-align: center;">Remove</td>
                        <td class="Table-cell" style="font-weight: bold;">Tag</td>
                        <td class="Table-cell" style="font-weight: bold;">Uses</td>
                    <?php } ?>
                </tr>
                <?php
                $list = $tagMan->listOfficial(TAG_OFFICIAL_COLUMNS, $_GET['order'] ?? 'name');
                $len = count($list[0]);
                for ($row = 0; $row < $len; ++$row) {
                ?>
                    <tr class="Table-row">
                        <?php
                        for ($col = 0; $col < TAG_OFFICIAL_COLUMNS; ++$col) {
                            if ($col) {
                        ?>
                                <td class="Table-cell">&nbsp;</td>
                            <?php
                            }
                            if (count($list[$col]) <= $row) {
                            ?>
                                <td class="Table-cell" colspan="3">&nbsp;</td>
                            <?php   } else { ?>
                                <td class="Table-cell" style="text-align: center;">
                                    <input type="checkbox" name="oldtags[]" value="<?= $list[$col][$row]['id'] ?>" />
                                </td>
                                <td class="Table-cell"><a href="torrents.php?action=advanced&taglist=<?= $list[$col][$row]['name'] ?>"><?= $list[$col][$row]['name'] ?></a></td>
                                <td class="Table-cell" style="text-align: right;"><?= number_format($list[$col][$row]['uses']) ?></td>
                        <?php   }
                        }
                        ?>
                    </tr>
                <?php } ?>
                <tr class="Table-row">
                    <td class="Table-cell" colspan="<?= TAG_OFFICIAL_COLUMNS * 4 - 1 ?>">
                        <label for="newtag">New official tag: </label><input class="Input" type="text" name="newtag" />
                    </td>
                </tr>
                <tr class="Table-row">
                    <td class="Table-cell" colspan="<?= TAG_OFFICIAL_COLUMNS * 4 - 1 ?>" style="text-align: center;">
                        <input class="Button" type="submit" value="Submit changes" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<?php

View::show_footer();
