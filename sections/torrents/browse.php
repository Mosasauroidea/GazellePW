<?
include(CONFIG['SERVER_ROOT'] . '/sections/torrents/functions.php');
include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

use Gazelle\Torrent\EditionInfo;
use Gazelle\Torrent\Language;
use Gazelle\Torrent\Region;
use Gazelle\Torrent\Subtitle;

$headlink = new class implements SortLink {
    function link($SortKey, $DefaultWay = 'desc') {
        global $OrderBy, $OrderWay;
        if ($SortKey == $OrderBy) {
            if ($OrderWay == 'desc') {
                $NewWay = 'asc';
            } else {
                $NewWay = 'desc';
            }
        } else {
            $NewWay = $DefaultWay;
        }
        return "torrents.php?order_way=$NewWay&amp;order_by=$SortKey&amp;" . Format::get_url(array('order_way', 'order_by'));
    }
};

if (!empty($_GET['searchstr']) || !empty($_GET['groupname'])) {
    if (!empty($_GET['searchstr'])) {
        $SearchInfo = $_GET['searchstr'];
    } else {
        $SearchInfo = $_GET['groupname'];
    }

    // Search by infohash
    if ($InfoHash = is_valid_torrenthash($SearchInfo)) {
        $InfoHash = db_string(pack('H*', $InfoHash));
        $DB->query("
			SELECT ID, GroupID
			FROM torrents
			WHERE info_hash = '$InfoHash'");
        if ($DB->has_results()) {
            list($ID, $GroupID) = $DB->next_record();
            header("Location: torrents.php?id=$GroupID&torrentid=$ID");
            die();
        }
    } else if ($IMDBID = is_valid_imdbid($SearchInfo)) {
        $DB->query("
			SELECT ID
			FROM torrents_group
			WHERE IMDBID = '$IMDBID'");
        if ($DB->has_results()) {
            list($GroupID) = $DB->next_record();
            header("Location: torrents.php?id=$GroupID");
            die();
        }
    } else if (!empty($_GET['groupname'])) {
        $DB->query("
			SELECT ID
			FROM torrents_group
			WHERE Name = '" . db_string($SearchInfo) . "' or SubName = '" . db_string($SearchInfo) . "'");
        if ($DB->has_results()) {
            list($GroupID) = $DB->next_record();
            header("Location: torrents.php?id=$GroupID");
            die();
        }
    }
}

// Setting default search options
if (!empty($_GET['setdefault'])) {
    $UnsetList = array('page', 'setdefault');
    $UnsetRegexp = '/(&|^)(' . implode('|', $UnsetList) . ')=.*?(&|$)/i';

    $DB->query("
		SELECT SiteOptions
		FROM users_info
		WHERE UserID = '" . db_string($LoggedUser['ID']) . "'");
    list($SiteOptions) = $DB->next_record(MYSQLI_NUM, false);
    $SiteOptions = unserialize_array($SiteOptions);
    $SiteOptions = array_merge(Users::default_site_options(), $SiteOptions);

    $SiteOptions['DefaultSearch'] = preg_replace($UnsetRegexp, '', $_SERVER['QUERY_STRING']);
    $DB->query("
		UPDATE users_info
		SET SiteOptions = '" . db_string(serialize($SiteOptions)) . "'
		WHERE UserID = '" . db_string($LoggedUser['ID']) . "'");
    $Cache->begin_transaction("user_info_heavy_$UserID");
    $Cache->update_row(false, array('DefaultSearch' => $SiteOptions['DefaultSearch']));
    $Cache->commit_transaction(0);

    // Clearing default search options
} elseif (!empty($_GET['cleardefault'])) {
    $DB->query("
		SELECT SiteOptions
		FROM users_info
		WHERE UserID = '" . db_string($LoggedUser['ID']) . "'");
    list($SiteOptions) = $DB->next_record(MYSQLI_NUM, false);
    $SiteOptions = unserialize_array($SiteOptions);
    $SiteOptions['DefaultSearch'] = '';
    $DB->query("
		UPDATE users_info
		SET SiteOptions = '" . db_string(serialize($SiteOptions)) . "'
		WHERE UserID = '" . db_string($LoggedUser['ID']) . "'");
    $Cache->begin_transaction("user_info_heavy_$UserID");
    $Cache->update_row(false, array('DefaultSearch' => ''));
    $Cache->commit_transaction(0);

    // Use default search options
} elseif (empty($_SERVER['QUERY_STRING']) || (count($_GET) === 1 && isset($_GET['page']))) {
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
// Terms were not submitted via the search form
if (isset($_GET['searchsubmit'])) {
    $GroupResults = !empty($_GET['group_results']);
} else {
    $GroupResults = empty($LoggedUser['DisableGrouping2']);
}

if (!empty($_GET['order_way']) && $_GET['order_way'] == 'asc') {
    $OrderWay = 'asc';
} else {
    $OrderWay = 'desc';
}

if (empty($_GET['order_by']) || !isset(TorrentSearch::$SortOrders[$_GET['order_by']])) {
    $OrderBy = 'time'; // For header links
} else {
    $OrderBy = $_GET['order_by'];
}

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

$HideFilter = isset($LoggedUser['ShowTorFilter']) && $LoggedUser['ShowTorFilter'] == 0;
// This is kinda ugly, but the enormous if paragraph was really hard to read
$AdvancedSearch = !empty($_GET['action']) && $_GET['action'] == 'advanced';
$AdvancedSearch |= !empty($LoggedUser['SearchType']) && (empty($_GET['action']) || $_GET['action'] == 'advanced');
$AdvancedSearch &= check_perms('site_advanced_search');
if ($AdvancedSearch) {
    $Action = 'action=advanced';
    $HideBasic = ' u-hidden';
    $HideAdvanced = '';
} else {
    $Action = 'action=basic';
    $HideBasic = '';
    $HideAdvanced = ' u-hidden';
}
$GenreTags = Tags::get_genre_tag();
$CheckAllTorrents = check_perms('torrents_check') && !G::$LoggedUser['DisableCheckAll'];
if ($CheckAllTorrents) {
    G::$DB->query("select count(*) from torrents where Checked=0");
    list($AllUncheckedCnt) = G::$DB->next_record();
}


View::show_header(t('server.torrents.header'), 'browse', 'PageTorrentHome');
//$TimeNow = new DateTime();
//$TimeUntil = new DateTime('2016-12-16 03:50:00');
//$Interval = $TimeUntil->diff($TimeNow);
//$Left = $Interval->format("%i MINS, %s SECONDS");
?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <div class="BodyHeader-nav TorrentViewWrapper">
            <?= t('server.torrents.header') ?>
            <?
            renderTorrentViewButton(TorrentViewScene::TorrentBrowse);
            ?>
        </div>
        <form class="Form SearchPage Box SearchTorrent" name="torrents" method="get" action="" onsubmit="$(this).disableUnset();">
            <div class="SearchPageHeader">
                <div class="SearchPageHeader-title">
                    <span class="is-basicText <?= $HideBasic ?>"><?= t('server.torrents.base') ?> /</span>
                    <span class="is-basicLink <?= $HideAdvanced ?>"><a href="#" onclick="globalapp.toggleSearchTorrentAdvanced(event, 'basic')"><?= t('server.torrents.base') ?></a> /</span>
                    <span class="is-advancedText <?= $HideAdvanced ?>"><?= t('server.torrents.advanced') ?></span>
                    <span class="is-advancedLink <?= $HideBasic ?>"><a href="#" onclick="globalapp.toggleSearchTorrentAdvanced(event, 'advanced')"><?= t('server.torrents.advanced') ?></a></span>
                    <?= t('server.torrents.search') ?>
                    <a href="wiki.php?action=article&name=%E9%AB%98%E7%BA%A7%E6%90%9C%E7%B4%A2%E6%8C%87%E5%8D%97" target="_blank" data-tooltip="<?= t('server.torrents.guide_of_advanced_search') ?>">[?]</a>
                </div>
                <div class="SearchPageHeader-actions">
                    <a href="#" onclick="globalapp.toggleAny(event, '.SearchPageBody, .SearchPageFooter');" id="ft_toggle" class="brackets">
                        <span class="u-toggleAny-show <?= $HideFilter ? '' : 'u-hidden' ?>"><?= t('server.common.show') ?></span>
                        <span class="u-toggleAny-hide <?= $HideFilter ? 'u-hidden' : '' ?>"><?= t('server.common.hide') ?></span>
                    </a>
                </div>
            </div>
            <div class="SearchPageBody <?= $HideFilter ? ' u-hidden' : '' ?>">
                <table class="Form-rowList">
                    <tr class="Form-row is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.torrents.basic') ?>:</td>
                        <td class="Form-inputs is-splitEven">
                            <input class="is-movieName Input" type="text" spellcheck="false" size="40" name="groupname" placeholder="<?= t('server.common.movie_name_title') ?>" value="<? Format::form('groupname') ?>" />
                            <input class="is-artist Input" type="text" spellcheck="false" size="40" name="artistname" placeholder="<?= t('server.common.artist') ?>" value="<? Format::form('artistname') ?>" />
                            <input class="is-year Input" type="text" spellcheck="false" size="40" name="year" placeholder="<?= t('server.common.year') ?>" value="<? Format::form('year') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-language is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.common.language') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" id="language_infos" onchange="globalapp.addTorrentItem('language', 'language_infos'); return false;">
                                <option class="Select-option" value="">--</option>
                                <? foreach (Language::allItem() as $Key) { ?>
                                    <option class="Select-option" value="<?= $Key ?>"><?= Language::text($Key)  ?></option>
                                <? } ?>
                            </select>
                            <input class="Input" type="text" spellcheck="false" size="40" id="language" name="language" placeholder="<?= t('server.common.comma_separated') ?>" value="<? Format::form('language') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-subtitle is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.common.subtitle') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" id="subtitle_infos" onchange="globalapp.addTorrentItem('subtitles', 'subtitle_infos'); return false;">
                                <option class="Select-option" value="">--</option>
                                <? foreach (Subtitle::allItem() as $Key) { ?>
                                    <option class="Select-option" value="<?= $Key ?>"><?= Subtitle::text($Key)  ?></option>
                                <? } ?>
                            </select>
                            <input class="Input" type="text" spellcheck="false" size="40" id="subtitles" name="subtitles" placeholder="<?= t('server.common.comma_separated') ?>" value="<? Format::form('subtitles') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-region is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.common.countries_and_regions') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" id="region_infos" onchange="globalapp.addTorrentItem('region', 'region_infos'); return false;">
                                <option class="Select-option" value="">--</option>
                                <? foreach (Region::allItem() as $Key) { ?>
                                    <option class="Select-option" value="<?= $Key ?>"><?= Region::text($Key)  ?></option>
                                <? } ?>
                            </select>
                            <input class="Input" type="text" spellcheck="false" size="40" id="region" name="region" placeholder="<?= t('server.common.comma_separated') ?>" value="<? Format::form('region') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-rating is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.common.rating') ?>:</td>
                        <td class="Form-inputs is-splitEven" colspan="3">
                            <input class="Input" type="text" spellcheck="false" size="40" name="imdbrating" placeholder="<?= t('server.common.imdb_rating') ?>" value="<? Format::form('imdbrating') ?>" />
                            <input class="Input" type="text" spellcheck="false" size="40" name="doubanrating" placeholder="<?= t('server.common.douban_rating') ?>" value="<? Format::form('doubanrating') ?>" />
                            <input class="Input" type="text" spellcheck="false" size="40" name="rtrating" placeholder="<?= t('server.common.rt_rating') ?>" value="<? Format::form('rtrating') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-editionInfo is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.common.edition_info') ?>:</td>
                        <td class="Form-inputs" colspan="3">
                            <select class="Input" id="edition_infos" onchange="globalapp.addTorrentItem('remtitle', 'edition_infos'); return false;">
                                <option class="Select-option" value="">--</option>
                                <? foreach (EditionInfo::allEditionKey() as $Key) { ?>
                                    <option class="Select-option" value="<?= $Key ?>"><?= EditionInfo::text($Key)  ?></option>
                                <? } ?>
                            </select>
                            <input class="Input" type="text" spellcheck="false" size="40" id="remtitle" name="remtitle" value="<? Format::form('remtitle') ?>" placeholder="<?= t('server.common.comma_separated_edition') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-fileList is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.torrents.ft_filelist') ?>:</td>
                        <td class="Form-inputs">
                            <input class="Input" type="text" spellcheck="false" size="40" name="filelist" value="<? Format::form('filelist') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-ripSpecifics is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.torrents.ft_ripspecifics') ?>:</td>
                        <td class="Form-items">
                            <div class="Form-inputs">
                                <select class="Input" name="releasetype">
                                    <option class="Select-option" value=""><?= t('server.torrents.ft_releasetype') ?></option>
                                    <? foreach ($ReleaseTypes as $ID) { ?>
                                        <option class="Select-option" value="<?= display_str($ID); ?>" <? Format::selected('releasetype', $ID) ?>><?= display_str(t('server.torrents.release_types')[$ID]); ?></option>
                                    <?  } ?>
                                </select>
                                <select class="Input" name="freetorrent">
                                    <option class="Select-option" value=""><?= t('server.tools.sales_promotion_plan') ?></option>
                                    <option class="Select-option" value='<?= Torrents::FREE ?>' <? $HideBasic ? Format::selected('freetorrent', Torrents::FREE) : '' ?>><?= t('server.torrents.freeleech') ?></option>
                                    <option class="Select-option" value='<?= Torrents::OneFourthOff ?>' <? Format::selected('freetorrent', Torrents::OneFourthOff) ?>><?= t('server.torrents.off25') ?></option>
                                    <option class="Select-option" value='<?= Torrents::TwoFourthOff ?>' <? Format::selected('freetorrent', Torrents::TwoFourthOff) ?>><?= t('server.torrents.off50') ?></option>
                                    <option class="Select-option" value='<?= Torrents::ThreeFourthOff ?>' <? Format::selected('freetorrent', Torrents::ThreeFourthOff) ?>><?= t('server.torrents.off75') ?></option>
                                    <option class="Select-option" value='<?= Torrents::Neutral ?>' <? Format::selected('freetorrent', Torrents::Neutral) ?>><?= t('server.torrents.neutral_leech') ?></option>
                                </select>
                                <?
                                if ($CheckAllTorrents) {
                                ?>
                                    <select class="Input" name="checked">
                                        <option class="Select-option" value=""><?= t('server.torrents.checking_status') ?></option>
                                        <option class="Select-option" value='1' <? Format::selected('checked', '1') ?>><?= t('server.torrents.checked_torrents') ?></option>
                                        <option class="Select-option" value='0' <? Format::selected('checked', '0') ?>><?= t('server.torrents.unchecked_torrents') ?></option>
                                    </select>
                                <?
                                }
                                ?>
                            </div>
                            <div class="Form-inputs">
                                <select class="Input" id="source" name="source">
                                    <option class="Select-option" value=""><?= t('server.torrents.source') ?></option>
                                    <? foreach ($Sources as $SourceName) { ?>
                                        <option class="Select-option" value="<?= display_str($SourceName); ?>" <? Format::selected('source', $SourceName) ?>><?= display_str($SourceName); ?></option>
                                    <?  } ?>
                                </select>
                                <select class="Input" name="codec">
                                    <option class="Select-option" value=""><?= t('server.torrents.codec') ?></option>
                                    <? foreach ($Codecs as $CodecName) { ?>
                                        <option class="Select-option" value="<?= display_str($CodecName); ?>" <? Format::selected('codec', $CodecName) ?>><?= display_str($CodecName); ?></option>
                                    <?  } ?>
                                </select>
                                <select class="Input" name="container">
                                    <option class="Select-option" value=""><?= t('server.torrents.container') ?></option>
                                    <? foreach ($Containers as $ContainerName) { ?>
                                        <option class="Select-option" value="<?= display_str($ContainerName); ?>" <? Format::selected('container', $ContainerName) ?>><?= display_str($ContainerName); ?></option>
                                    <?  } ?>
                                </select>
                                <select class="Input" name="resolution">
                                    <option class="Select-option" value=""><?= t('server.torrents.resolution') ?></option>
                                    <? foreach ($Resolutions as $ResolutionName) { ?>
                                        <option class="Select-option" value="<?= display_str($ResolutionName); ?>" <? Format::selected('resolution', $ResolutionName) ?>><?= display_str($ResolutionName); ?></option>
                                    <?  } ?>
                                </select>
                                <select class="Input" name="processing">
                                    <option class="Select-option" value=""><?= t('server.torrents.processing') ?></option>
                                    <? foreach ($Processings as $ProcessingName) { ?>
                                        <option class="Select-option" value="<?= display_str($ProcessingName); ?>" <? Format::selected('processing', $ProcessingName) ?>><?= display_str($ProcessingName); ?></option>
                                    <?  } ?>
                                </select>
                            </div>

                        </td>
                    </tr>
                    <tr class="Form-row is-special is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><?= t('server.torrents.feature') ?>:</td>
                        <td class="Form-inputs">
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" value="1" name="chinesedubbed" <? $HideBasic ? Format::checked('chinesedubbed', 1) : '' ?> id="chinesedubbed" />
                                <label class="Radio-label" for="chinesedubbed"> <?= t('server.upload.chinese_dubbed_label') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" value="1" name="specialsub" <? $HideBasic ? Format::checked('specialsub', 1) : '' ?> id="specialsub" />
                                <label class="Radio-label" for="specialsub"> <?= t('server.tools.special_effects_subtitles') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" value="2" name="diy" <? $HideBasic ? Format::checked('diy', 2) : '' ?> id="self_rip" />
                                <label class="Radio-label" for="self_rip"> <?= t('server.upload.self_rip') ?></label>
                            </div>
                            <div class="Checkbox">
                                <input class="Input" type="checkbox" value="2" name="buy" <? $HideBasic ? Format::checked('buy', 2) : '' ?> id="buy" />
                                <label class="Radio-label" for="buy"> <?= t('server.torrents.buy') ?></label>
                            </div>


                        </td>

                    </tr>

                    <tr class="Form-row is-searchStr is-basic <?= $HideBasic ?>">
                        <td class="Form-label"><?= t('server.torrents.ftb_searchstr') ?>:</td>
                        <td class="Form-inputs ftb_searchstr">
                            <input class="Input" type="text" spellcheck="false" size="40" name="searchstr" value="<? Format::form('searchstr') ?>" />
                        </td>
                    </tr>
                    <tr class="Form-row is-tagFilter is-advanced <?= $HideAdvanced ?>">
                        <td class="Form-label"><span data-tooltip="<?= t('server.common.tags') ?>"><?= t('server.common.tags') ?>:</span></td>
                        <td class="Form-inputs">
                            <select class="Input" id="genre_tags" name="genre_tags" onchange="globalapp.addTorrentItem('tags', 'genre_tags'); return false;">
                                <option class="Select-option" value="">--</option>
                                <? foreach (Misc::display_array($GenreTags) as $Genre) { ?>
                                    <option class="Select-option" value="<?= $Genre ?>"><?= $Genre ?></option>
                                <? } ?>
                            </select>
                            <input class="Input" type="text" placeholder="<?= t('server.common.comma_separated') ?>" size="40" id="tags" name="taglist" value="<?= Format::form('taglist') ?>" />
                            <div class="RadioGroup">
                                <div class="Radio">
                                    <input class="Input" type="radio" name="tags_type" id="tags_type0" value="0" <? Format::selected('tags_type', 0, 'checked') ?> />
                                    <label class="Radio-label" for="tags_type0"> <?= t('server.torrents.any') ?></label>
                                </div>
                                <div class="Radio">
                                    <input class="Input" type="radio" name="tags_type" id="tags_type1" value="1" <? Format::selected('tags_type', 1, 'checked') ?> />
                                    <label class="Radio-label" for="tags_type1"> <?= t('server.torrents.all') ?></label>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr class="Form-row is-order">
                        <td class="Form-label"><?= t('server.torrents.ft_order') ?>:</td>
                        <td class="Form-inputs">
                            <select class="Input" name="order_by">
                                <option class="Select-option" value="time" <? Format::selected('order_by', 'time') ?>><?= t('server.torrents.add_time') ?></option>
                                <option class="Select-option" value="year" <? Format::selected('order_by', 'year') ?>><?= t('server.torrents.year') ?></option>
                                <option class="Select-option" value="size" <? Format::selected('order_by', 'size') ?>><?= t('server.common.size') ?></option>
                                <option class="Select-option" value="snatched" <? Format::selected('order_by', 'snatched') ?>><?= t('server.common.snatched') ?></option>
                                <option class="Select-option" value="seeders" <? Format::selected('order_by', 'seeders') ?>><?= t('server.common.seeders') ?></option>
                                <option class="Select-option" value="leechers" <? Format::selected('order_by', 'leechers') ?>><?= t('server.common.leechers') ?></option>
                                <option class="Select-option" value="doubanrating" <? Format::selected('order_by', 'doubanrating') ?>><?= t('server.common.douban_rating') ?></option>
                                <option class="Select-option" value="imdbrating" <? Format::selected('order_by', 'imdbrating') ?>><?= t('server.common.imdb_rating') ?></option>
                                <option class="Select-option" value="rtrating" <? Format::selected('order_by', 'rtrating') ?>><?= t('server.common.rt_rating') ?></option>
                                <option class="Select-option" value="random" <? Format::selected('order_by', 'random') ?>><?= t('server.torrents.random') ?></option>
                            </select>
                            <select class="Input" name="order_way">
                                <option class="Select-option" value="desc" <? Format::selected('order_way', 'desc') ?>><?= t('server.torrents.desc') ?></option>
                                <option class="Select-option" value="asc" <? Format::selected('order_way', 'asc') ?>><?= t('server.torrents.asc') ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="Form-row">
                        <td class="Form-label">
                        </td>
                        <td class="Form-inputs">
                            <div class="Checkbox is-freeTorrent <?= $HideBasic ?>">
                                <input class="Input" type="checkbox" value="1" name="freetorrent" <? $HideAdvanced ? Format::checked('freetorrent', '1') : '' ?> id="shows_free" />
                                <label for="shows_free"><?= t('server.torrents.only_shows_free_torrents') ?></label>
                            </div>
                            <div class="Checkbox is-groupResults">
                                <input class="Input" type="checkbox" value="1" name="group_results" id="group_results" <?= $GroupResults ? ' checked="checked"' : '' ?> />
                                <label for="group_results"><?= t('server.torrents.group_results') ?></label>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="SearchPageFooter <?= $HideFilter ? ' u-hidden' : '' ?>">
                <div class="SearchPageFooter-resultCount">
                    <?= number_format($RealNumResults) ?> <?= t('server.torrents.space_results') ?>
                    <?= !check_perms('site_search_many') ? "(" . t('server.torrents.showing_first_n_matches', ['Values' => [$NumResults]]) . ")" : "" ?>
                    <?
                    if ($CheckAllTorrents) {
                    ?>
                        <span><?= t('server.torrents.unchecked_torrents_results_note', ['Values' => [$AllUncheckedCnt]]) ?></span>
                    <? }
                    ?>

                </div>
                <div class="SearchPageFooter-actions">
                    <input class="Button" type="submit" value="<?= t('server.torrents.search_torrents') ?>" />
                    <input class="is-inputAction" type="hidden" name="action" id="ft_type" value="<?= ($AdvancedSearch ? 'advanced' : 'basic') ?>" />
                    <input type="hidden" name="searchsubmit" value="1" />
                    <input class="Button" type="button" value="<?= t('server.torrents.reset') ?>" onclick="location.href = 'torrents.php<? if (isset($_GET['action']) && $_GET['action'] === 'advanced') { ?>?action=advanced<? } ?>'" />
                    <? if ($Search->has_filters()) { ?>
                        <input class="Button" type="submit" name="setdefault" value="<?= t('server.torrents.setdefault') ?>" />
                    <?
                    }
                    if (!empty($LoggedUser['DefaultSearch'])) {
                    ?>
                        <input class="Button" type="submit" name="cleardefault" value="<?= t('server.torrents.cleardefault') ?>" />
                    <?  } ?>
                </div>
            </div>
        </form>
        <?
        if ($NumResults == 0) {
            $text1 = t('server.torrents.search_empty_1');
            $text2 = t('server.torrents.search_empty_2');
            print <<<HTML
<div class="center">
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
<? View::pages($Pages) ?>
<?
if ($GroupResults || isset($GroupedCategories[$CategoryID - 1])) {
    $tableRender = newGroupTorrentView(TorrentViewScene::TorrentBrowse, $Groups);
    $tableRender->with_new_tag(true)->with_check(true)->with_sort(true, $headlink)->with_year(true)->with_time(true)->render();
} else {
    $TorrentLists = [];
    foreach ($Results as $Key => $GroupID) {
        $TorrentLists[] = Torrents::convert_torrent($Groups[$GroupID], $Key);
    }
    $tableRender =  newUngroupTorrentView(TorrentViewScene::TorrentBrowse, $TorrentLists);
    $tableRender->with_new_tag(true)->with_check(true)->with_sort(true, $headlink)->with_year(true)->with_time(true)->render();
}
?>
<? View::pages($Pages) ?>
</div>
<?
View::show_footer();
