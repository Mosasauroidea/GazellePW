<?
$CollageID = $_GET['collageid'];
if (!is_number($CollageID)) {
    error(0);
}

$DB->query("
	SELECT Name, UserID, CategoryID
	FROM collages
	WHERE ID = '$CollageID'");
list($Name, $UserID, $CategoryID) = $DB->next_record();
if ($CategoryID == $PersonalCollageCategoryCat && $UserID != $LoggedUser['ID'] && !check_perms('site_collages_delete')) {
    error(403);
}


$DB->query("
	SELECT
		ct.GroupID,
		um.ID,
		um.Username,
		ct.Sort
	FROM collages_torrents AS ct
		JOIN torrents_group AS tg ON tg.ID = ct.GroupID
		LEFT JOIN users_main AS um ON um.ID = ct.UserID
	WHERE ct.CollageID = '$CollageID'
	ORDER BY ct.Sort");

$GroupIDs = $DB->collect('GroupID');

$CollageDataList = $DB->to_array('GroupID', MYSQLI_ASSOC);
if (count($GroupIDs) > 0) {
    $TorrentList = Torrents::get_groups($GroupIDs);
} else {
    $TorrentList = array();
}

View::show_header(t('server.collages.manage_collage'), 'jquery-ui,jquery.tablesorter,sort', 'PageCollageManage');

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav"><?= t('server.collages.manage_collage') ?></div>
        <div class="BodyHeader-subNav"><a href="collages.php?id=<?= $CollageID ?>"><?= $Name ?></a></div>
    </div>
    <div class="BodyContent">
        <div class="Box">
            <div class="Box-header">
                <?= t('server.collages.sorting') ?>
            </div>
            <div class="Box-body">
                <?= t('server.collages.drag_drop_textnote') ?>
            </div>
        </div>
        <table class="TableManageCollage Table" id="manage_collage_table">
            <thead>
                <tr class="Table-rowHeader">
                    <th class="Table-cell" style="width: 50px" data-sorter="false"><?= t('server.collages.order') ?></th>
                    <th class="Table-cell Table-cellLeft" style="width: 50px"><span><abbr data-tooltip="Current rank">#</abbr></span></th>
                    <th class="Table-cell Table-cellLeft" style="width: 80px" data-sorter="ignoreArticles"><span><?= t('server.torrents.ft_year') ?></span></th>
                    <th class="Table-cell Table-cellLeft" data-sorter="ignoreArticles"><span><?= t('server.common.torrent') ?></span></th>
                    <th class="Table-cell Table-cellRight"><span><?= t('server.collages.adder') ?></span></th>
                    <th class="Table-cell Table-cellRight nobr" style="width:150px" data-sorter="false"><span><abbr data-tooltip="<?= t('server.collages.tweak_title') ?>"><?= t('server.collages.tweak') ?></abbr></span></th>
                </tr>
            </thead>
            <tbody>
                <?

                $Number = 0;
                foreach ($GroupIDs as $GroupID) {
                    if (!isset($TorrentList[$GroupID])) {
                        continue;
                    }
                    $Group = $TorrentList[$GroupID];
                    list(, $UserID, $Username, $Sort, $CatNum) = array_values($CollageDataList[$GroupID]);
                    $DisplayName = Torrents::group_name($Group);
                    $Number++;
                    $AltCSS = ($Number % 2 === 0) ? 'rowa' : 'rowb';
                ?>
                    <tr class="Table-row drag <?= $AltCSS ?>" id="li_<?= $GroupID ?>">
                        <form class="manage_form" name="collage" action="collages.php" method="post">
                            <td class="Table-cell">
                                <input class=" Input sort_numbers" type="text" name="sort" value="<?= $Sort ?>" id="sort_<?= $GroupID ?>" size="4" />
                            </td>
                            <td class="Table-cell Table-cellLeft"><?= $Number ?></td>
                            <td class="Table-cell Table-cellLeft"><?= $Group['Year'] ?></td>
                            <td class="Table-cell Table-cellLeft"><?= $DisplayName ?></td>
                            <td class="Table-cell Table-cellRight nobr"><?= Users::format_username($UserID, $Username, false, false, false) ?></td>
                            <td class="Table-cell Table-cellRight nobr">
                                <input type="hidden" name="action" value="manage_handle" />
                                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                                <input type="hidden" name="collageid" value="<?= $CollageID ?>" />
                                <input type="hidden" name="groupid" value="<?= $GroupID ?>" />
                                <button class="Button" type="submit" name="submit" value="Edit"><?= t('server.common.edit') ?> </button>
                                <button class="Button" type="submit" name="submit" value="Remove"><?= t('server.common.remove') ?> </button>
                            </td>
                        </form>
                    </tr>
                <? } ?>
            </tbody>
        </table>
        <div class="drag_drop_save hidden">
            <input class="Button" collage-manage-save type="button" name="submit" value="<?= t('server.apply.saved') ?>" />
        </div>
        <form class="dragdrop_form hidden" name="collage" action="collages.php" method="post" id="drag_drop_collage_form">
            <div>
                <input type="hidden" name="action" value="manage_handle" />
                <input type="hidden" name="auth" value="<?= $LoggedUser['AuthKey'] ?>" />
                <input type="hidden" name="collageid" value="<?= $CollageID ?>" />
                <input type="hidden" name="groupid" value="1" />
                <input type="hidden" name="drag_drop_collage_sort_order" id="drag_drop_collage_sort_order" readonly="readonly" value="" />
            </div>
        </form>
    </div>
</div>
<? View::show_footer(); ?>