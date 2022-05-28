<?php
if (!(check_perms('users_mod') || check_perms('site_tag_aliases_read'))) {
    error(403);
}

$tagMan = new \Gazelle\Manager\Tag;
$action = null;
if (check_perms('users_mod')) {
    if (isset($_POST['newalias'])) {
        $action = 'addition';
        $result = $tagMan->createAlias($_POST['badtag'], $_POST['aliastag']);
    }
    if (isset($_POST['changealias']) || isset($_POST['delete'])) {
        $aliasId = (int)$_POST['aliasid'];
        if ($_POST['save']) {
            $action = 'modification';
            $result = $tagMan->modifyAlias($aliasId, $_POST['badtag'], $_POST['aliastag']);
        } elseif ($_POST['delete']) {
            $action = 'removal';
            $result = $tagMan->removeAlias($aliasId);
        }
    }
}

View::show_header('Tag Aliases');
?>
<div class="header">
    <div class="BodyNavLinks">
        <a href="tools.php?action=tags" class="brackets">Batch Tag Editor</a>
        <a href="tools.php?action=tags_aliases" class="brackets">Tag Aliases</a>
        <a href="tools.php?action=tags_official" class="brackets">Official Tags</a>
        <a href="tools.php" class="brackets">Back to toolbox</a>
    </div>
    <h2>Tag Aliases</h2>
    <div class="BodyNavLinks">
        <a href="tools.php?action=tags_aliases&amp;order=goodtags" class="brackets">Sort by good tags</a>
        <a href="tools.php?action=tags_aliases&amp;order=badtags" class="brackets">Sort by bad tags</a>
    </div>
</div>
<?php if (!is_null($action)) { ?>
    <div class="BoxBody center">
        Result: <?= $action ?> <strong><?= $result == 1 ? 'succeeded' : 'failed' ?></strong>.
    </div>
<?php } ?>
<table class="Table">
    <tr class="Table-rowHeader">
        <td class="Table-cell">Proper tag</td>
        <td class="Table-cell">Renamed from</td>
        <?php if (check_perms('users_mod')) { ?>
            <td class="Table-cell">Submit</td>
        <?php    } ?>
    </tr>
    <tr />
    <tr class="Table-row">
        <form class="add_form" name="aliases" method="post" action="">
            <input type="hidden" name="newalias" value="1" />
            <td class="Table-cell">
                <input class="Input" type="text" name="aliastag" />
            </td>
            <td class="Table-cell">
                <input class="Input" type="text" name="badtag" />
            </td>
            <?php if (check_perms('users_mod')) { ?>
                <td class="Table-cell">
                    <input class="Button" type="submit" value="Add alias" />
                </td>
            <?php    } ?>
        </form>
    </tr>
    <?= G::$Twig->render('tag/alias.twig', [
        'alias' => $tagMan->listAlias(($_GET['order'] ?? 'badtags') === 'badtags'),
        'is_mod' => check_perms('users_mod'),
    ]) ?>
</table>
<?php

View::show_footer();
