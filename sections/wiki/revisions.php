<?
if (!isset($_GET['id']) || !is_number($_GET['id'])) {
    error(404);
}
$ArticleID = (int)$_GET['id'];

$Latest = Wiki::get_article($ArticleID);
list($Revision, $Title, $Body, $Read, $Edit, $Date, $AuthorID, $AuthorName) = array_shift($Latest);
if ($Read > $LoggedUser['EffectiveClass']) {
    error(404);
}
if ($Edit > $LoggedUser['EffectiveClass']) {
    error(403);
}

View::show_header(t('server.wiki.revision_history', ['Values' => [
    $Title
]]), '', 'PageWikiRevision');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav">
            <?= t('server.wiki.revision_history', ['Values' => [
                "<a href='wiki.php?action=article&amp;id=${ArticleID}'>${Title}</a>"
            ]]) ?>
        </h2>
    </div>
    <form class="BodyContent" action="wiki.php" method="get">
        <input type="hidden" name="action" id="action" value="compare" />
        <input type="hidden" name="id" id="id" value="<?= $ArticleID ?>" />
        <div>
            <button class="Button" type="submit" value="Compare"><?= t('server.wiki.compare') ?></button>
        </div>
        <div class="TableContainer">
            <table class="TableWikiRevision Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= t('server.wiki.history_revision') ?></td>
                    <td class="Table-cell"><?= t('server.wiki.history_title') ?></td>
                    <td class="Table-cell"><?= t('server.wiki.history_author') ?></td>
                    <td class="Table-cell"><?= t('server.wiki.history_age') ?></td>
                    <td class="Table-cell"><?= t('server.wiki.history_old') ?></td>
                    <td class="Table-cell"><?= t('server.wiki.history_new') ?></td>
                </tr>
                <tr class="Table-row">
                    <td class="Table-cell"><?= $Revision ?></td>
                    <td class="Table-cell"><?= $Title ?></td>
                    <td class="Table-cell"><?= Users::format_username($AuthorID, false, false, false) ?></td>
                    <td class="Table-cell"><?= time_diff($Date) ?></td>
                    <td class="Table-cell"><input type="radio" name="old" value="<?= $Revision ?>" disabled="disabled" /></td>
                    <td class="Table-cell"><input type="radio" name="new" value="<?= $Revision ?>" checked="checked" /></td>
                </tr>
                <?
                $DB->query("
	SELECT
		Revision,
		Title,
		Author,
		Date
	FROM wiki_revisions
	WHERE ID = '$ArticleID'
	ORDER BY Revision DESC");
                while (list($Revision, $Title, $AuthorID, $Date) = $DB->next_record()) { ?>
                    <tr class="Table-row">
                        <td class="Table-cell"><?= $Revision ?></td>
                        <td class="Table-cell"><?= $Title ?></td>
                        <td class="Table-cell"><?= Users::format_username($AuthorID, false, false, false) ?></td>
                        <td class="Table-cell"><?= time_diff($Date) ?></td>
                        <td class="Table-cell"><input type="radio" name="old" value="<?= $Revision ?>" /></td>
                        <td class="Table-cell"><input type="radio" name="new" value="<?= $Revision ?>" /></td>
                    </tr>
                <? } ?>
            </table>
        </div>

    </form>
</div>
<? View::show_footer(); ?>