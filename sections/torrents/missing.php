<?
// todo by qwerty 冗余代码和browse.php

use Gazelle\Torrent\TorrentSlotGroup;
use Gazelle\Torrent\TorrentSlotGroupStatus;
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

View::show_header(Lang::get('torrents', 'missing'), 'missing', 'PageTorrentMissing');
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Lang::get('torrents', 'missing') ?></h2>
    </div>
    <form class="Form SearchPage Box SearchTorrent" name="torrents" method="get" action="" onsubmit="$(this).disableUnset();">
        <div class="SearchPageBody <?= $HideFilter ? ' hidden' : '' ?>">
            <table class="Form-rowList">
                <tr class="Form-row is-searchStr">
                    <td class="Form-label"><?= Lang::get('torrents', 'ftb_searchstr') ?>:</td>
                    <td class="Form-inputs ftb_searchstr">
                        <input class="Input" type="text" spellcheck="false" size="40" name="searchstr" value="<? Format::form('searchstr') ?>" />
                    </td>
                </tr>
            </table>
        </div>
        <div class="SearchPageFooter">
            <div class="SearchPageFooter-resultCount">
                <?= number_format($RealNumResults) ?> <?= Lang::get('torrents', 'space_results') ?>
                <?= !check_perms('site_search_many') ? "(" . Lang::get('torrents', 'showing_first_n_matches_before') . $NumResults . Lang::get('torrents', 'showing_first_n_matches_after') . ")" : "" ?>
            </div>
            <div class="SearchPageFooter-actions">
                <input class="Button" type="submit" value="<?= Lang::get('torrents', 'search_torrents') ?>" />
                <input class="is-inputAction" type="hidden" name="action" id="ft_type" value=basic" />
                <input type="hidden" name="searchsubmit" value="1" />
                <input type="hidden" name="type" value="missing" />
            </div>

        </div>
    </form>
    <?
    if ($NumResults == 0) {
        $text1 = Lang::get('torrents', 'search_empty_1');
        $text2 = Lang::get('torrents', 'search_empty_2');
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
            <td class="TableMissing-cellName Table-cell"><?= Lang::get('torrents', 'name') ?></td>
            <td class="TableMissing-cellSnatches Table-cell">
                <i aria-hidden="true" alt="Snatches" data-tooltip="<?= Lang::get('global', 'snatched') ?>">
                    <?= icon("torrent-snatches") ?>
                </i>
            </td>
            <td class="TableMissing-cellRequests Table-cell">
                <i aria-hidden="true" alt="Request number" data-tooltip="<?= Lang::get('torrents', 'request_number') ?>">
                    <?= icon("Table/request-numbers") ?>
                </i>
            </td>
            <td class="TableMissing-cellSDEncode Table-cell" data-tooltip="<?= Lang::get('torrents', 'sd_encode_title') ?>">
                <?= Lang::get('torrents', 'sd_encode') ?>
            </td>
            <td class="TableMissing-cellSDUntouched Table-cell" data-tooltip="<?= Lang::get('torrents', 'sd_untouched_title') ?>">
                <?= Lang::get('torrents', 'sd_untouched') ?>
            </td>
            <td class="TableMissing-cellHDencode Table-cell" data-tooltip="<?= Lang::get('torrents', 'hd_encode_title') ?>">
                <?= Lang::get('torrents', 'hd_encode') ?>
            </td>
            <td class="TableMissing-cellHDUntouched Table-cell" data-tooltip="<?= Lang::get('torrents', 'hd_untouched_title') ?>">
                <?= Lang::get('torrents', 'hd_untouched') ?>
            </td>
            <td class="TableMissing-cellUHDEncode Table-cell" data-tooltip="<?= Lang::get('torrents', 'uhd_encode_title') ?>">
                <?= Lang::get('torrents', 'uhd_encode') ?>
            </td>
            <td class="TableMissing-cellUHDUnTouched Table-cell" data-tooltip="<?= Lang::get('torrents', 'uhd_untouched_title') ?>">
                <?= Lang::get('torrents', 'uhd_untouched') ?>
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
            $RequestCount = count(get_group_requests($GroupID));
            $SlotStatus = TorrentSlot::get_slot_group_status($GroupInfo['Torrents']);
            $SlotStatusClass = [];
            $text = [];
            foreach ($SlotStatus as $type => $Status) {
                list($status, $misslots) = $Status;
                switch ($status) {
                    case TorrentSlotGroupStatus::Empty:
                        // 空的class
                        $SlotStatusClass[$type] = '';
                        $text[$type] = "<span class='u-colorSuccess' data-tooltip='" . Lang::get('torrents', 'slot_status_empty') . "'>" . icon('check') . "</span>";
                        break;
                    case TorrentSlotGroupStatus::Full:
                        // 满的class
                        $SlotStatusClass[$type] = '';
                        $text[$type] = "<span class='u-colorWarning' title = '" . Lang::get('torrents', 'slot_status_full') . "'>" . icon('times') . "</span>";
                        break;
                    case TorrentSlotGroupStatus::Free:
                        // 有空位的class
                        $SlotStatusClass[$type] = '';
                        $SlotsTitle = '';
                        foreach ($misslots as $slot) {
                            $SlotsTitle .= "<div>&nbsp;&nbsp;" . Lang::get('torrents', TorrentSlot::slot_option_lang($slot)) . "</div>";
                        }
                        $title = Lang::get('torrents', 'slots_we_need');
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
                <td class="TableMissing-cellSnatches Table-cell"><?= $SnatchedCount ?></td>
                <td class="TableMissing-cellRequests Table-cell"><?= $RequestCount ?></td>
                <td class="TableMissing-cellSDEncode Table-cell <?= $SlotStatusClass[TorrentSlotGroup::SDEncode] ?>"><?= $text[TorrentSlotGroup::SDEncode] ?>
                </td>
                <td class="TableMissing-cellSDUntouched Table-cell <?= $SlotStatusClass[TorrentSlotGroup::SDUntouched] ?>"><?= $text[TorrentSlotGroup::SDUntouched] ?>
                </td>
                <td class="TableMissing-cellHDEncode Table-cell <?= $SlotStatusClass[TorrentSlotGroup::HDEncode] ?>"><?= $text[TorrentSlotGroup::HDEncode] ?>
                </td>
                <td class="TableMissing-cellHDUntouched Table-cell <?= $SlotStatusClass[TorrentSlotGroup::HDUntouched] ?>"><?= $text[TorrentSlotGroup::HDUntouched] ?></td>
                <td class="TableMissing-cellUHDEncode Table-cell <?= $SlotStatusClass[TorrentSlotGroup::UHDEncode] ?>"><?= $text[TorrentSlotGroup::UHDEncode] ?></td>
                <td class="TableMissing-cellUHDUntouched Table-cell <?= $SlotStatusClass[TorrentSlotGroup::UHDUntouched] ?>"><?= $text[TorrentSlotGroup::UHDUntouched] ?></td>
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
