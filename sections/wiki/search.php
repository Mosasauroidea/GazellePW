<?php
if (!empty($_GET['letter'])) {
    $Letter = strtoupper(substr($_GET['letter'], 0, 1));
    $SQL = "
	SELECT
		SQL_CALC_FOUND_ROWS
		ID,
		Title,
		Date,
		Author
	FROM wiki_articles
	WHERE MinClassRead <= '" . $LoggedUser['EffectiveClass'] . "'";
    if (isset($Letter) && $Letter !== '1') {
        $SQL .= " AND LEFT(Title,1) = '" . db_string($Letter) . "'";
    } else {
        $Letter = 'All';
    }
    $SQL .= " ORDER BY Title";
    $Order = "Title";
    $Way = "Ascending";
} else {
    // What are we looking for? Let's make sure it isn't dangerous.
    $Search = db_string(trim($_GET['search']));

    $Type = $_GET['type'];

    if (!in_array($Type, array('Title', 'Body'))) {
        $Type = 'Title';
    }

    // Break search string down into individual words
    $Words = explode(' ', $Search);

    $Order = $OrderTable[$_GET['order']];
    if (!$Order) {
        $Order = 'ID';
    }

    $Way = $WayTable[$_GET['way']];
    if (!$Way) {
        $Way = 'DESC';
    }

    $SQL = "
	SELECT
		SQL_CALC_FOUND_ROWS
		ID,
		Title,
		Date,
		Author
	FROM wiki_articles
	WHERE MinClassRead <= '" . $LoggedUser['EffectiveClass'] . "'";
    if ($Search != '') {
        $SQL .= " AND $Type LIKE '%";
        $SQL .= implode("%' AND $Type LIKE '%", $Words);
        $SQL .= "%' ";
    }

    $SQL .= "
	ORDER BY $Order $Way";
    $Order = $_GET['order'];
    $Way = $_GET['way'];
}
$OrderVals = array('Title', 'Created', 'Edited');
$WayVals = array('Ascending', 'Descending');
$TypeTable = array('Title' => 'Title', 'Body' => 'Body');
$OrderTable = array('Title' => 'Title', 'Created' => 'ID', 'Edited' => 'Date');
$WayTable = array('Ascending' => 'ASC', 'Descending' => 'DESC');

$Text = [
    'Title' => t('server.wiki.title'),
    'Created' => t('server.wiki.created'),
    'Edited' => t('server.wiki.edited'),
    'Ascending' => t('server.torrents.asc'),
    'Descending' => t('server.torrents.desc'),
];

list($Page, $Limit) = Format::page_limit(CONFIG['ARTICLES_PER_PAGE']);
$SQL .= " LIMIT $Limit ";
$RS = $DB->query($SQL);
$DB->query("
	SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();
$DB->query($SQL);
if (empty($_GET['nojump'])) {
    $ArticleID = Wiki::alias_to_id($_GET['search']);
    if ($ArticleID) {
        //Found the article!
        header('Location: wiki.php?action=article&id=' . $ArticleID);
        die();
    }
}
View::show_header(t('server.user.wiki'), '', 'PageWikiSearch');
$DB->set_query_id($RS);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.user.wiki') ?></h2>
        <h2 class="BodyHeader-subNav"><?= t('server.wiki.search_articles') ?></h2>
    </div>
    <div id="wiki_search_box">
        <form class="Form Box SearchPage" action="" method="get">
            <input type="hidden" name="action" value="search" />
            <input type="hidden" name="nojump" value="1" />
            <div class="SearchPageBody">
                <table class="Form-rowList">
                    <tr class="Form-row">
                        <td class="Form-label"><label for="search"><?= t('server.wiki.search_for') ?>:</label>
                        </td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" name="search" id="search" size="70" value="<?= display_str($_GET['search']) ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.wiki.search_in') ?>:</td>
                        <td class="Form-inputs">
                            <div class="RadioGroup">
                                <div class="Radio">
                                    <input id="search_title" class="Input" type="radio" name="type" value="Title" <? if ($Type == 'Title') {
                                                                                                                        echo 'checked="checked" ';
                                                                                                                    } ?> /><label class="Radio-label" for="search_title"> <?= t('server.wiki.title') ?></label>
                                </div>
                                <div class="Radio">
                                    <input id="search_body" class="Input" type="radio" name="type" value="Body" <? if ($Type == 'Body') {
                                                                                                                    echo 'checked="checked" ';
                                                                                                                } ?> /><label class="Radio-lable" for="search-body"> <?= t('server.wiki.body') ?></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label"><?= t('server.wiki.order_by') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="order">
                                <? foreach ($OrderVals as $Cur) { ?>
                                    <option class="Select-option" value="<?= $Cur ?>" <? if ($Order == $Cur || (!$Order && $Cur == 'Time')) {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= $Text[$Cur] ?></option>
                                <?                  } ?>
                            </select>
                            <select class="Input" name="way">
                                <? foreach ($WayVals as $Cur) { ?>
                                    <option class="Select-option" value="<?= $Cur ?>" <? if ($Way == $Cur || (!$Way && $Cur == 'Descending')) {
                                                                                            echo ' selected="selected"';
                                                                                        } ?>><?= $Text[$Cur] ?></option>
                                <?                  } ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td>

                        </td>
                    </tr>
                </table>
            </div>
            <div class="SearchPageFooter">
                <div class="SearchPageFooter-actions">
                    <button class="Button" type="submit" value="Search"><?= t('server.common.search') ?></button>
                </div>
            </div>
        </form>
    </div>
    <div class="BodyNavLinks">
        <span>
            <a href="wiki.php?action=search&amp;letter=a">A</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=b">B</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=c">C</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=d">D</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=e">E</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=f">F</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=g">G</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=h">H</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=i">I</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=j">J</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=k">K</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=l">L</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=m">M</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=n">N</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=o">O</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=p">P</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=q">Q</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=r">R</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=s">S</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=t">T</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=u">U</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=v">V</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=w">W</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=x">X</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=y">Y</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=z">Z</a>&nbsp;&nbsp;
            <a href="wiki.php?action=search&amp;letter=1">All</a>&nbsp;&nbsp;
        </span>
    </div>
    <?
    $Pages = Format::get_pages($Page, $NumResults, CONFIG['ARTICLES_PER_PAGE']);
    if ($Pages) { ?>
        <div class="BodyNavLinks pager"><?= ($Pages) ?></div>


    <?  } ?>
    <div class="TableContainer">
        <table class="TableWikiArticle Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.wiki.article') ?></td>
                <td class="Table-cell"><?= t('server.wiki.last_updated_on') ?></td>
                <td class="Table-cell"><?= t('server.wiki.last_edited_by') ?></td>
            </tr>
            <? while (list($ID, $Title, $Date, $UserID) = $DB->next_record()) { ?>
                <tr class="Table-row">
                    <td class="Table-cell"><a href="wiki.php?action=article&amp;id=<?= $ID ?>"><?= $Title ?></a></td>
                    <td class="Table-cell"><?= $Date ?></td>
                    <td class="Table-cell"><?= Users::format_username($UserID, false, false, false) ?></td>
                </tr>
            <?  } ?>
        </table>
    </div>
    <div class="BodyNavLinks"><?= $Pages ?></div>
</div>
<? View::show_footer(); ?>