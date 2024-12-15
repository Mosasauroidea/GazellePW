<?
// todo by qwerty 冗余代码和browse.php

use Gazelle\Torrent\TorrentSlot;

include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');

if (empty($_SERVER['QUERY_STRING']) || (count($_GET) === 1 && isset($_GET['page']))) {
    if (!empty($LoggedUser['DefaultSearch'])) {
        if (!empty($_GET['page'])) {
            $Page = $_GET['page'];
            parse_str($LoggedUser['DefaultSearch'], $_GET);
            $_GET['page'] = $Page;
        } else {
            parse_str($LoggedUser['DefaultSearch'], $_GET);
        }
    }
}
$OrderBy = 'time';
$GroupResults = true;
$OrderWay = 'desc';
$Page = !empty($_GET['page']) ? (int) $_GET['page'] : 1;
$Search = new TorrentSearch($GroupResults, $OrderBy, $OrderWay, $Page, CONFIG['TORRENTS_PER_PAGE']);
$Results = $Search->query($_GET);
$Groups = $Search->get_groups();
$RealNumResults = $NumResults = $Search->record_count();

if (check_perms('site_search_many')) {
    $LastPage = ceil($NumResults / CONFIG['TORRENTS_PER_PAGE']);
    $FixSearch = new TorrentSearch($GroupResults, $OrderBy, $OrderWay, $LastPage, CONFIG['TORRENTS_PER_PAGE']);
    $FixSearch->query($_GET);
    $RealNumResults = $NumResults = $FixSearch->record_count();
} else {
    $NumResults = min($NumResults, CONFIG['SPHINX_MAX_MATCHES']);
}

View::show_header(t('server.torrents.missing'), 'missing', 'PageTorrentMissing');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= t('server.torrents.missing') ?></h2>
    </div>
    <form class="Form SearchPage Box SearchTorrent" name="torrents" method="get" action="" onsubmit="$(this).disableUnset();">
        <div class="SearchPageBody <?= $HideFilter ? ' hidden' : '' ?>">
            <table class="Form-rowList">
                <tr class="Form-row is-searchStr">
                    <td class="Form-label"><?= t('server.torrents.ftb_searchstr') ?>:</td>
                    <td class="Form-inputs ftb_searchstr">
                        <input class="Input" type="text" spellcheck="false" size="40" name="searchstr" value="<? Format::form('searchstr') ?>" />
                    </td>
                </tr>
            </table>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-resultCount">
                <?= number_format($RealNumResults) ?> <?= t('server.torrents.space_results') ?>
                <?= !check_perms('site_search_many') ? "(" . t('server.torrents.showing_first_n_matches', ['Values' => [$NumResults]])  . ")" : "" ?>
            </div>
            <div class="SearchPageFooter-actions">
                <input class="Button" type="submit" value="<?= t('server.torrents.search_torrents') ?>" />
                <input class="is-inputAction" type="hidden" name="action" id="ft_type" value=basic" />
                <input type="hidden" name="searchsubmit" value="1" />
                <input type="hidden" name="type" value="missing" />
            </div>

        </div>
    </form>
    <?
    if ($NumResults == 0) {
        $text1 = t('server.torrents.search_empty_1');
        $text2 = t('server.torrents.search_empty_2');
        print <<<HTML
<div class="BoxBody" align="center">
	<h2>$text1</h2>
	<p>$text2</p>
</div>
</div>
HTML;
        View::show_footer();
        die();
    }

    if ($NumResults < ($Page - 1) * CONFIG['TORRENTS_PER_PAGE'] + 1) {
        $LastPage = ceil($NumResults / CONFIG['TORRENTS_PER_PAGE']);
        $Pages = Format::get_pages(0, $NumResults, CONFIG['TORRENTS_PER_PAGE']);
    ?>
        <div class="BoxBody" align="center">
            <h2>The requested page contains no matches.</h2>
            <p>You are requesting page <?= $Page ?>, but the search returned only <?= number_format($LastPage) ?> pages.</p>
        </div>
        <div class="BodyNavLinks">Go to page <?= $Pages ?></div>
</div>
<?
        View::show_footer();
        die();
    }

    // List of pages
    $Pages = Format::get_pages($Page, $NumResults, CONFIG['TORRENTS_PER_PAGE']);
?>
<div class="BodyNavLinks"><?= $Pages ?></div>
<div class="TableContainer">
    <table class="TableMissing Table" variant="ungroup" id="slot_table">
        <tr class="Table-rowHeader">
            <td class="TableMissing-cellName Table-cell"><?= t('server.torrents.name') ?></td>
            <td class="TableMissing-cellSnatches Table-cell Table-cellCenter ">
                <i aria-hidden="true" alt="Snatches" data-tooltip="<?= t('server.common.snatched') ?>">
                    <?= icon("torrent-snatches") ?>
                </i>
            </td>
            <td class="TableMissing-cellRequests Table-cell Table-cellCenter ">
                <i aria-hidden="true" alt="Request number" data-tooltip="<?= t('server.torrents.request_number') ?>">
                    <?= icon("Table/request-numbers") ?>
                </i>
            </td>
            <td class="TableMissing-cellSDEncode Table-cell Table-cellCenter " data-tooltip="<?= t('server.torrents.sd_encode_title') ?>">
                <?= t('server.torrents.sd_encode') ?>
            </td>
            <td class="TableMissing-cellSDUntouched Table-cell Table-cellCenter " data-tooltip="<?= t('server.torrents.sd_untouched_title') ?>">
                <?= t('server.torrents.sd_untouched') ?>
            </td>
            <td class="TableMissing-cellHDencode Table-cell Table-cellCenter " data-tooltip="<?= t('server.torrents.hd_encode_title') ?>">
                <?= t('server.torrents.hd_encode') ?>
            </td>
            <td class="TableMissing-cellHDUntouched Table-cell Table-cellCenter " data-tooltip="<?= t('server.torrents.hd_untouched_title') ?>">
                <?= t('server.torrents.hd_untouched') ?>
            </td>
            <td class="TableMissing-cellUHDEncode Table-cell Table-cellCenter " data-tooltip="<?= t('server.torrents.uhd_encode_title') ?>">
                <?= t('server.torrents.uhd_encode') ?>
            </td>
            <td class="TableMissing-cellUHDUnTouched Table-cell  Table-cellCenter " data-tooltip="<?= t('server.torrents.uhd_untouched_title') ?>">
                <?= t('server.torrents.uhd_untouched') ?>
            </td>
        </tr>
        <?
        foreach ($Results as $Key => $GroupID) {
            $GroupInfo = $Groups[$GroupID];
            $GroupName = Torrents::group_name($GroupInfo);

            $SnatchedCount = 0;
            foreach ($GroupInfo['Torrents'] as $T) {
                $SnatchedCount += $T['Snatched'];
            }
            $RequestCount = count(Requests::get_group_requests($GroupID));
            $SlotStatus = TorrentSlot::get_slot_group_status($GroupInfo['Torrents']);
            $SlotStatusClass = [];
            $text = [];
            foreach ($SlotStatus as $type => $Status) {
                list($status, $misslots) = $Status;
                switch ($status) {
                    case TorrentSlot::TorrentSlotStatusEmpty:
                        // 空的class
                        $SlotStatusClass[$type] = '';
                        $text[$type] = "<span class='u-colorSuccess' data-tooltip='" . t('server.torrents.slot_status_empty') . "'>" . icon('circle') . "</span>";
                        break;
                    case TorrentSlot::TorrentSlotStatusFull:
                        // 满的class
                        $SlotStatusClass[$type] = '';
                        $text[$type] = "<span class='u-colorWarning' title = '" . t('server.torrents.slot_status_full') . "'>" . icon('circle') . "</span>";
                        break;
                    case TorrentSlot::TorrentSlotStatusFree:
                        // 有空位的class
                        $SlotStatusClass[$type] = '';
                        $SlotsTitle = '';
                        foreach ($misslots as $slot) {
                            $SlotsTitle .= "<div>&nbsp;&nbsp;" . t('server.torrents.' . TorrentSlot::slot_option_lang($slot)) . "</div>";
                        }
                        $title = t('server.torrents.slots_we_need');
                        $text[$type] = "<span class='u-colorCircle' data-tooltip-html>" . icon('circle') .
                            "<div data-tooltip-html-content>
                        <div class='title'>$title</div>
                        <div class='content'>$SlotsTitle</div>
                    </div>
                  </span>";
                        break;
                    default:
                        $SlotStatusClass[$type] = '';
                        break;
                }
            }

        ?>
            <tr class="TableMissing-row Table-row">
                <td class="TableMissing-cellName Table-cell"><a href="torrents.php?id=<?= $GroupID ?>&view=slot#slot"><?= $GroupName ?><a></td>
                <td class="TableMissing-cellSnatches Table-cell  Table-cellCenter "><?= $SnatchedCount ?></td>
                <td class="TableMissing-cellRequests Table-cell  Table-cellCenter "><?= $RequestCount ?></td>
                <td class="TableMissing-cellSDEncode Table-cell  Table-cellCenter <?= $SlotStatusClass[TorrentSlot::TorrentSlotGroupSDEncode] ?>"><?= $text[TorrentSlot::TorrentSlotGroupSDEncode] ?>
                </td>
                <td class="TableMissing-cellSDUntouched Table-cell  Table-cellCenter <?= $SlotStatusClass[TorrentSlot::TorrentSlotGroupSDUntouched] ?>"><?= $text[TorrentSlot::TorrentSlotGroupSDUntouched] ?>
                </td>
                <td class="TableMissing-cellHDEncode Table-cell  Table-cellCenter <?= $SlotStatusClass[TorrentSlot::TorrentSlotGroupHDEncode] ?>"><?= $text[TorrentSlot::TorrentSlotGroupHDEncode] ?>
                </td>
                <td class="TableMissing-cellHDUntouched Table-cell  Table-cellCenter <?= $SlotStatusClass[TorrentSlot::TorrentSlotGroupHDUntouched] ?>"><?= $text[TorrentSlot::TorrentSlotGroupHDUntouched] ?></td>
                <td class="TableMissing-cellUHDEncode Table-cell  Table-cellCenter <?= $SlotStatusClass[TorrentSlot::TorrentSlotGroupUHDEncode] ?>"><?= $text[TorrentSlot::TorrentSlotGroupUHDEncode] ?></td>
                <td class="TableMissing-cellUHDUntouched Table-cell Table-cellCenter <?= $SlotStatusClass[TorrentSlot::TorrentSlotGroupUHDUntouched] ?>"><?= $text[TorrentSlot::TorrentSlotGroupUHDUntouched] ?></td>
            </tr>
        <?
        }
        ?>
    </table>
</div>
<div class="BodyNavLinks"><?= $Pages ?></div>
</div>
<?
View::show_footer();
