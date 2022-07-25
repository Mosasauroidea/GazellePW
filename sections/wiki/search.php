<?php
if (empty($_GET['nojump'])) {
    $ArticleID = Wiki::alias_to_id($_GET['search']);
    if ($ArticleID) {
        //Found the article!
        header('Location: wiki.php?action=article&id=' . $ArticleID);
        die();
    }
}

define('ARTICLES_PER_PAGE', 25);
list($Page, $Limit) = Format::page_limit(ARTICLES_PER_PAGE);

$OrderVals = array('Title', 'Created', 'Edited');
$WayVals = array('Ascending', 'Descending');
$TypeTable = array('Title' => 'Title', 'Body' => 'Body');
$OrderTable = array('Title' => 'Title', 'Created' => 'ID', 'Edited' => 'Date');
$WayTable = array('Ascending' => 'ASC', 'Descending' => 'DESC');

// What are we looking for? Let's make sure it isn't dangerous.
$Search = db_string(trim($_GET['search']));

if (!in_array($Type, array('Title', 'Body'))) {
    $Type = 'Title';
}

// Break search string down into individual words
$Words = explode(' ', $Search);

$Type = $TypeTable[$_GET['type']];
if (!$Type) {
    $Type = 'Title';
}

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
	ORDER BY $Order $Way
	LIMIT $Limit ";
$RS = $DB->query($SQL);
$DB->query("
	SELECT FOUND_ROWS()");
list($NumResults) = $DB->next_record();

View::show_header(t('server.wiki.search_articles'), '', 'PageWikiSearch');
$DB->set_query_id($RS);
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.wiki.search_articles') ?></h2>
        <div class="BodyNavLinks">
            <a href="wiki.php?action=create&amp;alias=<?= display_str(Wiki::normalize_alias($_GET['search'])) ?>" class="brackets"><?= t('server.wiki.create_an_article') ?></a>
        </div>
    </div>
    <div id="wiki_search_box">
        <form action="" method="get">
            <div>
                <input type="hidden" name="action" value="search" />
                <input type="hidden" name="nojump" value="1" />
            </div>
            <table cellpadding="6" cellspacing="1" border="0" class="layout border" width="100%">
                <tr>
                    <td class="label"><label for="search"><strong><?= t('server.wiki.search_for') ?>:</strong></label></td>
                    <td colspan="3">
                        <input class="Input" type="text" name="search" id="search" size="70" value="<?= display_str($_GET['search']) ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="label"><strong><?= t('server.wiki.search_in') ?>:</strong></td>
                    <td>
                        <label><input type="radio" name="type" value="Title" <? if ($Type == 'Title') {
                                                                                    echo 'checked="checked" ';
                                                                                } ?> /> <?= t('server.wiki.title') ?></label>
                        <label><input type="radio" name="type" value="Body" <? if ($Type == 'Body') {
                                                                                echo 'checked="checked" ';
                                                                            } ?> /> <?= t('server.wiki.body') ?></label>
                    </td>
                    <td class="label"><strong><?= t('server.wiki.order_by') ?>:</strong></td>
                    <td>
                        <select class="Input" name="order">
                            <? foreach ($OrderVals as $Cur) { ?>
                                <option class="Select-option" value="<?= $Cur ?>" <? if ($_GET['order'] == $Cur || (!$_GET['order'] && $Cur == 'Time')) {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= $Cur ?></option>
                            <?                  } ?>
                        </select>
                        <select class="Input" name="way">
                            <? foreach ($WayVals as $Cur) { ?>
                                <option class="Select-option" value="<?= $Cur ?>" <? if ($_GET['way'] == $Cur || (!$_GET['way'] && $Cur == 'Descending')) {
                                                                                        echo ' selected="selected"';
                                                                                    } ?>><?= $Cur ?></option>
                            <?                  } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" class="center">
                        <input class="Button" type="submit" value="Search" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <!-- <br /> -->
    <?
    $Pages = Format::get_pages($Page, $NumResults, ARTICLES_PER_PAGE);
    if ($Pages) { ?>
        <div class="BodyNavLinks pager"><?= ($Pages) ?></div>
    <?  } ?>
    <div class="TableContainer">
        <table class="TableWikiArticle Table">
            <tr class="Table-rowHeader">
                <td class="Table-cell"><?= t('server.wiki.article') ?></td>
                <td class="Table-cell" class="Table-cell"><?= t('server.wiki.last_updated_on') ?></td>
                <td><?= t('server.wiki.last_edited_by') ?></td>
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