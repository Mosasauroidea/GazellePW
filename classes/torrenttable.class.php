<?

use Gazelle\API\Torrent;
use Gazelle\Torrent\Subtitle;

interface SortLink {
    public function link($SortKey, $DefaultWay = 'desc');
}

class TorrentGroupCoverTableView extends GroupTorrentTableView {
    /* { UseTorrentID => false } */
    public function render($options = []) {
        $Class = $options['class'];
        $Variant = $options['Variant'];
?>
        <div class="TorrentCover u-hideScrollbar <?= $Class ?>" variant="<?= $Variant ?>">
            <?
            foreach ($this->Groups as $RS) {
                $Name = Torrents::group_name($RS, false);
                $QueryString = $options['UseTorrentID'] ? "torrentid=" . $RS['TorrentID'] : "id=" . $RS['ID'];
            ?>
                <a class="TorrentCover-item" href="torrents.php?<?= $QueryString ?>">
                    <div class="TorrentCover-imageContainer">
                        <img class="TorrentCover-image" src="<?= ImageTools::process($RS['WikiImage'], false) ?>" />
                    </div>
                    <b><?= display_str($Name) ?></b>
                </a>
            <? } ?>
        </div>
    <?
    }
}

class UngroupTorrentSimpleListView extends UngroupTorrentTableView {
    public function __construct($Torrents) {
        $this->Torrents = $Torrents;
        parent::__construct($Torrents);
        parent::with_cover(false);
        parent::with_time(true);
    }
    public function with_cover($Bool): ?TorrentTableView {
        $this->WithCover = false;
        return $this;
    }

    public function render($Options = []) {
        $Options = array_merge([
            'NoActions' => false,
        ], $Options)
    ?>
        <div class="TableContainer UngroupTorrentSimpleListView">
            <? if (!empty($this->Torrents)) { ?>
                <table class="TableTorrent Table <?= $this->TableTorrentClass ?>" variant="ungroup" id="torrent_table">
                    <tr class="Table-rowHeader">
                        <?
                        $this->render_header();
                        foreach ($this->Torrents as $Idx => $Torrent) {
                            $this->render_torrent_info($Idx, $Options);
                        }
                        ?>
                    </tr>
                </table>
            <? } else { ?>
                <table>
                    <tr class="rowb">
                        <td colspan="7" class="center">
                            <?= t('server.top10.found_no_torrents_matching_the_criteria') ?>
                        </td>
                    </tr>
                </table>
            <? } ?>
        </div>
    <?
    }
    protected function render_torrent_info($Idx, $Options = []) {
        $Torrent = $this->Torrents[$Idx];
        $TorrentID = $Torrent['ID'];
        $Group = $Torrent['Group'];
        $GroupID = $Group['ID'];
        $SnatchedGroupClass = Torrents::parse_group_snatched($Group) ? ' snatched_group' : '';
        $TorrentID = $Torrent['ID'];
        $SnatchedTorrentClass = $Torrent['IsSnatched'] ? ' snatched_torrent' : '';
        global $LoggedUser;
        $ColCount = 6;
        if (!$this->WithTime) {
            $ColCount -= 1;
        }
    ?>
        <? /* UngroupTorrentSimpleListView */ ?>
        <tr class="TableTorrent-rowTitle Table-row u-tableTorrent-rowTitle <?= $SnatchedGroupClass ?>" group-id="<?= $GroupID ?>" torrent-id="<?= $TorrentID ?>">
            <? if ($this->WithNumber) { ?>
                <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoNo" style="padding: 8px; text-align: center;" class="td_rank m_td_left"><strong><?= $Idx + 1 ?></strong></td>
            <? }
            if (!empty($this->FilterID)) { ?>
                <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoCheckbox" style="text-align: center;">
                    <input type="checkbox" class="notify_box notify_box_<?= $this->FilterID ?>" value="<?= $TorrentID ?>" id="clear_<?= $TorrentID ?>" tabindex="1" />
                </td>
            <? } ?>
            <td class="Table-cell">
                <div class="TableTorrent-title">
                    <? if (!$Options['NoActions']) { ?>
                        <span class="TableTorrent-titleActions">
                            [
                            <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="Download">DL</a>
                            <? if (Torrents::can_use_token($Torrent)) { ?>
                                |
                                <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="Use a FL Token" onclick="return confirm('<?= FL_confirmation_msg($Torrent['Seeders'], $Torrent['Size']) ?>');">FL</a>
                            <? } ?>
                            |
                            <a href="torrents.php?torrentid=<?= $TorrentID ?>" data-tooltip="<?= t('server.torrents.permalink') ?>">PL</a>
                            ]
                        </span>
                    <? } ?>
                    <? if (isset($this->DetailView)) { ?>
                        <a class="<?= $SnatchedTorrentClass ?>" href="#" onclick="globalapp.toggleTorrentDetail(event, '#torrent_<?= $this->DetailView ?>_<?= $TorrentID ?>')">
                            <?= Torrents::torrent_simple_view($Group, $Torrent, false, [
                                'Self' => $this->WithSelf,
                                'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
                            ]) ?>
                        </a>
                    <? } else { ?>
                        <?= Torrents::torrent_simple_view(
                            $Group,
                            $Torrent,
                            true,
                            [
                                'Class' => $SnatchedTorrentClass,
                                'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle'],
                            ]
                        ) ?>
                    <? } ?>

                </div>
            </td>
            <? if ($this->WithTime) { ?>
                <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatTime">
                    <?= time_diff($Torrent['Time'], 1) ?>
                </td>
            <? } ?>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSize">
                <?= Format::get_size($Torrent['Size']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSnatches">
                <?= number_format($Torrent['Snatched']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSeeders <?= (($Torrent['Seeders'] == 0) ? ' u-colorRatio00' : '') ?>">
                <?= number_format($Torrent['Seeders']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatLeechers">
                <?= number_format($Torrent['Leechers']) ?>
            </td>
        </tr>
        <?
        if (isset($this->DetailView)) {
            $Expand = $this->DetailOption->Expand;
        ?>
            <tr class="TableTorrent-rowDetail u-toggleEdition-alwaysHidden Table-row releases_<?= $Group['ReleaseType'] ?>  <? if (!$Expand) { ?>u-hidden<? } ?>" id="torrent_<?= $this->DetailView ?>_<?= $TorrentID; ?>" group-id="<?= $GroupID ?>">
                <td class="TableTorrent-cellDetail Table-cell" colspan="<?= $ColCount ?>">
                    <? $this->render_torrent_detail($Torrent['Group'], $Torrent, null, null, false); ?>
                </td>
            </tr>
        <? } ?>
    <?
    }
}

class DetailOption {
    public $ThumbCounts = null;
    public $BonusSended = null;
    public $WithReport = true;
    public $ReadOnly = false;
    public $Expand = false;
}

class TorrentTableView {
    protected $WithTime = false;
    protected $WithNumber = false;
    protected $WithCheck = false;
    protected $WithSort = false;
    protected $WithCover = true;
    protected $FilterID = 0;
    protected $WithVote = false;
    protected $WithYear = false;
    protected $WithSelf = true;
    protected $DetailView = null;

    protected $CheckAllTorrents;
    protected $CheckSelfTorrents;
    protected $AllUncheckedCnt = 0;
    protected $PageUncheckedCnt = 0;
    /**
     * @var DetailOption $DetailOption
     */
    protected $DetailOption;

    protected $HeadLinkFunc = null;

    public function __construct() {
        if (G::$LoggedUser['CoverArt']) {
            $this->WithCover = true;
        } else {
            $this->WithCover = false;
        }
        $this->TableTorrentClass = G::$LoggedUser['SettingTorrentTitle']['Alternative'] ? 'is-alternative' : '';
    }
    public function with_detail($View = '', DetailOption $DetailOption = null): ?TorrentTableView {
        $this->DetailView = $View;
        $this->DetailOption = $DetailOption;
        return $this;
    }
    public function with_self($Bool): ?TorrentTableView {
        $this->WithSelf = $Bool;
        return $this;
    }
    public function with_year($Bool): ?TorrentTableView {
        $this->WithYear = $Bool;
        return $this;
    }
    public function with_cover($Bool): ?TorrentTableView {
        $this->WithCover = $Bool;
        return $this;
    }
    public function with_number($Bool): ?TorrentTableView {
        $this->WithNumber = $Bool;
        return $this;
    }
    public function with_check($Bool): ?TorrentTableView {
        $this->WithCheck = $Bool;
        return $this;
    }
    public function with_sort($Bool, SortLink $HeadLinkFunc): ?TorrentTableView {
        $this->WithSort = $Bool;
        $this->HeadLinkFunc = $HeadLinkFunc;
        return $this;
    }
    public function with_time($Bool): ?TorrentTableView {
        $this->WithTime = $Bool;
        return $this;
    }

    public function with_filter_id($ID): ?TorrentTableView {
        $this->FilterID = $ID;
        return $this;
    }

    public function with_vote($Bool): ?TorrentTableView {
        $this->WithVote = $Bool;
        return $this;
    }
    public function render() {
    }

    protected function header_elem($Name, $Sort, $SortKey = "") {
        if ($Sort) {
            return "<a href='" . $this->HeadLinkFunc->link($SortKey, $Sort) . "'> " . $Name . "</a>";
        }
        return "<i>" . $Name . "</i>";
    }
    public function render_torrent_detail($Group, $Torrent) {
        $ReadOnly = $this->DetailOption->ReadOnly;
        $ThumbCounts = $this->DetailOption->ThumbCounts[$Torrent['ID']];
        $BonusSended = $this->DetailOption->BonusSended[$Torrent['ID']];
        $ShowReport = $this->DetailOption->WithReport;
        $GroupID = $Group['ID'];
        $GroupCategoryID = $Group['CategoryID'];
        $TorrentID = $Torrent['ID'];
        $Seeders = $Torrent['Seeders'];
        $TorrentTime = $Torrent['Time'];
        $Subtitles = $Torrent['Subtitles'];
        $ExternalSubtitles = $Torrent['ExternalSubtitles'];
        $ExternalSubtitleIDs = $Torrent['ExternalSubtitleIDs'];
        $Description = $Torrent['Description'];
        $MediaInfos = $Torrent['MediaInfo'];
        $Note = $Torrent['Note'];
        $SubtitleType = $Torrent['SubtitleType'];
        $LastReseedRequest = $Torrent['LastReseedRequest'];
        $UserID = $Torrent['UserID'];
        $BadFolders = $Torrent['BadFolders'];
        $BadFiles = $Torrent['BadFiles'];
        $NoSub = $Torrent['NoSub'];
        $HardSub = $Torrent['HardSub'];
        $LastActive = $Torrent['last_action'];
        $CustomTrumpable = $Torrent['CustomTrumpable'];
        $Dead = Torrents::is_torrent_dead($Torrent);
        $Reported = !empty($Torrent['ReportID']);
        $TrumpableMsg = '';
        $TrumpableAddExtra = '';


        if (!empty($BadFolders)) {
            $TrumpableMsg .= $TrumpableAddExtra . t('server.torrents.bad_filename');
            $TrumpableAddExtra = ' / ';
        }

        if (!empty($BadFiles)) {
            $TrumpableMsg .= $TrumpableAddExtra . t('server.torrents.bad_files');
            $TrumpableAddExtra = ' / ';
        }
        if (!empty($NoSub)) {
            $TrumpableMsg .= $TrumpableAddExtra . t('server.upload.no_sub');
            $TrumpableAddExtra = ' / ';
        }
        if (!empty($HardSub)) {
            $TrumpableMsg .= $TrumpableAddExtra . t('server.upload.hardcode_sub');
            $TrumpableAddExtra = ' / ';
        }
        if (!empty($CustomTrumpable)) {
            $TrumpableMsg .= $TrumpableAddExtra . $CustomTrumpable;
            $TrumpableAddExtra = ' / ';
        }
        if ($Dead) {
            $TrumpableMsg .= $TrumpableAddExtra . t('server.upload.dead_torrent');
            $TrumpableAddExtra = ' / ';
        }
    ?>

        <div class="TorrentDetail">
            <div class="TorrentDetail-row is-viewActionsContainer">
                <div class="ButtonGroup TorrentDetail-postMessageList">
                    <?
                    if (
                        !$ReadOnly &&
                        (($Seeders == 0  &&
                            $LastActive != '0000-00-00 00:00:00' &&
                            time() - strtotime($LastActive) >= 345678 &&
                            time() - strtotime($LastReseedRequest) >= 864000) ||
                            check_perms('users_mod'))
                    ) {
                    ?><a href="torrents.php?action=reseed&amp;torrentid=<?= $TorrentID ?>&amp;groupid=<?= $GroupID ?>" class="brackets" onclick="return confirm('<?= t('server.torrents.request_re_seed_confirm') ?>');"><?= t('server.torrents.request_re_seed') ?></a>
                    <?
                    } ?>
                    <? if (check_perms('site_moderate_requests')) { ?>
                        <span class="is-massPM">
                            <a class="Link" href="torrents.php?action=masspm&amp;id=<?= $GroupID ?>&amp;torrentid=<?= $TorrentID ?>">
                                <?= t('server.torrents.masspm') ?>
                            </a>
                        </span>
                    <?
                    } ?>
                </div>
                <div class="TorrentDetail-links is-viewActions">
                    <? if (!$ReadOnly) { ?>
                        <a class="Link" href="#" onclick="show_peers('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;"><?= t('server.torrents.view_peer_list') ?></a>
                        <? if (check_perms('site_view_torrent_snatchlist')) { ?>
                            <a class="Link" href="#" onclick="show_downloads('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;" data-tooltip="<?= t('server.torrents.show_downloads_title') ?>"><?= t('server.torrents.view_download_list') ?></a>
                            <a class="Link" href="#" onclick="show_snatches('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;" data-tooltip="<?= t('server.torrents.show_snatches_title') ?>"><?= t('server.torrents.view_snatch_list') ?></a>
                        <?  } ?>
                        <a class="Link" href="#" onclick="show_giver('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;"><?= t('server.torrents.giver_list') ?></a>
                    <?  } ?>
                    <a class="Link" href="#" onclick="show_files('<?= $TorrentID ?>', '<?= $this->DetailView ?>'); return false;"><?= t('server.torrents.view_file_list') ?></a>
                    <? if ($Reported) { ?>
                        <a class="Link" href="#" onclick="show_reported('<?= $TorrentID ?>','<?= $this->DetailView ?>'); return false;"><?= t('server.torrents.view_report_information') ?></a>
                    <?  } ?>
                </div>
                <div class="TorrentDetail-giverList hidden" id="<?= $this->DetailView ?>_giver_<?= $TorrentID ?>"></div>
                <div class="TorrentDetail-peerList hidden" id="<?= $this->DetailView ?>_peers_<?= $TorrentID ?>"></div>
                <div class="TorrntDetail-downloadList hidden" id="<?= $this->DetailView ?>_downloads_<?= $TorrentID ?>"></div>
                <div class="TorrentDetail-snatchList hidden" id="<?= $this->DetailView ?>_snatches_<?= $TorrentID ?>"></div>
                <div class="TorrentDetail-fileList hidden" id="<?= $this->DetailView ?>_files_<?= $TorrentID ?>"></div>
                <? if ($Reported) { ?>
                    <div class="TorrentDetail-reportedList hidden" id="<?= $this->DetailView ?>_reported_<?= $TorrentID ?>"></div>
                <?  } ?>
            </div>
            <div class="TorrentDetail-row is-uploadContainer is-block" id="release_<?= $TorrentID ?>">
                <div class="TorrentDetail-uploader">
                    <div class="TorrentDetail-uploaderInfo">
                        <span>
                            <?= t('server.torrents.upload_by', ['Values' => [
                                Users::format_username($UserID, false, false, false)
                            ]]) ?>
                        </span>
                        <?= time_diff($TorrentTime); ?>
                        <?
                        if ($Seeders == 0) {
                            // If the last time this was seeded was 50 years ago, most likely it has never been seeded, so don't bother
                            // displaying "Last active: 2000+ years" as that's dumb
                            if (time() - strtotime($LastActive) > 1576800000) {
                        ?>
                                <span>,&nbsp;</span>
                                <?= t('server.torrents.last_active') ?>:<?= t('server.torrents.never') ?>
                            <?
                            } elseif ($LastActive != '0000-00-00 00:00:00' && time() - strtotime($LastActive) >= 1209600) {
                            ?>
                                <span>,&nbsp;</span><strong><?= t('server.torrents.last_active') ?> <?= time_diff($LastActive); ?></strong>
                            <?
                            } else {
                            ?><span>,&nbsp;</span> <?= t('server.torrents.last_active') ?> <?= time_diff($LastActive); ?>
                        <?
                            }
                        }
                        ?>

                    </div>
                    <? if (!$ReadOnly) { ?>
                        <div class="TorrentDetail-likeContainer ButtonGroup ButtonGroup--wide">
                            <div class="TorrentDetail-reward is-total">
                                <span class="TorrentDetail-rewardButton" data-tooltip="<?= t('server.torrents.total_reward_bonus_points_pre_tax') ?>">
                                    <?= icon('bonus-active') ?>
                                </span>
                                <span data-tooltip="<?= t('server.torrents.total_reward_bonus_points_pre_tax') ?>" id="bonuscnt<?= $TorrentID ?>">
                                    <?= isset($BonusSended) && isset($BonusSended['Count']) && $BonusSended['Count'] > 0 ? $BonusSended['Count'] : '0' ?>
                                </span>
                            </div>
                            <div class="TorrentDetail-like">
                                <span id="thumb<?= $TorrentID ?>" <?= isset($ThumbCounts) && isset($ThumbCounts['on']) && $ThumbCounts['on'] > 0 ? 'style="display: none;"' : '' ?>>
                                    <? if (G::$LoggedUser['ID'] == $UserID) { ?>
                                        <i data-tooltip="<?= t('server.torrents.you_cant_like_yourself') ?>">
                                            <?= icon("Common/like") ?>
                                        </i>
                                    <?
                                    } else { ?>
                                        <a href="javascript:void(0);" onclick="thumb(<?= $TorrentID ?>, <?= $UserID ?>, 'torrent')">
                                            <?= icon("Common/like") ?>
                                        </a>
                                    <? } ?>
                                </span>
                                <span id="unthumb<?= $TorrentID ?>" <?= isset($ThumbCounts) && empty($ThumbCounts['on']) ? 'style="display: none;"' : '' ?>>
                                    <a href="javascript:void(0);" onclick="unthumb(<?= $TorrentID ?>, <?= $UserID ?>, 'torrent')">
                                        <?= icon("Common/like-solid") ?>
                                    </a>
                                </span>
                                <span id="thumbcnt<?= $TorrentID ?>">
                                    <?= isset($ThumbCounts) && isset($ThumbCounts['count']) ? $ThumbCounts['count'] : t('server.torrents.like') ?>
                                </span>
                            </div>
                        </div>
                    <? } ?>

                </div>
                <? if (!$ReadOnly) { ?>
                    <div class="TorrentDetail-ratioCalc">
                        <?
                        $NewRatio = Format::get_ratio_html(G::$LoggedUser['BytesUploaded'], G::$LoggedUser['BytesDownloaded'] + Torrents::get_actual_size($Torrent));
                        ?>
                        <?= t('server.torrents.if_you_download_this', ['Values' => [$NewRatio]]) ?>
                    </div>
                <? } ?>
                <? if ($TrumpableMsg) { ?>
                    <div class="TorrentDetail-trumpable">
                        <span class="TorrentDetail-trumpableTitle">
                            <?= t('server.torrents.trumpable_reason') ?>:
                        </span>
                        <span class="TorrentDetail-trumpableMessage">
                            <?= $TrumpableMsg ?>
                        </span>
                    </div>
                <? } ?>

                <?
                if (!$ReadOnly) {

                ?>
                    <div class="is-rewardContainer">
                        <div class="TorrentDetail-rewardList ButtonGroup" id="sendbonus_<?= $TorrentID ?>">
                            <? $Sended = isset($BonusSended) ? explode(',', $BonusSended['Sended']) : []; ?>
                            <?
                            global $TorrentBonus;
                            foreach ($TorrentBonus as $Bonus) {
                            ?>
                                <div class="TorrentDetail-reward">
                                    <span class="TorrentDetail-rewardButton is-active" data-tooltip="<?= G::$LoggedUser['ID'] == $UserID ? t('server.torrents.you_cant_reward_yourself') : t('server.torrents.you_have_rewarded') ?>" style="<?= in_array($Bonus, $Sended) || G::$LoggedUser['ID'] == $UserID ? "" : "display: none;" ?>" id="bonus<?= $Bonus ?><?= $TorrentID ?>">
                                        <?= icon('bonus-active') ?>
                                    </span>
                                    <a class="TorrentDetail-rewardButton is-toReward" data-tooltip="<?= t('server.torrents.reward_bonus_to_uploader', ['Values' => [$Bonus]]) ?>" style="<?= in_array($Bonus, $Sended) || G::$LoggedUser['ID'] == $UserID ? "display: none;" : "" ?>" id="abonus<?= $Bonus ?><?= $TorrentID ?>" href="javascript:void(0);" onclick="sendbonus(<?= $TorrentID ?>, <?= $Bonus ?>)">
                                        <?= icon('bonus-active') ?>
                                    </a>
                                    <span><?= $Bonus ?></span>
                                </div>
                            <?
                            }
                            ?>
                        </div>
                    </div>
                <? } ?>
            </div>
            <?
            if ($TrumpableMsg) { ?>

            <?
            } ?>


            <? if ($Note) { ?>
                <div class="TorrentDetail-row is-staffNote is-block">
                    <span class='u-colorWarning'><strong><?= t('server.upload.staff_note') ?>:</strong></span>
                    <?= Text::full_format($Note) ?>
                </div>
            <? } ?>

            <div class="TorrentDetail-row is-subtitle is-block TorrentDetailSubtitle" id="subtitles_box">
                <div class="TorrentDetailSubtitle-header" id="subtitles_box_header">
                    <strong class="TorrentDetailSubtitle-title" id="subtitles_box_title"><?= t('server.common.subtitles') ?>:

                    </strong>
                    <? if (!$Subtitles && !$ExternalSubtitleIDs) { ?>
                        <span class="TorrentDetailSubtitle-noSubtitle" data-tooltip="<?= t('server.upload.no_subtitles') ?>">
                            <?= Subtitle::icon(Subtitle::NoSubtitleitem) ?>
                        </span>
                    <? } ?>
                    <span class="floatright">
                        <? if (!$ReadOnly) { ?>
                            <a href="subtitles.php?action=upload&torrent_id=<?= $TorrentID ?>"><?= t('server.torrents.add_subtitles') ?></a>

                        <?  } ?>
                        <? if ($ExternalSubtitleIDs) { ?>
                            | <a class="Link" href="#" onclick="BrowseExternalSub(<?= $TorrentID ?>); return false;"><?= t('server.index.details') ?></a>
                        <? } ?>
                    </span>

                </div>
                <?
                if ($Subtitles) {

                    $SubtitleArray = explode(',', $Subtitles);
                ?>
                    <div class="TorrentDetailSubtitle-list is-internal" id="subtitles_box_in_torrent">
                        <span class="TorrentDetailSubtitle-listTitle"><?= $SubtitleType == 1 ? t('server.common.in_torrent_subtitles') : t('server.common.in_torrent_hard_subtitles'); ?>:</span>
                        <? foreach ($SubtitleArray as $Subtitle) { ?>
                            <span class="TorrentDetailSubtitle-listItem" data-tooltip="<?= t("server.upload.$Subtitle") ?>">
                                <?= icon("flag/$Subtitle") ?>
                            </span>
                        <? } ?>
                    </div>
                <?
                }
                if ($ExternalSubtitleIDs) {
                    $ExternalSubtitleIDArray = explode('|', $ExternalSubtitleIDs);
                    $ExternalSubtitleArray = explode('|', $ExternalSubtitles);
                ?>
                    <div class="TorrentDetailSubtitle-list is-external" id="subtitles_box_external">
                        <span class="TorrentDetailSubtitle-listTitle">
                            <?= t('server.common.external_subtitles') ?>:
                        </span>
                        <?
                        foreach ($ExternalSubtitleIDArray as $index => $ExternalSubtitleID) {
                            $SubtitleLanguages = $ExternalSubtitleArray[$index];
                            $SubtitleLanguagesArray = explode(',', $SubtitleLanguages);
                            if (in_array('chinese_simplified', $SubtitleLanguagesArray)) {
                        ?>
                                <a class="TorrentDetailSubtitle-listItem" href="subtitles.php?action=download&id= <?= $ExternalSubtitleID ?>" data-tooltip="<?= t('server.upload.chinese_simplified') ?>">
                                    <?= icon('flag/chinese_simplified') ?>
                                </a>
                            <?
                            } else if (in_array('chinese_traditional', $SubtitleLanguagesArray)) { ?>
                                <a class=" TorrentDetailSubtitle-listItem" href="subtitles.php?action=download&id=<?= $ExternalSubtitleID ?>" data-tooltip="<?= t('server.upload.chinese_traditional') ?>">
                                    <?= icon('flag/chinese_traditional') ?>
                                </a>
                            <?
                            } else if ($SubtitleLanguagesArray[0]) { ?>
                                <a class=" TorrentDetailSubtitle-listItem" href="subtitles.php?action=download&id=<?= $ExternalSubtitleID ?>" data-tooltip="<?= t("server.upload.${SubtitleLanguagesArray[0]}") ?>">
                                    <?= icon("flag/$SubtitleLanguagesArray[0]") ?>
                                </a>
                        <?
                            }
                        }
                        ?>

                    </div>
                    <div id="external_subtitle_container_<?= $TorrentID ?>" class="hidden"></div>
                <?  } ?>

            </div>


            <? if (!empty($MediaInfos)) { ?>
                <div class=" TorrentDetail-row is-mediainfo is-block">
                    <strong class="TorrentDetailSubtitle-title" id="subtitles_box_title"><?= t('server.torrents.media_info') ?>:</strong>
                    <?
                    $Index = 0;
                    $MediaInfoObj = json_decode($MediaInfos);
                    if (is_array($MediaInfoObj)) {
                        foreach ($MediaInfoObj as $MediaInfo) {
                            $MediaInfo = ltrim(trim($MediaInfo), '[mediainfo]');
                            $MediaInfo = ltrim(trim($MediaInfo), '[bdinfo]');
                            $MediaInfo = rtrim(trim($MediaInfo), '[/mediainfo]');
                            $MediaInfo = rtrim(trim($MediaInfo), '[/bdinfo]');
                            echo ($Index > 0 ? "<br>" : "") . Text::full_format('[mediainfo]' . $MediaInfo . '[/mediainfo]');
                            $Index++;
                        }
                    }
                    ?>
                </div>
            <? } ?>
            <? if (!empty($Description)) { ?>
                <div class="TorrentDetail-row is-description is-block">
                    <?= Text::full_format($Description) ?>
                </div>
            <? } ?>
        </div>
    <?
    }
    protected function render_group_name($GroupInfo) {
        $GroupID = $GroupInfo['ID'];
        $GroupName = Lang::choose_content($GroupInfo['Name'], $GroupInfo['SubName']);
        $SubName = Lang::choose_content($GroupInfo['SubName'], $GroupInfo['Name']);
        $GroupYear = $GroupInfo['Year'];
    ?>
        <span class="TableTorrent-movieInfoTitle">
            <a href="\torrents.php?id=<?= $GroupID ?>"><?= display_str($GroupName) ?></a>
            <span class="TableTorrent-movieInfoYear">(<? print_r($GroupYear) ?>)</span>
        </span>
        <? if (Bookmarks::has_bookmarked('torrent', $GroupID)) { ?>
            <span class="TableTorrent-movieInfoAction remove_bookmark ">
                <a href="#" id="bookmarklink_torrent_<?= $GroupID ?>" onclick="Unbookmark('torrent', <?= $GroupID ?>); return false;">
                    <svg class="remove-icon bookmark-active icon" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" fill="currentColor" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    <svg class="add-icon bookmark icon" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                </a>
            </span>
        <?  } else { ?>
            <span class="TableTorrent-movieInfoAction add_bookmark floatright">
                <a href="#" id="bookmarklink_torrent_<?= $GroupID ?>" onclick="Bookmark('torrent', <?= $GroupID ?>); return false;">
                    <svg class="remove-icon bookmark-active icon" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" fill="currentColor" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                    <svg class="add-icon bookmark icon" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z" fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
                    </svg>
                </a>
            </span>
        <?  }
        if ($this->WithVote && CONFIG['ENABLE_VOTES']) {
            $VoteType = isset($UserVotes[$GroupID]['Type']) ? $UserVotes[$GroupID]['Type'] : '';
            Votes::vote_link($GroupID, $VoteType);
        }
        ?>

        <div class="TableTorrent-movieInfoSubtitle">
            <? if ($SubName) {
                echo display_str($SubName);
            } ?>
        </div>
    <?
    }
    protected function render_movie_info($Group) {
        $Artists = $Group['Artists'];
        $Name = $Group['Name'];
        $Director = Artists::get_first_directors($Artists);
        $RTTitle = empty($Group['RTTitle']) ? str_replace([':', '"'], '', str_replace(' ', '_', strtolower($Name))) : $Group['RTTitle'];

    ?>
        <div class="TableTorrent-movieInfoFacts ">
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.common.imdb_rating') ?>, <?= $Group['IMDBVote'] . ' ' . t('server.torrents.movie_votes') ?>" target="_blank" href="https://www.imdb.com/title/<?= $Group['IMDBID'] ?>">
                <?= icon('imdb-gray') ?>
                <span><?= !empty($Group['IMDBRating']) ? sprintf("%.1f", $Group['IMDBRating']) : '--' ?></span>
            </a>
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.common.douban_rating') ?>, <?= ($Group['DoubanVote'] ? $Group['DoubanVote'] : '?') . ' ' . t('server.torrents.movie_votes') ?>" target="_blank" href="https://movie.douban.com/subject/<?= $Group['DoubanID'] ?>/">
                <?= icon('douban-gray') ?>
                <span><?= !empty($Group['DoubanRating']) ? sprintf("%.1f", $Group['DoubanRating']) : '--' ?></span>
            </a>
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.common.rt_rating') ?>" target="_blank" href="https://www.rottentomatoes.com/m/<?= $RTTitle ?>">
                <?= icon('rotten-tomatoes-gray') ?>
                <span><?= !empty($Group['RTRating']) ? $Group['RTRating'] : '--' ?></span>
            </a>
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.upload.director') ?>" href="/artist.php?id=<?= $Director['ArtistID'] ?>" dir="ltr">
                <?= icon('movie-director') ?>
                <span><?= Artists::display_artist($Director, false) ?></span>
            </a>
            <span class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.torrents.imdb_region') ?>">
                <?= icon('movie-country') ?>
                <span><?= Torrents::format_region($Group['Region'], 2) ?></span>
            </span>
            <span class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= t('server.upload.movie_type') ?>">
                <?= icon('movie-type') ?>
                <span><?= t('server.torrents.release_types')[$Group['ReleaseType']] ?></span>
            </span>
        </div>
    <?
    }

    protected function render_header() {
        // 折叠符号+封面+列
    ?>
        <?
        if ($this->WithNumber) {
        ?>
            <td class="Table-cell" width="40px"></td>
        <?
        }
        if (!empty($this->FilterID)) {
        ?>
            <td class="Table-cell" width="40px"></td>
        <?
        }
        if ($this->WithCover) {
        ?>
            <td class="Table-cell" width="110px"></td>
        <?
        }
        ?>
        <td class="Table-cell TableTorrent-cellHeaderUncheckedStatistic">
            <span><?= t('server.torrents.name') ?><?= ($this->WithYear ? ' /' . $this->header_elem(t('server.torrents.year'), true, 'year') : '') ?></span>
        </td>
        <?
        if ($this->WithTime) {
        ?>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatTime">
                <?= $this->header_elem('<span  aria-hidden="true" data-tooltip="' . t('server.torrents.time') . '">' . icon('torrent-time') . '</span>', $this->WithSort, 'time') ?>
            </td>
        <?
        }
        ?>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSize  ">
            <?= $this->header_elem('<span  aria-hidden="true" data-tooltip="' . t('server.common.size') . '">' . icon('torrent-size') . '</i>', $this->WithSort, 'size') ?>
        </td>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSnatches">
            <?= $this->header_elem('<i  aria-hidden="true" data-tooltip="' . t('server.common.snatched') . '">' . icon('torrent-snatches') . '</i>', $this->WithSort, 'snatched') ?>
        </td>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSeeders">
            <?= $this->header_elem('<i  aria-hidden="true" data-tooltip="' . t('server.common.seeders') . '">' . icon('torrent-seeders') . '</i>', $this->WithSort, 'seeders') ?>
        </td>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatLeechers">
            <?= $this->header_elem('<i  aria-hidden="true" data-tooltip="' . t('server.common.leechers') . '">' . icon('torrent-leechers') . '</i>', $this->WithSort, 'leechers') ?>
        </td>
        <?
    }
}

class GroupTorrentTableView extends TorrentTableView {
    protected $Groups = [];

    public function __construct($Groups) {
        $this->set_groups($Groups);
        parent::__construct();
        if (check_perms('torrents_check')) {
            $this->CheckAllTorrents = !G::$LoggedUser['DisableCheckAll'];
        } else {
            $this->CheckAllTorrents = false;
        }
        if (check_perms('self_torrents_check')) {
            $this->CheckSelfTorrents = !G::$LoggedUser['DisableCheckSelf'];
        } else {
            $this->CheckSelfTorrents = false;
        }
    }
    private function set_groups($Groups): ?GroupTorrentTableView {
        $this->Groups = $Groups;
        return $this;
    }
    protected function torrents() {
        $Torrents = [];
        foreach ($this->Groups as $Group) {
            foreach ($Group['Torrents'] as $Torrent) {
                $Torrents[] = $Torrent;
            }
        }
        return $Torrents;
    }
    public function render() {
        if ($this->WithCheck) {
            // TODO by qwerty move js
            if ($this->CheckAllTorrents || $this->CheckSelfTorrents) {
        ?>
                <script>
                    function torrents_unchecked() {
                        $('#torrents_unchecked').hide()
                        $('#torrents_checked').show()
                        $('.torrent_all_checked').show()
                        $('.torrent_checked').show()
                        $('.torrent_all_unchecked').hide()
                        $('.torrent_unchecked').hide()
                    }

                    function torrents_checked() {
                        $('#torrents_checked').hide()
                        $('#torrents_all').show()
                        $('.torrent_all_unchecked').show()
                        $('.torrent_unchecked').show()
                    }

                    function torrents_all() {
                        $('#torrents_all').hide()
                        $('#torrents_unchecked').show()
                        $('.torrent_all_checked').hide()
                        $('.torrent_checked').hide()
                    }
                    $(document).ready(function() {
                        $('#torrents_all').click(torrents_all)
                        $('#torrents_unchecked').click(torrents_unchecked)
                        $('#torrents_checked').click(torrents_checked)
                    })
                </script>
        <? }
        }
        ?>
        <div class="TableContainer">
            <table class="TableTorrent Table <?= $this->TableTorrentClass ?>" id="torrent_table">
                <tr class="Table-rowHeader">
                    <?
                    $this->render_header();
                    ?>
                </tr>
                <?
                foreach ($this->Groups as $Idx => $Group) {
                    $this->render_group_info($Idx);
                }
                ?>
            </table>
        </div>
    <?
    }


    protected function render_header() {
    ?>
        <td class="Table-cell" width="40px">
            <?
            if ($this->WithCheck && $this->CheckAllTorrents) {
            ?>
                <a href="javascript:void(-1)" id="torrents_all"><?= icon("Table/check-all") ?></a>
                <a href="javascript:void(-1)" id="torrents_unchecked" style="display:none"><?= icon("Table/unchecked") ?></a>
                <a href="javascript:void(-1)" id="torrents_checked" style="display:none"><?= icon("Table/checked") ?></a>
            <?
            }
            ?>
        </td>

    <?
        parent::render_header();
    }

    protected function render_group_info($Idx) {
        $Group = $this->Groups[$Idx];
        $SnatchedGroupClass = Torrents::parse_group_snatched($Group) ? ' snatched_group' : '';
        $Cols = 5;
        if ($this->WithTime) {
            $Cols += 1;
        }
        $GroupChecked = true;
        foreach ($Group['Torrents'] as $Torrent) {
            if (!$Torrent['Checked']) {
                $GroupChecked = false;
                break;
            }
        }
        $GroupID = $Group['ID'];
        $ShowGroups = !(!empty(G::$LoggedUser['TorrentGrouping']) && G::$LoggedUser['TorrentGrouping'] == 1);
        $TagsList = Torrents::tags($Group);
        $TorrentTags = new Tags($TagsList);
    ?>
        <tr class="TableTorrent-rowMovieInfo Table-row <?= $this->WithCheck && $GroupChecked ? "torrent_all_checked " : "torrent_all_unchecked" ?> <?= $SnatchedGroupClass ?>" group-id="<?= $Group['ID'] ?>">
            <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoCollapse">
                <div id="showimg_<?= $GroupID ?>" class="ToggleGroup <?= ($ShowGroups ? 'is-toHide' : '') ?>">
                    <a href="#" class="ToggleGroup-button" onclick="globalapp.toggleGroup(<?= $GroupID ?>, this, event)" data-tooltip="<?= t('server.common.collapse_this_group_title') ?>"></a>
                </div>
            </td>
            <? if ($this->WithCover) { ?>
                <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoPoster">
                    <? ImageTools::cover_thumb($Group['WikiImage'], $Group['CategoryID']) ?>
                </td>
            <?  } ?>
            <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoBody" colspan="<?= $Cols ?>">
                <div class="TableTorrent-movieInfoBody">
                    <div class="TableTorrent-movieInfoContent">
                        <?= $this->render_group_name($Group); ?>
                        <?= $this->render_movie_info($Group) ?>
                        <div class="TableTorrent-movieInfoTags"><i><?= $TorrentTags->format('torrents.php?action=advanced&amp;taglist=', '', 'TableTorrent-movieInfoTagsItem') ?></i></div>
                    </div>
                </div>
            </td>
            <?
            ?>
        </tr>
        <?
        $this->render_browse_group($Idx);
    }

    private function render_browse_group($Idx) {
        global $LoggedUser;
        $Group = $this->Groups[$Idx];
        $GroupID = $Group['ID'];
        $TorrentList = $Group['Torrents'];
        $LastTorrent = [];
        $EditionID = 0;
        $Cols = 5 + 1;
        if ($this->WithTime) {
            $Cols += 1;
        }
        if ($this->WithCover) {
            $Cols += 1;
        }
        $GroupChecked = true;
        foreach ($Group['Torrents'] as $Torrent) {
            if (!$Torrent['Checked']) {
                $GroupChecked = false;
                break;
            }
        }
        foreach ($TorrentList as $Torrent) {
            $NewEdition = Torrents::get_new_edition_title($LastTorrent, $Torrent);
            if ($NewEdition) {
                $EditionID++;
        ?>
                <tr class="TableTorrent-rowCategory Table-row <?= $this->WithCheck && $GroupChecked ? "torrent_all_checked " : "torrent_all_unchecked" ?> <?= (!empty($LoggedUser['TorrentGrouping']) && $LoggedUser['TorrentGrouping'] === 1 ? ' hidden' : '') ?>" group-id="<?= $GroupID ?>">
                    <td class="TableTorrent-cellCategory Table-cell" colspan="<?= $Cols ?>">
                        <a class="u-toggleEdition-button" href="#" onclick="globalapp.toggleEdition(event, <?= $GroupID ?>, <?= $EditionID ?>)" data-tooltip="<?= t('server.common.collapse_this_edition_title') ?>">&minus;</a>
                        <?= $NewEdition ?>
                    </td>
                </tr>

        <?
            }
            $Torrent['Group'] = $Group;
            $this->render_torrent_info($Torrent, $EditionID);
            $LastTorrent = $Torrent;
        }
    }

    protected function render_torrent_info($Torrent, $EditionID) {
        $TorrentID = $Torrent['ID'];
        $Group = $Torrent['Group'];
        $GroupID = $Group['ID'];

        global $LoggedUser;
        $TorrentChecked = $Torrent['Checked'];
        $FileName = Torrents::filename($Torrent);
        $SnatchedTorrentClass = $Torrent['IsSnatched'] ? ' snatched_torrent' : '';
        $SnatchedGroupClass = Torrents::parse_group_snatched($Group) ? ' snatched_group' : '';
        $Cols = 2;
        if ($this->WithCover) {
            $Cols += 1;
        }
        ?>
        <? /* GroupTorrentTableView */ ?>
        <tr class="TableTorrent-rowTitle Table-row <?= $this->WithCheck && $TorrentChecked ? "torrent_checked " : "torrent_unchecked" ?> <?= $SnatchedGroupClass . (!empty($LoggedUser['TorrentGrouping']) && $LoggedUser['TorrentGrouping'] == 1 ? ' hidden' : '') ?>" group-id="<?= $GroupID ?>" edition-id="<?= $EditionID ?>">
            <td class="Table-cell is-name" colspan="<?= $Cols ?>">
                <div class="TableTorrent-title">
                    <span class="TableTorrent-titleActions">
                        [
                        <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="Download">DL</a>
                        <? if (Torrents::can_use_token($Torrent)) { ?>
                            |
                            <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="Use a FL Token" onclick="return confirm('<?= FL_confirmation_msg($Torrent['Seeders'], $Torrent['Size']) ?>');">FL</a>
                        <? } ?>
                        ]
                    </span>
                    <?
                    if ($this->WithCheck) {
                        $TorrentChecked = $Torrent['Checked'];
                        $TorrentCheckedBy = 'unknown';
                        if ($TorrentChecked) {
                            $TorrentCheckedBy = Users::user_info($TorrentChecked)['Username'];
                        }
                    ?>
                        <span class="TableTorrent-titleCheck">
                            <? if ($this->CheckAllTorrents || ($this->CheckSelfTorrents && $LoggedUser['id'] == $Torrent['UserID'])) { ?>
                                <i class="TableTorrent-check" id="torrent<?= $TorrentID ?>_check1" style="display:<?= $TorrentChecked ? "inline-block" : "none" ?>;color:#649464;" data-tooltip="<?= t('server.torrents.checked_by', ['Values' => [$TorrentChecked ? $TorrentCheckedBy : $LoggedUser['Username']]]) ?>"><?= icon("Table/checked") ?></i>
                                <i class="TableTorrent-check" id="torrent<?= $TorrentID ?>_check0" style="display:<?= $TorrentChecked ? "none" : "inline-block" ?>;color:#CF3434;" data-tooltip="<?= t('server.torrents.has_not_been_checked') ?><?= t('server.torrents.checked_explanation') ?>"><?= icon("Table/unchecked") ?></i>
                            <? } else { ?>
                                <i class="TableTorrent-check" style="color: <?= $TorrentChecked ? "#74B274" : "#A6A6A6" ?>;" data-tooltip="<?= $TorrentChecked ? t('server.torrents.has_been_checked') : t('server.torrents.has_not_been_checked') ?><?= t('server.torrents.checked_explanation') ?>"><?= icon("Table/" . ($TorrentChecked ? "checked" : "unchecked")) ?> </i>
                            <? } ?>
                        </span>
                    <? } ?>
                    <a class="<?= $SnatchedTorrentClass ?>" data-tooltip="<?= $FileName ?>" href="torrents.php?id=<?= $GroupID ?>&amp;torrentid=<?= $TorrentID ?>#torrent<?= $TorrentID ?>">
                        <?= Torrents::torrent_info($Torrent, true, [
                            'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle']
                        ]) ?>
                    </a>

            </td>
            <? if ($this->WithTime) { ?>
                <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatTime">
                    <?= time_diff($Torrent['Time'], 1) ?>
                </td>
            <? } ?>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSize">
                <?= Format::get_size($Torrent['Size']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSnatches">
                <?= number_format($Torrent['Snatched']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSeeders <?= (($Torrent['Seeders'] == 0) ? ' u-colorRatio00' : '') ?>">
                <?= number_format($Torrent['Seeders']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatLeechers">
                <?= number_format($Torrent['Leechers']) ?>
            </td>
        </tr>
    <?
    }
}

class UngroupTorrentTableView  extends TorrentTableView {
    protected $Torrents = [];
    public function __construct($Torrents) {
        $this->Torrents = $Torrents;
        parent::__construct();
    }
    public function render() {
    ?>
        <div class="TableContainer">
            <? if (!empty($this->Torrents)) { ?>
                <table class="TableTorrent Table <?= $this->TableTorrentClass ?> " variant="ungroup" id="torrent_table">
                    <tr class="Table-rowHeader">
                        <?
                        $this->render_header();
                        foreach ($this->Torrents as $Idx => $Torrent) {
                            $this->render_group_info($Idx);
                        }
                        ?>
                    </tr>
                </table>
            <? } else { ?>
                <table>
                    <tr class="rowb">
                        <td colspan="7" class="center">
                            <?= t('server.top10.found_no_torrents_matching_the_criteria') ?>
                        </td>
                    </tr>
                </table>
            <? } ?>
        </div>
    <?
    }

    protected function render_torrent_info($Idx) {
        $Torrent = $this->Torrents[$Idx];
        $TorrentID = $Torrent['ID'];
        $Group = $Torrent['Group'];
        $GroupID = $Group['ID'];
        $SnatchedGroupClass = Torrents::parse_group_snatched($Group) ? ' snatched_group' : '';
        $TorrentID = $Torrent['ID'];
        $SnatchedTorrentClass = $Torrent['IsSnatched'] ? ' snatched_torrent' : '';
        $FileName = Torrents::filename($Torrent);
        global $LoggedUser;
    ?>
        <? /* UngroupTorrentTableView */ ?>
        <tr class="TableTorrent-rowTitle Table-row  <?= $SnatchedGroupClass  ?>" group-id="<?= $GroupID ?>">
            <td class="Table-cell">
                <div class="TableTorrent-title">
                    <span class="TableTorrent-titleActions">
                        [
                        <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="Download">DL</a>
                        <? if (Torrents::can_use_token($Torrent)) { ?>
                            |
                            <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="Use a FL Token" onclick="return confirm('<?= FL_confirmation_msg($Torrent['Seeders'], $Torrent['Size']) ?>');">FL</a>
                        <? } ?>
                        ]
                    </span>
                    <a class="<?= $SnatchedTorrentClass ?>" data-tooltip="<?= $FileName ?>" href="torrents.php?id=<?= $GroupID ?>&amp;torrentid=<?= $TorrentID ?>#torrent<?= $TorrentID ?>">
                        <?= Torrents::torrent_info($Torrent, true, [
                            'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle']
                        ]) ?>
                    </a>

                </div>
            </td>
            <? if ($this->WithTime) { ?>
                <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatTime">
                    <?= time_diff($Torrent['Time'], 1) ?>
                </td>
            <? } ?>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSize">
                <?= Format::get_size($Torrent['Size']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSnatches">
                <?= number_format($Torrent['Snatched']) ?>
            </td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSeeders <?= (($Torrent['Seeders'] == 0) ? ' u-colorRatio00' : '') ?>">
                <?= number_format($Torrent['Seeders']) ?></td>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatLeechers">
                <?= number_format($Torrent['Leechers']) ?>
            </td>
        </tr>
    <?
    }

    private function render_group_info($Idx) {
        $Torrent = $this->Torrents[$Idx];
        $Cols = 5;
        if ($this->WithTime) {
            $Cols += 1;
        }
        $GroupInfo = $Torrent['Group'];
        $CategoryID = $GroupInfo['CategoryID'];
        $TorrentTags = new Tags(Torrents::tags($GroupInfo));
        $TorrentID = $Torrent['ID'];
        $SnatchedTorrentClass = $Torrent['IsSnatched'] ? ' snatched_torrent' : '';
        $SnatchedGroupClass = Torrents::parse_group_snatched($GroupInfo) ? ' snatched_group' : '';
    ?>
        <tr class="TableTorrent-rowMovieInfo Table-row <?= $SnatchedTorrentClass . $SnatchedGroupClass ?>" group-id="<?= $GroupInfo['ID'] ?>">
            <? if ($this->WithNumber) { ?>
                <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoNo" rowspan="2" style="padding: 8px; text-align: center;" class="td_rank m_td_left"><strong><?= $Idx + 1 ?></strong></td>
            <? } ?>
            <? if (!empty($this->FilterID)) { ?>
                <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoCheckbox" rowspan="2" style="text-align: center;">
                    <input type="checkbox" class="notify_box notify_box_<?= $this->FilterID ?>" value="<?= $TorrentID ?>" id="clear_<?= $TorrentID ?>" tabindex="1" />
                </td>
            <? } ?>
            <? if ($this->WithCover) { ?>
                <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoPoster" rowspan="2">
                    <?= ImageTools::cover_thumb($GroupInfo['WikiImage'], $CategoryID) ?>
                </td>
            <? } ?>
            <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoBody" colspan="<?= $Cols ?>">
                <div class="TableTorrent-movieInfoBody">
                    <div class="TableTorrent-movieInfoContent">
                        <?= $this->render_group_name($GroupInfo, true); ?>
                        <?= $this->render_movie_info($GroupInfo) ?>
                        <div class="TableTorrent-movieInfoTags">
                            <i><?= $TorrentTags->format("torrents.php?action=advanced&amp;taglist=", '', 'TableTorrent-movieInfoTagsItem') ?></i>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
<?
        $this->render_torrent_info($Idx);
    }
}
