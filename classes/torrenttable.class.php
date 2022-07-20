<?

use Gazelle\API\Torrent;

interface SortLink {
    public function link($SortKey, $DefaultWay = 'desc');
}

class TorrentGroupCoverTableView extends GroupTorrentTableView {
    /* { UseTorrentID => false } */
    public function render($options = []) {
        $Class = $options['class'];
        $Variant = $options['Variant'];
?>
        <div class="TorrentCover <?= $Class ?>" variant="<?= $Variant ?>">
            <?
            foreach ($this->Groups as $RS) {
                $Name = Torrents::group_name($RS, false);
                $QueryString = $options['UseTorrentID'] ? "torrentid=" . $RS['TorrentID'] : "id=" . $RS['ID'];
            ?>
                <a class="TorrentCover-item" href="torrents.php?<?= $QueryString ?>">
                    <div class="TorrentCover-imageContainer">
                        <img class="TorrentCover-image" src="<?= ImageTools::process($RS['WikiImage'], false) ?>" />
                    </div>
                    <b><?= $Name ?></b>
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
                            <?= Lang::get('top10', 'found_no_torrents_matching_the_criteria') ?>
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
        <tr class="TableTorrent-rowTitle Table-row u-tableTorrent-rowTitle <?= $SnatchedGroupClass . (!empty($LoggedUser['TorrentGrouping']) && $LoggedUser['TorrentGrouping'] === 1 ? ' hidden' : '') ?>" group-id="<?= $GroupID ?>" torrent-id="<?= $TorrentID ?>">
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
                    <? if (isset($this->DetailView)) { ?>
                        <a clas="<?= $SnatchedTorrentClass ?>" href="#" onclick="globalapp.toggleTorrentDetail(event, '#torrent_<?= $this->DetailView ?>_<?= $TorrentID ?>')">
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
                    <? if (!$Options['NoActions']) { ?>
                        <span class="TableTorrent-titleActions">
                            [
                            <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="Download">DL</a>
                            <? if (Torrents::can_use_token($Torrent)) { ?>
                                |
                                <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="Use a FL Token" onclick="return confirm('<?= FL_confirmation_msg($Torrent['Seeders'], $Torrent['Size']) ?>');">FL</a>
                            <? } ?>
                            |
                            <a href="torrents.php?torrentid=<?= $TorrentID ?>" data-tooltip="<?= Lang::get('torrents', 'permalink') ?>">PL</a>
                            ]
                        </span>
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
        $Size = $Torrent['Size'];
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
        $TrumpableMsg = '';
        $TrumpableAddExtra = '';


        if (!empty($BadFolders)) {
            $TrumpableMsg .= $TrumpableAddExtra . Lang::get('torrents', 'bad_filename');
            $TrumpableAddExtra = ' / ';
        }

        if (!empty($BadFiles)) {
            $TrumpableMsg .= $TrumpableAddExtra . Lang::get('torrents', 'bad_files');
            $TrumpableAddExtra = ' / ';
        }
        if (!empty($NoSub)) {
            $TrumpableMsg .= $TrumpableAddExtra . Lang::get('upload', 'no_sub');
            $TrumpableAddExtra = ' / ';
        }
        if (!empty($HardSub)) {
            $TrumpableMsg .= $TrumpableAddExtra . Lang::get('upload', 'hardcode_sub');
            $TrumpableAddExtra = ' / ';
        }
        if (!empty($CustomTrumpable)) {
            $TrumpableMsg .= $TrumpableAddExtra . $CustomTrumpable;
            $TrumpableAddExtra = ' / ';
        }
        if ($Dead) {
            $TrumpableMsg .= $TrumpableAddExtra . Lang::get('upload', 'dead_torrent');
            $TrumpableAddExtra = ' / ';
        }
        $Reported = false;
        $Reports = Torrents::get_reports($TorrentID);
        $NumReports = count($Reports);
        // 如果不展示report，这里直接赋值0 bad
        if (!$ShowReport) {
            $NumReports = 0;
        }

        if ($NumReports > 0) {
            $Reported = true;
            include(Lang::getLangfilePath("report_types"));
            $ReportInfo = '
        <div class="TableContainer">
            <table class="TableReportInfo Table">
                <tr class="Table-rowHeader">
                    <td class="Table-cell">' . Lang::get('torrents', 'this_torrent_has_active_reports_1') . $NumReports . Lang::get('torrents', 'this_torrent_has_active_reports_2') . ($NumReports === 1 ? Lang::get('torrents', 'this_torrent_has_active_reports_3') : Lang::get('torrents', 'this_torrent_has_active_reports_4')) . ":</td>
                </tr>";
            foreach ($Reports as $Report) {
                $ReportID = $Report['ID'];
                if (check_perms('admin_reports')) {
                    $ReporterID = $Report['ReporterID'];
                    $Reporter = Users::user_info($ReporterID);
                    $ReporterName = $Reporter['Username'];
                    $ReportLinks = "<a href=\"user.php?id=$ReporterID\">$ReporterName</a> <a href=\"reportsv2.php?view=report&amp;id=$Report[ID]\">" . Lang::get('torrents', 'reported_it') . "</a>";
                    $UploaderLinks = Users::format_username($UserID, false, false, false) . " " . Lang::get('torrents', 'reply_at');
                } else {
                    $ReportLinks = Lang::get('torrents', 'someone_reported_it');
                    $UploaderLinks = Lang::get('torrents', 'uploader_replied_it');
                }

                if (isset($Types[$GroupCategoryID][$Report['Type']])) {
                    $ReportType = $Types[$GroupCategoryID][$Report['Type']];
                } elseif (isset($Types['master'][$Report['Type']])) {
                    $ReportType = $Types['master'][$Report['Type']];
                } else {
                    //There was a type but it wasn't an option!
                    $ReportType = $Types['master']['other'];
                }
                $CanReply = $UserID == G::$LoggedUser['ID'] && !$Report['UploaderReply'] && !$ReadOnly;
                $ReportInfo .= "
                <tr class='Table-row'>
                    <td class='Table-cell'>$ReportLinks" . Lang::get('torrents', 'at') . " " . time_diff($Report['ReportedTime'], 2, true, true) . Lang::get('torrents', 'for_the_reason') . $ReportType['title'] . '":' . ($CanReply ? ('<a class="floatright report_reply_btn" onclick="$(\'.can_reply_' . $ReportID . '\').toggle()" href="javascript:void(0)">' . Lang::get('torrents', 'reply') . '</a>') : "") . '
                        <blockquote>' . Text::full_format($Report['UserComment']) . ($Report['UploaderReply'] ? ('
                            <hr class="report_inside_line">' . $UploaderLinks . ' ' . time_diff($Report['ReplyTime'], 2, true, true) . ':<br>' . Text::full_format($Report['UploaderReply'])) : '') . '
                        </blockquote>
                    </td>
                </tr>';
                $area = new TEXTAREA_PREVIEW('uploader_reply', '', '', 50, 10, true, true, true, array(
                    'placeholder="' . Lang::get('torrents', 'reply_it_patiently') . '"'
                ), false);
                $ReportInfo .= $CanReply ? '
                <tr class="Table-row report_reply_tr can_reply_' . $ReportID . '" style="display: none;">
                    <td class="Table-cell Table-cellCenter report_reply_td">
                        <form action="reportsv2.php?action=takeuploaderreply" method="POST">
                            <input type="hidden" name="reportid" value="' . $ReportID . '">
                            <input type="hidden" name="torrentid" value="' . $TorrentID . '">
                            ' . $area->getBuffer() . '
                            <div class="submit_div preview_submit">
                                <input class="Button" type="submit">
                            </div>
                        </form>
                    </td>
                </tr>' : "";
            }
            $ReportInfo .= "\n\t\t
            </table>
        </div>";
        }
    ?>

        <div class="TorrentDetail">
            <div class="TorrentDetail-row is-uploadContainer is-block" id="release_<?= $TorrentID ?>">
                <div class="TorrentDetail-uploader">
                    <div class="TorrentDetail-uploaderInfo">
                        <span><?= Lang::get('torrents', 'upload_by_before') ?><span>
                                <?= Users::format_username($UserID, false, false, false) ?>
                                <?= Lang::get('torrents', 'upload_by_after') ?>
                                <?= time_diff($TorrentTime); ?>
                                <?
                                if ($Seeders == 0) {
                                    // If the last time this was seeded was 50 years ago, most likely it has never been seeded, so don't bother
                                    // displaying "Last active: 2000+ years" as that's dumb
                                    if (time() - strtotime($LastActive) > 1576800000) {
                                ?>
                                        <span>|</span>
                                        <?= Lang::get('torrents', 'last_active') ?>:<?= Lang::get('torrents', 'never') ?>
                                    <?
                                    } elseif ($LastActive != '0000-00-00 00:00:00' && time() - strtotime($LastActive) >= 1209600) {
                                    ?>
                                        <span>|</span><strong><?= Lang::get('torrents', 'last_active') ?> <?= time_diff($LastActive); ?></strong>
                                    <?
                                    } else {
                                    ?><span>|</span> <?= Lang::get('torrents', 'last_active') ?> <?= time_diff($LastActive); ?>
                                    <?
                                    }
                                }
                                if (
                                    !$ReadOnly &&
                                    (($Seeders == 0  &&
                                        $LastActive != '0000-00-00 00:00:00' &&
                                        time() - strtotime($LastActive) >= 345678 &&
                                        time() - strtotime($LastReseedRequest) >= 864000) ||
                                        check_perms('users_mod'))
                                ) {
                                    ?><span>|</span> <a href="torrents.php?action=reseed&amp;torrentid=<?= $TorrentID ?>&amp;groupid=<?= $GroupID ?>" class="brackets" onclick="return confirm('<?= Lang::get('torrents', 'request_re_seed_confirm') ?>');"><?= Lang::get('torrents', 'request_re_seed') ?></a>
                                <?
                                } ?>
                    </div>
                    <? if (!$ReadOnly) { ?>
                        <div class="TorrentDetail-likeContainer ButtonGroup ButtonGroup--wide">
                            <div class="TorrentDetail-reward is-total">
                                <span class="TorrentDetail-rewardButton" data-tooltip="<?= Lang::get('torrents', 'total_reward_bonus_points_pre_tax') ?>">
                                    <?= icon('bonus-active') ?>
                                </span>
                                <span data-tooltip="<?= Lang::get('torrents', 'total_reward_bonus_points_pre_tax') ?>" id="bonuscnt<?= $TorrentID ?>">
                                    <?= isset($BonusSended) && isset($BonusSended['Count']) && $BonusSended['Count'] > 0 ? $BonusSended['Count'] : '0' ?>
                                </span>
                            </div>
                            <div class="TorrentDetail-like">
                                <span id="thumb<?= $TorrentID ?>" <?= isset($ThumbCounts) && isset($ThumbCounts['on']) && $ThumbCounts['on'] > 0 ? 'style="display: none;"' : '' ?>>
                                    <? if (G::$LoggedUser['ID'] == $UserID) { ?>
                                        <i data-tooltip="<?= Lang::get('torrents', 'you_cant_like_yourself') ?>">
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
                                    <?= isset($ThumbCounts) && isset($ThumbCounts['count']) ? $ThumbCounts['count'] : Lang::get('torrents', 'like') ?>
                                </span>
                            </div>
                        </div>
                    <? } ?>
                </div>
                <? if (!$ReadOnly) { ?>
                    <div class="TorrentDetail-ratioCalc">
                        <?
                        $NewRatio = Format::get_ratio_html(G::$LoggedUser['BytesUploaded'], G::$LoggedUser['BytesDownloaded'] + $Size);
                        ?>
                        <?= Lang::get('torrents', 'if_you_download_this_before') ?> <?= $NewRatio ?><?= Lang::get('torrents', 'if_you_download_this_after') ?>
                    </div>
                <? } ?>
            </div>
            <?
            if ($TrumpableMsg) { ?>
                <div class="TorrentDetail-trumpable TorrentDetail-row is-block">
                    <span class="TorrentDetail-trumpableTitle">
                        <?= Lang::get('torrents', 'trumpable_reason') ?>:
                    </span>
                    <span class="TorrentDetail-trumpableMessage">
                        <?= $TrumpableMsg ?>
                    </span>
                </div>
            <?
            } ?>

            <?
            if (!$ReadOnly) {
            ?>
                <div class="TorrentDetail-row is-rewardContainer">
                    <div class="TorrentDetail-rewardList ButtonGroup" id="sendbonus_<?= $TorrentID ?>">
                        <? $Sended = isset($BonusSended) ? explode(',', $BonusSended['Sended']) : []; ?>
                        <div class="TorrentDetail-reward">
                            <span class="TorrentDetail-rewardButton is-active" data-tooltip="<?= G::$LoggedUser['ID'] == $UserID ? Lang::get('torrents', 'you_cant_reward_yourself') : Lang::get('torrents', 'you_have_rewarded') ?>" style="<?= in_array(5, $Sended) || G::$LoggedUser['ID'] == $UserID ? "" : "display: none;" ?>" id="bonus5<?= $TorrentID ?>">
                                <?= icon('bonus-active') ?>
                            </span>
                            <a class="TorrentDetail-rewardButton is-toReward" data-tooltip="<?= Lang::get('torrents', 'reward_5_bonus_to_uploader') ?>" style="<?= in_array(5, $Sended) || G::$LoggedUser['ID'] == $UserID ? "display: none;" : "" ?>" id="abonus5<?= $TorrentID ?>" href="javascript:void(0);" onclick="sendbonus(<?= $TorrentID ?>, 5)">
                                <?= icon('bonus-active') ?>
                            </a>
                            <span>5</span>
                        </div>
                        <div class="TorrentDetail-reward">
                            <span class="TorrentDetail-rewardButton is-active" data-tooltip="<?= G::$LoggedUser['ID'] == $UserID ? Lang::get('torrents', 'you_cant_reward_yourself') : Lang::get('torrents', 'you_have_rewarded') ?>" style="<?= in_array(30, $Sended) || G::$LoggedUser['ID'] == $UserID ? "" : "display: none;" ?>" id="bonus30<?= $TorrentID ?>">
                                <?= icon('bonus-active') ?>
                            </span>
                            <a class="TorrentDetail-rewardButton is-toReward" data-tooltip="<?= Lang::get('torrents', 'reward_30_bonus_to_uploader') ?>" style="<?= in_array(30, $Sended) || G::$LoggedUser['ID'] == $UserID ? "display: none;" : "" ?>" id="abonus30<?= $TorrentID ?>" href="javascript:void(0);" onclick="sendbonus(<?= $TorrentID ?>, 30)">
                                <?= icon('bonus-active') ?>
                            </a>
                            <span>30</span>
                        </div>
                        <div class="TorrentDetail-reward">
                            <span class="TorrentDetail-rewardButton is-active" data-tooltip="<?= G::$LoggedUser['ID'] == $UserID ? Lang::get('torrents', 'you_cant_reward_yourself') : Lang::get('torrents', 'you_have_rewarded') ?>" style="<?= in_array(100, $Sended) || G::$LoggedUser['ID'] == $UserID ? "" : "display: none;" ?>" id="bonus100<?= $TorrentID ?>">
                                <?= icon('bonus-active') ?>
                            </span>
                            <a class="TorrentDetail-rewardButton is-toReward" data-tooltip="<?= Lang::get('torrents', 'reward_100_bonus_to_uploader') ?>" style="<?= in_array(100, $Sended) || G::$LoggedUser['ID'] == $UserID ? "display: none;" : "" ?>" id="abonus100<?= $TorrentID ?>" href="javascript:void(0);" onclick="sendbonus(<?= $TorrentID ?>, 100)">
                                <?= icon('bonus-active') ?>
                            </a>
                            <span>100</span>
                        </div>
                        <div class="TorrentDetail-reward">
                            <span class="TorrentDetail-rewardButton is-active" data-tooltip="<?= G::$LoggedUser['ID'] == $UserID ? Lang::get('torrents', 'you_cant_reward_yourself') : Lang::get('torrents', 'you_have_rewarded') ?>" style="<?= in_array(300, $Sended) || G::$LoggedUser['ID'] == $UserID ? "" : "display: none;" ?>" id="bonus300<?= $TorrentID ?>">
                                <?= icon('bonus-active') ?>
                            </span>
                            <a class="TorrentDetail-rewardButton is-toReward" data-tooltip="<?= Lang::get('torrents', 'reward_300_bonus_to_uploader') ?>" style="<?= in_array(300, $Sended) || G::$LoggedUser['ID'] == $UserID ? "display: none;" : "" ?>" id="abonus300<?= $TorrentID ?>" href="javascript:void(0);" onclick="sendbonus(<?= $TorrentID ?>, 300)">
                                <?= icon('bonus-active') ?>
                            </a>
                            <span>300</span>
                        </div>
                    </div>
                </div>

                <? if (check_perms('site_moderate_requests')) { ?>
                    <div class="TorrentDetail-row is-pmContainer">
                        <div class="TorrentDetail-links is-massPM">
                            <a class="Link" href="torrents.php?action=masspm&amp;id=<?= $GroupID ?>&amp;torrentid=<?= $TorrentID ?>">
                                <?= Lang::get('torrents', 'masspm') ?>
                            </a>
                        </div>
                    <?
                } ?>
                    </div>
                <? } ?>
                <div class="TorrentDetail-row is-viewActionsContainer">
                    <div class="TorrentDetail-links is-viewActions">
                        <? if (!$ReadOnly) { ?>
                            <a class="Link" href="#" onclick="show_peers('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;"><?= Lang::get('torrents', 'view_peer_list') ?></a>
                            <? if (check_perms('site_view_torrent_snatchlist')) { ?>
                                <a class="Link" href="#" onclick="show_downloads('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;" data-tooltip="<?= Lang::get('torrents', 'show_downloads_title') ?>"><?= Lang::get('torrents', 'view_download_list') ?></a>
                                <a class="Link" href="#" onclick="show_snatches('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;" data-tooltip="<?= Lang::get('torrents', 'show_snatches_title') ?>"><?= Lang::get('torrents', 'view_snatch_list') ?></a>
                            <?  } ?>
                            <a class="Link" href="#" onclick="show_giver('<?= $TorrentID ?>', 0, '<?= $this->DetailView ?>'); return false;"><?= Lang::get('torrents', 'giver_list') ?></a>
                        <?  } ?>
                        <a class="Link" href="#" onclick="show_files('<?= $TorrentID ?>', '<?= $this->DetailView ?>'); return false;"><?= Lang::get('torrents', 'view_file_list') ?></a>
                        <? if ($Reported) { ?>
                            <a class="Link" href="#" onclick="show_reported('<?= $TorrentID ?>','<?= $this->DetailView ?>'); return false;"><?= Lang::get('torrents', 'view_report_information') ?></a>
                        <?  } ?>
                    </div>
                    <div class="TorrentDetail-giverList hidden" id="<?= $this->DetailView ?>_giver_<?= $TorrentID ?>"></div>
                    <div class="TorrentDetail-peerList hidden" id="<?= $this->DetailView ?>_peers_<?= $TorrentID ?>"></div>
                    <div class="TorrntDetail-downloadList hidden" id="<?= $this->DetailView ?>_downloads_<?= $TorrentID ?>"></div>
                    <div class="TorrentDetail-snatchList hidden" id="<?= $this->DetailView ?>_snatches_<?= $TorrentID ?>"></div>
                    <div class="TorrentDetail-fileList hidden" id="<?= $this->DetailView ?>_files_<?= $TorrentID ?>"></div>
                    <? if ($Reported) { ?>
                        <div class="TorrentDetail-reportedList hidden" id="<?= $this->DetailView ?>_reported_<?= $TorrentID ?>"><?= $ReportInfo ?></div>
                    <?  } ?>
                </div>

                <? if ($Note) { ?>
                    <div class="TorrentDetail-row is-staffNote is-block">
                        <span class='u-colorWarning'><strong><?= Lang::get('upload', 'staff_note') ?>:</strong></span>
                        <?= Text::full_format($Note) ?>
                    </div>
                <? } ?>

                <div class="TorrentDetail-row is-subtitle is-block TorrentDetailSubtitle" id="subtitles_box">
                    <div class="TorrentDetailSubtitle-header" id="subtitles_box_header">
                        <strong class="TorrentDetailSubtitle-title" id="subtitles_box_title"><?= Lang::get('global', 'subtitles') ?>:</strong>
                        <? if (!$ReadOnly) { ?>
                            <span class="floatright"><a href="subtitles.php?action=upload&torrent_id=<?= $TorrentID ?>"><?= Lang::get('torrents', 'add_subtitles') ?></a></span>
                        <?  } ?>
                        <? if (!$Subtitles && !$ExternalSubtitleIDs) { ?>
                            <span class="TorrentDetailSubtitle-noSubtitle" data-tooltip="<?= Lang::get('upload', "no_subtitles") ?>">
                                <?= icon('flag/no_subtitles') ?>
                            </span>
                        <? } ?>
                    </div>
                    <?
                    if ($Subtitles) {

                        $SubtitleArray = explode(',', $Subtitles);
                    ?>
                        <div class="TorrentDetailSubtitle-list is-internal" id="subtitles_box_in_torrent">
                            <span class="TorrentDetailSubtitle-listTitle"><?= $SubtitleType == 1 ? Lang::get('global', 'in_torrent_subtitles') : Lang::get('global', 'in_torrent_hard_subtitles'); ?>:</span>
                            <? foreach ($SubtitleArray as $Subtitle) { ?>
                                <span class="TorrentDetailSubtitle-listItem" data-tooltip="<?= Lang::get('upload', $Subtitle) ?>">
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
                                <?= Lang::get('global', 'external_subtitles') ?>:
                            </span>
                            <?
                            foreach ($ExternalSubtitleIDArray as $index => $ExternalSubtitleID) {
                                $SubtitleLanguages = $ExternalSubtitleArray[$index];
                                $SubtitleLanguagesArray = explode(',', $SubtitleLanguages);
                                if (in_array('chinese_simplified', $SubtitleLanguagesArray)) {
                            ?>
                                    <a class="TorrentDetailSubtitle-listItem" href="subtitles.php?action=download&id= <?= $ExternalSubtitleID ?>" data-tooltip="<?= Lang::get('upload', 'chinese_simplified') ?>">
                                        <?= icon('flag/chinese_simplified') ?>
                                    </a>
                                <?
                                } else if (in_array('chinese_traditional', $SubtitleLanguagesArray)) { ?>
                                    <a class=" TorrentDetailSubtitle-listItem" href="subtitles.php?action=download&id=<?= $ExternalSubtitleID ?>" data-tooltip="<?= Lang::get('upload', 'chinese_traditional') ?>">
                                        <?= icon('flag/chinese_traditional') ?>
                                    </a>
                                <?
                                } else if ($SubtitleLanguagesArray[0]) { ?>
                                    <a class=" TorrentDetailSubtitle-listItem" href="subtitles.php?action=download&id=<?= $ExternalSubtitleID ?>" data-tooltip="<?= Lang::get('upload', $SubtitleLanguagesArray[0]) ?>">
                                        <?= icon("flag/$SubtitleLanguagesArray[0]") ?>
                                    </a>
                            <?
                                }
                            }
                            ?>
                            | <a class="Link" href="#" onclick="BrowseExternalSub(<?= $TorrentID ?>); return false;"><?= Lang::get('index', 'details') ?></a>
                        </div>
                        <div id="external_subtitle_container_<?= $TorrentID ?>" class="hidden"></div>
                    <?  } ?>

                </div>


                <? if (!empty($MediaInfos)) { ?>
                    <div class=" TorrentDetail-row is-mediainfo is-block">
                        <strong class="TorrentDetailSubtitle-title" id="subtitles_box_title"><?= Lang::get('torrents', 'media_info') ?>:</strong>
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
        $SubName = $GroupInfo['SubName'];
        $GroupName = $GroupInfo['Name'];
        $GroupYear = $GroupInfo['Year'];
    ?>
        <span class="TableTorrent-movieInfoTitle">
            <a href="\torrents.php?id=<?= $GroupID ?>"><?= $GroupName ?></a>
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
                echo " <a href=\"torrents.php?searchstr=" . $SubName . "\">$SubName</a>";
            } ?>
        </div>
    <?
    }
    protected function render_movie_info($Group) {
        $Artists = $Group['Artists'];
        $Director = Artists::get_first_directors($Artists);

    ?>
        <div class="TableTorrent-movieInfoFacts ">
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= Lang::get('global', 'imdb_rating') ?>, <?= $Group['IMDBVote'] . ' ' . Lang::get('torrents', 'movie_votes') ?>" target="_blank" href="https://www.imdb.com/title/<?= $Group['IMDBID'] ?>">
                <?= icon('imdb-gray') ?>
                <span><?= !empty($Group['IMDBRating']) ? sprintf("%.1f", $Group['IMDBRating']) : '--' ?></span>
            </a>
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= Lang::get('global', 'douban_rating') ?>, <?= ($Group['DoubanVote'] ? $Group['DoubanVote'] : '?') . ' ' . Lang::get('torrents', 'movie_votes') ?>" target="_blank" href="https://movie.douban.com/subject/<?= $Group['DoubanID'] ?>/">
                <?= icon('douban-gray') ?>
                <span><?= !empty($Group['DoubanRating']) ? sprintf("%.1f", $Group['DoubanRating']) : '--' ?></span>
            </a>
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= Lang::get('global', 'rt_rating') ?>" target="_blank" href="https://www.rottentomatoes.com/m/<?= $Group['RTTitle'] ?>">
                <?= icon('rotten-tomatoes-gray') ?>
                <span><?= !empty($Group['RTRating']) ? $Group['RTRating'] : '--' ?></span>
            </a>
            <a class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= Lang::get('upload', 'director') ?>" href="/artist.php?id=<?= $Director['id'] ?>" dir="ltr">
                <?= icon('movie-director') ?>
                <span><?= Artists::display_artist($Director, false) ?></span>
            </a>
            <span class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= Lang::get('torrents', 'imdb_region') ?>">
                <?= icon('movie-country') ?>
                <span><? print_r(implode(', ', array_slice(explode(',', $Group['Region']), 0, 2))) ?></span>
            </span>
            <span class="TableTorrent-movieInfoFactsItem" data-tooltip="<?= Lang::get('upload', 'movie_type') ?>">
                <?= icon('movie-type') ?>
                <span><?= Lang::get('torrents', 'release_types')[$Group['ReleaseType']] ?></span>
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
            <span><?= Lang::get('torrents', 'name') ?><?= ($this->WithYear ? '/' . $this->header_elem(Lang::get('torrents', 'year'), true, 'year') : '') ?></span>
            <? if ($this->WithCheck) {
                if ($this->CheckAllTorrents) {
                    if ($this->AllUncheckedCnt < 50) {
                        $CntColor = "#009900";
                    } else if ($this->AllUncheckedCnt < 100) {
                        $CntColor = "#99CC33";
                    } else if ($this->AllUncheckedCnt < 200) {
                        $CntColor = "#F2C300";
                    } else {
                        $CntColor = "#CF3434";
                    }
            ?>
                    <span><?= Lang::get('torrents', 'unchecked_torrents') ?>:<?= $this->PageUncheckedCnt ?>/<span style="color: <?= $CntColor ?>;font-weight: bold;"><?= $this->AllUncheckedCnt ?></span></span>
            <? }
            } ?>
        </td>
        <?
        if ($this->WithTime) {
        ?>
            <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatTime">
                <?= $this->header_elem('<span  aria-hidden="true" data-tooltip="' . Lang::get('torrents', 'time') . '">' . icon('torrent-time') . '</span>', $this->WithSort, 'time') ?>
            </td>
        <?
        }
        ?>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSize  ">
            <?= $this->header_elem('<span  aria-hidden="true" data-tooltip="' . Lang::get('global', 'size') . '">' . icon('torrent-size') . '</i>', $this->WithSort, 'size') ?>
        </td>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSnatches">
            <?= $this->header_elem('<i  aria-hidden="true" data-tooltip="' . Lang::get('global', 'snatched') . '">' . icon('torrent-snatches') . '</i>', $this->WithSort, 'snatched') ?>
        </td>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatSeeders">
            <?= $this->header_elem('<i  aria-hidden="true" data-tooltip="' . Lang::get('global', 'seeders') . '">' . icon('torrent-seeders') . '</i>', $this->WithSort, 'seeders') ?>
        </td>
        <td class="Table-cell TableTorrent-cellStat TableTorrent-cellStatLeechers">
            <?= $this->header_elem('<i  aria-hidden="true" data-tooltip="' . Lang::get('global', 'leechers') . '">' . icon('torrent-leechers') . '</i>', $this->WithSort, 'leechers') ?>
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
        if ($this->CheckAllTorrents) {
            G::$DB->query("select count(*) from torrents where Checked=0");
            list($this->AllUncheckedCnt) = G::$DB->next_record();
            foreach ($this->torrents() as $Torrent) {
                $TorrentChecked = $Torrent['Checked'];
                if (!$TorrentChecked) {
                    $this->PageUncheckedCnt++;
                }
            }
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
    ?>
        <tr class="TableTorrent-rowMovieInfo Table-row <?= $this->WithCheck && $GroupChecked ? "torrent_all_checked " : "torrent_all_unchecked" ?> <?= $SnatchedGroupClass ?>" group-id="<?= $Group['ID'] ?>">
            <?
            $GroupID = $Group['ID'];
            $ShowGroups = !(!empty(G::$LoggedUser['TorrentGrouping']) && G::$LoggedUser['TorrentGrouping'] == 1);
            $TagsList =  $Group['TagList'];
            $TorrentTags = new Tags($TagsList);
            ?>
            <td class="TableTorrent-cellMovieInfo Table-cell TableTorrent-cellMovieInfoCollapse">
                <div id="showimg_<?= $GroupID ?>" class="ToggleGroup <?= ($ShowGroups ? 'is-toHide' : '') ?>">
                    <a href="#" class="ToggleGroup-button" onclick="globalapp.toggleGroup(<?= $GroupID ?>, this, event)" data-tooltip="<?= Lang::get('global', 'collapse_this_group_title') ?>"></a>
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
                        <div class="TableTorrent-movieInfoTags"><?= $TorrentTags->format('torrents.php?action=basic&amp;taglist=', '', 'TableTorrent-movieInfoTagsItem') ?></div>
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
                        <a class="u-toggleEdition-button" href="#" onclick="globalapp.toggleEdition(event, <?= $GroupID ?>, <?= $EditionID ?>)" data-tooltip="<?= Lang::get('global', 'collapse_this_edition_title') ?>">&minus;</a>
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
        $FileName = Torrents::parse_file_name($Torrent);
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
                    <?
                    if ($this->WithCheck) {
                        $TorrentChecked = $Torrent['Checked'];
                        $TorrentCheckedBy = 'unknown';
                        if ($TorrentChecked) {
                            $TorrentCheckedBy = Users::user_info($TorrentChecked)['Username'];
                        }
                    ?>
                        <div class="TableTorrent-titleCheck">
                            <? if ($this->CheckAllTorrents || ($this->CheckSelfTorrents && $LoggedUser['id'] == $Torrent['UserID'])) { ?>
                                <i class="TableTorrent-check" id="torrent<?= $TorrentID ?>_check1" style="display:<?= $TorrentChecked ? "inline-block" : "none" ?>;color:#649464;" data-tooltip="<?= Lang::get('torrents', 'checked_by_before') ?><?= $TorrentChecked ? $TorrentCheckedBy : $LoggedUser['Username'] ?><?= Lang::get('torrents', 'checked_by_after') ?>"><?= icon("Table/checked") ?></i>
                                <i class="TableTorrent-check" id="torrent<?= $TorrentID ?>_check0" style="display:<?= $TorrentChecked ? "none" : "inline-block" ?>;color:#CF3434;" data-tooltip="<?= Lang::get('torrents', 'has_not_been_checked') ?><?= Lang::get('torrents', 'checked_explanation') ?>"><?= icon("Table/unchecked") ?></i>
                            <? } else { ?>
                                <i class="TableTorrent-check" style="color: <?= $TorrentChecked ? "#74B274" : "#A6A6A6" ?>;" data-tooltip="<?= $TorrentChecked ? Lang::get('torrents', 'has_been_checked') : Lang::get('torrents', 'has_not_been_checked') ?><?= Lang::get('torrents', 'checked_explanation') ?>"><?= icon("Table/" . ($TorrentChecked ? "checked" : "unchecked")) ?> </i>
                            <? } ?>
                        </div>
                    <? } ?>
                    <a class="<?= $SnatchedTorrentClass ?>" data-tooltip="<?= $FileName ?>" href="torrents.php?id=<?= $GroupID ?>&amp;torrentid=<?= $TorrentID ?>#torrent<?= $TorrentID ?>">
                        <?= Torrents::torrent_info($Torrent, true, [
                            'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle']
                        ]) ?>
                    </a>
                    <span class="TableTorrent-titleActions">
                        [
                        <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="Download">DL</a>
                        <? if (Torrents::can_use_token($Torrent)) { ?>
                            |
                            <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="Use a FL Token" onclick="return confirm('<?= FL_confirmation_msg($Torrent['Seeders'], $Torrent['Size']) ?>');">FL</a>
                        <? } ?>
                        ]
                    </span>
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
                            <?= Lang::get('top10', 'found_no_torrents_matching_the_criteria') ?>
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
        $FileName = Torrents::parse_file_name($Torrent);
        global $LoggedUser;
    ?>
        <? /* UngroupTorrentTableView */ ?>
        <tr class="TableTorrent-rowTitle Table-row  <?= $SnatchedGroupClass . (!empty($LoggedUser['TorrentGrouping']) && $LoggedUser['TorrentGrouping'] === 1 ? ' hidden' : '') ?>" group-id="<?= $GroupID ?>">
            <td class="Table-cell">
                <div class="TableTorrent-title">
                    <a class="<?= $SnatchedTorrentClass ?>" data-tooltip="<?= $FileName ?>" href="torrents.php?id=<?= $GroupID ?>&amp;torrentid=<?= $TorrentID ?>#torrent<?= $TorrentID ?>">
                        <?= Torrents::torrent_info($Torrent, true, [
                            'SettingTorrentTitle' => G::$LoggedUser['SettingTorrentTitle']
                        ]) ?>
                    </a>
                    <span class="TableTorrent-titleActions">
                        [
                        <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>" data-tooltip="Download">DL</a>
                        <? if (Torrents::can_use_token($Torrent)) { ?>
                            |
                            <a href="torrents.php?action=download&amp;id=<?= $TorrentID ?>&amp;authkey=<?= $LoggedUser['AuthKey'] ?>&amp;torrent_pass=<?= $LoggedUser['torrent_pass'] ?>&amp;usetoken=1" data-tooltip="Use a FL Token" onclick="return confirm('<?= FL_confirmation_msg($Torrent['Seeders'], $Torrent['Size']) ?>');">FL</a>
                        <? } ?>
                        ]
                    </span>
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
        $TagsList =  $GroupInfo['TagList'];
        $TorrentTags = new Tags($TagsList);
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
                            <?= $TorrentTags->format("torrents.php?action=basic&amp;taglist=", '', 'TableTorrent-movieInfoTagsItem') ?>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
<?
        $this->render_torrent_info($Idx);
    }
}
