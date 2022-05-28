<?
$Title = Lang::get('wiki', 'browse_wiki_articles');
if (!empty($_GET['letter'])) {
    $Letter = strtoupper(substr($_GET['letter'], 0, 1));
    if ($Letter !== '1') {
        $Title .= ' (' . $Letter . ')';
    }
}

View::show_header($Title, '', 'PageWikiBrowse');

$sql = "
	SELECT
		SQL_CALC_FOUND_ROWS
		ID,
		Title,
		Date,
		Author
	FROM wiki_articles
	WHERE MinClassRead <= '" . $LoggedUser['EffectiveClass'] . "'";
if (isset($Letter) && $Letter !== '1') {
    $sql .= " AND LEFT(Title,1) = '" . db_string($Letter) . "'";
} else {
    $Letter = 'All';
}
$sql .= " ORDER BY Title";

$DB->query($sql);

?>
<div class="LayoutBody">
    <? if (isset($Letter)) { ?>
        <div class="BodyHeader">
            <h2 class="BodyHeader-nav"><?= $Title ?></h2>
        </div>
        <div class="BoxBody center" id="alpha_beta_switcher">
            <p><?= Lang::get('wiki', 'article_note1') ?></p>
            <form class="FormOneLine SearchWiki" name="wiki" action="wiki.php" method="get">
                <input type="hidden" name="action" value="search" />
                <input type="hidden" name="nojump" value="1" />
                <input class="Input" type="text" name="search" size="80" />
                <input value="Search" type="submit" class="hidden" />
            </form>
            <!-- <br /> -->
            <p><?= Lang::get('wiki', 'article_note2') ?></p>
            <span>
                <a href="wiki.php?action=browse&amp;letter=a">A</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=b">B</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=c">C</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=d">D</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=e">E</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=f">F</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=g">G</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=h">H</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=i">I</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=j">J</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=k">K</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=l">L</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=m">M</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=n">N</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=o">O</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=p">P</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=q">Q</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=r">R</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=s">S</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=t">T</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=u">U</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=v">V</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=w">W</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=x">X</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=y">Y</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=z">Z</a>&nbsp;&nbsp;
                <a href="wiki.php?action=browse&amp;letter=1">All</a>&nbsp;&nbsp;
            </span>
        </div>
        <div class="TableContainer">
            <table class="TableWikiArticle">
                <tr class="Table-rowHeader">
                    <td class="Table-cell"><?= Lang::get('wiki', 'article') ?></td>
                    <td class="Table-cell"><?= Lang::get('wiki', 'last_upload') ?></td>
                    <td class="Table-cell"><?= Lang::get('wiki', 'last_edited') ?></td>
                </tr>
                <? while (list($ID, $Title, $Date, $UserID) = $DB->next_record()) { ?>
                    <tr>
                        <td class="Table-cell"><a href="wiki.php?action=article&amp;id=<?= $ID ?>"><?= $Title ?></a></td>
                        <td class="Table-cell"><?= $Date ?></td>
                        <td class="Table-cell"><?= Users::format_username($UserID, false, false, false) ?></td>
                    </tr>
                <?      } ?>
            </table>
        </div>
    <?  } ?>

</div>
<? View::show_footer(); ?>