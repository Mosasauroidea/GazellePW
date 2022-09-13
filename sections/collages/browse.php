<?php
define('COLLAGES_PER_PAGE', 25);

list($Page, $Limit) = Format::page_limit(COLLAGES_PER_PAGE);


$OrderVals = array(
    'Time' => t('server.collages.search_time'),
    'Name' => t('server.collages.search_name'),
    'Subscribers' => t('server.collages.search_subscribers'),
    'Torrents' => t('server.collages.search_torrents'),
    'Updated' => t('server.collages.search_updated')
);
$WayVals = array('Ascending' => t('server.collages.search_ascending'), 'Descending' => t('server.collages.search_descending'));
$OrderTable = array('Time' => 'ID', 'Name' => 'c.Name', 'Subscribers' => 'c.Subscribers', 'Torrents' => 'NumTorrents', 'Updated' => 'c.Updated');
$WayTable = array('Ascending' => 'ASC', 'Descending' => 'DESC');

// Are we searching in bodies, or just names?
if (!empty($_GET['type'])) {
    $Type = $_GET['type'];
    if (!in_array($Type, array('c.name', 'description'))) {
        $Type = 'c.name';
    }
} else {
    $Type = 'c.name';
}

if (!empty($_GET['search'])) {
    // What are we looking for? Let's make sure it isn't dangerous.
    $Search = db_string(trim($_GET['search']));
    // Break search string down into individual words
    $Words = explode(' ', $Search);
}

if (!empty($_GET['tags'])) {
    $Tags = explode(',', db_string(trim($_GET['tags'])));
    foreach ($Tags as $ID => $Tag) {
        $Tags[$ID] = Misc::sanitize_tag($Tag);
    }
}
$Categories = [];
if (!empty($_GET['cats'])) {
    foreach ($_GET['cats'] as $Cat => $Accept) {
        $Categories[] = $Cat;
    }
} else {
    foreach ($CollageCats as $Cat) {
        if ($Cat == $PersonalCollageCategoryCat && empty($_GET['userid'])) {
            continue;
        }
        $Categories[] = $Cat;
    }
}

// Ordering
if (!empty($_GET['order_by']) && !empty($OrderTable[$_GET['order_by']])) {
    $Order = $OrderTable[$_GET['order_by']];
} else {
    $Order = 'ID';
}

if (!empty($_GET['order_way']) && !empty($WayTable[$_GET['order_way']])) {
    $Way = $WayTable[$_GET['order_way']];
} else {
    $Way = 'DESC';
}

$BookmarkView = !empty($_GET['bookmarks']);

if ($BookmarkView) {
    $Categories[] = 0;
    $BookmarkJoin = 'INNER JOIN bookmarks_collages AS bc ON c.ID = bc.CollageID';
} else {
    $BookmarkJoin = '';
}

$BaseSQL = $SQL = "
	SELECT
		SQL_CALC_FOUND_ROWS
		c.ID,
		c.Name,
		c.NumTorrents,
		c.TagList,
		c.CategoryID,
		c.UserID,
		c.Subscribers,
		c.Updated
	FROM collages AS c
		$BookmarkJoin
	WHERE Deleted = '0'";

if ($BookmarkView) {
    $SQL .= " AND bc.UserID = '" . $LoggedUser['ID'] . "'";
}

if (!empty($Search)) {
    $SQL .= " AND $Type LIKE '%";
    $SQL .= implode("%' AND $Type LIKE '%", $Words);
    $SQL .= "%'";
}

if (isset($_GET['tags_type']) && $_GET['tags_type'] === '0') { // Any
    $_GET['tags_type'] = '0';
} else { // All
    $_GET['tags_type'] = '1';
}

if (!empty($Tags)) {
    $SQL .= " AND (TagList LIKE '%";
    if ($_GET['tags_type'] === '0') {
        $SQL .= implode("%' OR TagList LIKE '%", $Tags);
    } else {
        $SQL .= implode("%' AND TagList LIKE '%", $Tags);
    }
    $SQL .= "%')";
}

if (!empty($_GET['userid'])) {
    $UserID = $_GET['userid'];
    if (!is_number($UserID)) {
        error(404);
    }
    $User = Users::user_info($UserID);
    $Perms = Permissions::get_permissions($User['PermissionID']);
    $UserClass = $Perms['Class'];

    $UserLink = '<a href="user.php?id=' . $UserID . '">' . $User['Username'] . '</a>';
    if (!empty($_GET['contrib'])) {
        if (!check_paranoia('collagecontribs', $User['Paranoia'], $UserClass, $UserID)) {
            error(403);
        }
        $DB->query("
			SELECT DISTINCT CollageID
			FROM collages_torrents
			WHERE UserID = $UserID");
        $CollageIDs = $DB->collect('CollageID');
        if (empty($CollageIDs)) {
            $SQL .= " AND 0";
        } else {
            $SQL .= " AND c.ID IN(" . db_string(implode(',', $CollageIDs)) . ')';
        }
    } else {
        if (!check_paranoia('collages', $User['Paranoia'], $UserClass, $UserID)) {
            error(403);
        }
        $SQL .= " AND UserID = '" . $_GET['userid'] . "'";
    }
    $Categories[] = 0;
}

if (!empty($Categories)) {
    $SQL .= " AND CategoryID IN(" . db_string(implode(',', $Categories)) . ')';
}

if (isset($_GET['action']) && $_GET['action'] === 'mine') {
    $SQL = $BaseSQL;
    $SQL .= "
		AND c.UserID = '" . $LoggedUser['ID'] . "'
		AND c.CategoryID = 0";
}

$SQL .= "
	ORDER BY $Order $Way
	LIMIT $Limit";
$DB->query($SQL);
$Collages = $DB->to_array();
$DB->query('SELECT FOUND_ROWS()');
list($NumResults) = $DB->next_record();

View::show_header(t('server.collages.browse_collages'), '', 'PageCollageHome');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <?
        if ($BookmarkView) { ?>
            <div class="BodyHeader-nav"><?= t('server.collages.your_bookmarked_collages') ?></div>
            <?
        } else if (!empty($UserLink)) {
            if (isset($CollageIDs)) {
            ?>
                <div class="BodyHeader-nav">
                    <?= t('server.collages.contributed_collages_browse', ['Values' => [$UserLink]]); ?>
                </div>
            <?
            } else if (isset($UserLink)) {
            ?>
                <div class="BodyHeader-nav">
                    <?= t('server.collages.started_collages_browse', ['Values' => [$UserLink]]) ?>
                </div>
            <?
            }
        } else {
            ?>
            <div class="BodyHeader-nav">
                <?= t('server.collages.browse_collages') ?>
            </div>
        <?
        }
        if (!$BookmarkView) {
        ?>
            <div class="BodyNavLinks">
                <?
                if (check_perms('site_collages_create')) {
                ?>
                    <a href="collages.php?action=new" class="brackets"><?= t('server.collages.create_collages') ?></a>
                    <?
                }
                if (check_perms('site_collages_personal')) {

                    $DB->query("
				SELECT ID
				FROM collages
				WHERE UserID = '$LoggedUser[ID]'
					AND CategoryID = '0'
					AND Deleted = '0'");
                    $CollageCount = $DB->record_count();

                    if ($CollageCount === 1) {
                        list($CollageID) = $DB->next_record();
                    ?>
                        <a href="collages.php?id=<?= $CollageID ?>" class="brackets"><?= t('server.collages.personal_collage') ?></a>
                    <?          } elseif ($CollageCount > 1) { ?>
                        <a href="collages.php?action=mine" class="brackets"><?= t('server.collages.personal_collages') ?></a>
                    <?
                    }
                }
                if (check_perms('site_collages_subscribe')) {
                    ?>
                    <a href="userhistory.php?action=subscribed_collages" class="brackets"><?= t('server.collages.subscribed_collages') ?></a>
                <?      } ?>
                <a href="bookmarks.php?type=collages" class="brackets"><?= t('server.collages.bookmarks_collages') ?></a>
                <? if (check_perms('site_collages_recover')) { ?>
                    <a href="collages.php?action=recover" class="brackets"><?= t('server.collages.recover_collages') ?></a>
                <?
                }
                if (check_perms('site_collages_create') || check_perms('site_collages_personal') || check_perms('site_collages_recover')) {
                ?>
            </div>
            <div class="BodyNavLinks">
            <?
                }
            ?>
            <a href="collages.php?userid=<?= $LoggedUser['ID'] ?>" class="brackets"><?= t('server.collages.start_collages') ?></a>
            <a href="collages.php?userid=<?= $LoggedUser['ID'] ?>&amp;contrib=1" class="brackets"><?= t('server.collages.contributed_collages') ?></a>
            <a href="random.php?action=collage" class="brackets"><?= t('server.collages.random_collages') ?></a>
            </div>
        <?
        } else {
        ?>

            <div>
                <div class="BodyNavLinks">
                    <a href="bookmarks.php?type=torrents" class="brackets"><?= t('server.index.moviegroups') ?></a>
                    <a href="bookmarks.php?type=artists" class="brackets"><?= t('server.common.artists') ?></a>
                    <a href="bookmarks.php?type=collages" class="brackets"><?= t('server.collages.collage') ?></a>
                    <a href="bookmarks.php?type=requests" class="brackets"><?= t('server.common.requests') ?></a>
                </div>
            </div>
        <?
        }
        ?>

        <? if (!$BookmarkView) { ?>
            <div>
                <form class="Form SearchPage Box SearchCollage" name="collages" action="" method="get">
                    <div><input type="hidden" name="action" value="search" /></div>
                    <div class="TableContainer">
                        <table class="Form-rowList">
                            <tr class="Form-row is-searchStr">
                                <td class="Form-label"><?= t('server.collages.ftb_searchstr') ?>:</td>
                                <td class="Form-inputs">
                                    <input class="Input" type="text" name="search" size="70" value="<?= (!empty($_GET['search']) ? display_str($_GET['search']) : '') ?>" />
                                </td>
                            </tr>
                            <tr class="Form-row is-tagFilter">
                                <td class="Form-label"><?= t('server.collages.tags') ?>:</td>
                                <td class="Form-inputs">
                                    <input class="Input" type="text" id="tags" name="tags" size="70" value="<?= (!empty($_GET['tags']) ? display_str($_GET['tags']) : '') ?>" <? Users::has_autocomplete_enabled('other'); ?> />
                                    <div class="RadioGroup">
                                        <div class="Radio">
                                            <input class="Input" type="radio" name="tags_type" id="tags_type0" value="0" <? Format::selected('tags_type', 0, 'checked') ?> />
                                            <label class="Radio-label" for="tags_type0"> <?= t('server.collages.any') ?></label>
                                        </div>
                                        <div class="Radio">
                                            <input class="Input" type="radio" name="tags_type" id="tags_type1" value="1" <? Format::selected('tags_type', 1, 'checked') ?> />
                                            <label class="Radio-input" for="tags_type1"> <?= t('server.collages.all') ?></label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="Form-row is-type">
                                <td class="Form-label"><?= t('server.collages.type') ?>:</td>
                                <td class="Form-inputs">
                                    <? foreach ($CollageCats as $ID) {
                                    ?>
                                        <div class="Checkbox">
                                            <input class="Input" type="checkbox" value="1" name="cats[<?= $ID ?>]" id="cats_<?= $ID ?>" <? if (in_array($ID, $Categories)) {
                                                                                                                                            echo ' checked="checked"';
                                                                                                                                        } ?> />
                                            <label class="Checkbox-label" for="cats_<?= $ID ?>"><?= t('server.collages.collagecats')[$ID] ?></label>
                                        </div>
                                    <?      } ?>
                                </td>
                            </tr>
                            <tr class="Form-row is-searchIn">
                                <td class="Form-label"><?= t('server.collages.search_for') ?>:</td>
                                <td class="Form-inputs">
                                    <div class="RadioGroup">
                                        <div class="Radio">
                                            <input class="Input" type="radio" name="type" id="type1" value="c.name" <? if ($Type === 'c.name') {
                                                                                                                        echo 'checked="checked" ';
                                                                                                                    } ?> />
                                            <label class="Radio-label" for="type1">
                                                <?= t('server.collages.search_name') ?>
                                            </label>
                                        </div>
                                        <div class="Radio">
                                            <input class="Input" type="radio" name="type" id="type2" value="description" <? if ($Type === 'description') {
                                                                                                                                echo 'checked="checked" ';
                                                                                                                            } ?> />
                                            <label class="Radio-label" for="type2">
                                                <?= t('server.collages.search_descriptions') ?>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="Form-row is-order">
                                <td class="Form-label"><?= t('server.collages.ft_order') ?>:</td>
                                <td class="Form-inputs">
                                    <select class="Input" name="order_by">
                                        <? foreach ($OrderVals as $Key => $Cur) { ?>
                                            <option class="Select-option" value="<?= $Key ?>" <? if (isset($_GET['order_by']) && $_GET['order_by'] === $Key || (!isset($_GET['order_by']) && $Key === 'Time')) {
                                                                                                    echo ' selected="selected"';
                                                                                                } ?>>
                                                <?= $Cur ?>
                                            </option>
                                        <?      } ?>
                                    </select>
                                    <select class="Input" name="order_way">
                                        <? foreach ($WayVals as $Key => $Cur) { ?>
                                            <option class="Select-option" value="<?= $Key ?>" <? if (isset($_GET['order_way']) && $_GET['order_way'] === $Key || (!isset($_GET['order_way']) && $Key === 'Descending')) {
                                                                                                    echo ' selected="selected"';
                                                                                                } ?>>
                                                <?= $Cur ?>
                                            </option>
                                        <?      } ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="SearchPageFooter">
                        <div class="SearchPageFooter-actions">
                            <input class="Button" type="submit" value="<?= t('server.collages.search') ?>" />
                        </div>
                    </div>
                </form>
            </div>
        <?
        }

        ?>
    </div>
    <?
    $Pages = Format::get_pages($Page, $NumResults, COLLAGES_PER_PAGE, 9);
    if ($Pages) {
    ?>
        <div class="BodyNavLinks">
            <?
            echo $Pages;
            ?>
        </div>
    <?
    }
    if (count($Collages) === 0) { ?>
        <div>
            <div class="center">
                <? if ($BookmarkView) { ?>
                    <div><?= t('server.collages.result_1') ?></div>
                <?      } else { ?>
                    <h2><?= t('server.collages.result_2') ?></h2>
                    <p><?= t('server.collages.result_3') ?></p>
                <?      } ?>
            </div>
        </div>
        <!--box-->
</div>
<!--content-->
<? View::show_footer();
        die();
    }
?>
<div class="TableContainer">
    <table class="TableCollage Table">
        <tr class="Table-rowHeader">
            <td class="Table-cell"><?= t('server.collages.category') ?></td>
            <td class="Table-cell"><?= t('server.collages.collage') ?></td>
            <td class="Table-cell Table-cellRight"><?= t('server.common.torrents') ?></td>
            <td class="Table-cell Table-cellRight"><?= t('server.collages.subscribers') ?></td>
            <td class="Table-cell Table-cellRight"><?= t('server.collages.updated') ?></td>
            <td class="Table-cell Table-cellRight"><?= t('server.collages.author') ?></td>
        </tr>
        <?
        $Row = 'a'; // For the pretty colours
        foreach ($Collages as $Collage) {
            list($ID, $Name, $NumTorrents, $TagList, $CategoryID, $UserID, $Subscribers, $Updated) = $Collage;
            $Row = $Row === 'a' ? 'b' : 'a';
            $TorrentTags = new Tags($TagList);
        ?>
            <tr class="Table-row <?= ($BookmarkView) ? " bookmark_$ID" : ''; ?>">
                <td class="Table-cell td_collage_category">
                    <a href="collages.php?action=search&amp;cats[<?= (int)$CategoryID ?>]=1"><?= t('server.collages.collagecats')[(int)$CategoryID] ?></a>
                </td>
                <td class="Table-cell">
                    <a href="collages.php?id=<?= $ID ?>"><?= $Name ?></a>
                    <div class="tags"><i><?= $TorrentTags->format('collages.php?action=search&amp;tags=') ?></i></div>
                </td>
                <td class="Table-cell Table-cellRight td_torrent_count number_column"><?= number_format((int)$NumTorrents) ?></td>
                <td class="Table-cell Table-cellRight td_subscribers number_column"><?= number_format((int)$Subscribers) ?></td>
                <td class="Table-cell Table-cellRight td_updated nobr"><?= time_diff($Updated) ?></td>
                <td class="Table-cell Table-cellRight td_author"><?= Users::format_username($UserID, false, false, false) ?></td>
            </tr>
        <?
        }
        ?>
    </table>
</div>
<div class="BodyNavLinks"><?= $Pages ?></div>
</div>
<? View::show_footer(); ?>