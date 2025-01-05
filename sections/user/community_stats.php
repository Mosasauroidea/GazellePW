<?
$DB->query("
	SELECT Page, COUNT(1)
	FROM comments
	WHERE AuthorID = $UserID
	GROUP BY Page");
$Comments = $DB->to_array('Page');
$NumComments = $Comments['torrents'][1];
$NumArtistComments = $Comments['artist'][1];
$NumCollageComments = $Comments['collages'][1];
$NumRequestComments = $Comments['requests'][1];

$CanViewUploads = check_perms("users_view_uploaded");

$DB->query("
	SELECT COUNT(ID)
	FROM collages
	WHERE Deleted = '0'
		AND UserID = '$UserID'");
list($NumCollages) = $DB->next_record();

$DB->query("
	SELECT COUNT(DISTINCT CollageID)
	FROM collages_torrents AS ct
		JOIN collages ON CollageID = ID
	WHERE Deleted = '0'
		AND ct.UserID = '$UserID'");
list($NumCollageContribs) = $DB->next_record();

$DB->query("
	SELECT COUNT(DISTINCT GroupID)
	FROM torrents
	WHERE UserID = '$UserID'");
list($UniqueGroups) = $DB->next_record();

$DB->query("
	SELECT COUNT(ID)
	FROM torrents
	WHERE Buy='1'
		AND UserID = '$UserID'
	");
list($OriginalsBuy) = $DB->next_record();

$DB->query("
	SELECT COUNT(ID)
	FROM torrents
	WHERE Diy='1'
		AND UserID = '$UserID'
    ");
list($OriginalsDiy) = $DB->next_record();


$DB->prepared_query("SELECT COUNT(*) FROM forums_topics WHERE AuthorID = ?", $UserID);
list($ForumTopics) = $DB->fetch_record();

$OverrideClass = $Override === 2 ? 'paranoia_override' : '';

?>
<div>
    <div class="Group-body-row">
        <div class="SidebarItemUserCoummunity SidebarItem Box Group-column">
            <div class="Box-header">
                <?= t('server.common.forums') ?>
            </div>
            <ul class="SidebarList SidebarItem-body Box-body">
                <li class="SidebarList-item isForumTopicCount" id="forum-topic-count-value" data-value="<?= $ForumTopics ?>">
                    <span>
                        <?= t('server.user.community_topic') ?>
                        : </span>
                    <a class="brackets" href="userhistory.php?action=topics&amp;userid=<?= $UserID ?>">
                        <?= number_format($ForumTopics) ?>
                    </a>
                </li>
                <li class="SidebarList-item isForumThreadCount" id="forum-thread-count-value" data-value="<?= $ForumPosts ?>">
                    <span>
                        <?= t('server.user.community_pots') ?>
                        : </span>
                    <a class="brackets" href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>&showunread=0&group=0">
                        <span data-tooltip="<?= !empty($PostRank) ?  t('server.user.all_site') . ' ' . number_format($PostRank) . '%' : '' ?>"><?= number_format($ForumPosts) ?></span>
                    </a>
                </li>
                <? if ($Override = check_paranoia_here('torrentcomments+')) { ?>
                    <li class="SidebarList-item isTorrentCommentCount <?= $OverrideClass ?>" id="torrent-comment-count-value" data-value="<?= $NumComments ?>">
                        <span>
                            <?= t('server.user.community_comms') ?>:
                        </span>
                        <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                            <a href="comments.php?id=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                                <?= number_format($NumComments) ?>
                            </a>
                        <? } else { ?>
                            <?= number_format($NumComments) ?>
                        <? } ?>
                    </li>
                    <li class="SidebarList-item isArtistCommentCount <?= $OverrideClass ?>" id="artist-comment-count-value" data-value="<?= $NumArtistComments ?>">
                        <span>
                            <?= t('server.user.community_arts') ?>:
                        </span>
                        <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                            <a href="comments.php?id=<?= $UserID ?>&amp;action=artist" class="brackets <?= $OverrideClass ?>">
                                <?= number_format($NumArtistComments) ?>
                            </a>
                        <? } else { ?>
                            <?= number_format($NumArtistComments) ?>
                        <? } ?>
                    </li>
                    <? if (CONFIG['ENABLE_COLLAGES']) { ?>
                        <li class="SidebarList-item isCollageCommentCount <?= $OverrideClass ?>" id="collage-comment-count-value" data-value="<?= $NumCollageComments ?>">
                            <span>
                                <?= t('server.user.community_colls') ?>: </span>
                            <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                                <a href="comments.php?id=<?= $UserID ?>&amp;action=collages" class="brackets <?= $OverrideClass ?>">
                                    <?= number_format($NumCollageComments) ?>
                                </a>
                            <? } else { ?>
                                <?= number_format($NumCollageComments) ?>
                            <? } ?>
                        </li>
                    <?  } ?>
                    <li class="SidebarList-item isReqeustCommentCount <?= $OverrideClass ?>" id="request-comment-count-value" data-value="<?= $NumRequestComments ?>">
                        <span><?= t('server.user.community_reqs') ?>: </span>
                        <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                            <a href="comments.php?id=<?= $UserID ?>&amp;action=requests" class="brackets <?= $OverrideClass ?>">
                                <?= number_format($NumRequestComments) ?>
                            </a>
                        <? } else { ?>
                            <?= number_format($NumRequestComments) ?>
                        <? } ?>
                    </li>
                <? } ?>
            </ul>
        </div>
        <?
        $hasTorrentItem = false;
        ?>

        <div class="SidebarItemUserCoummunity SidebarItem Box Group-column">
            <div class="Box-header">
                <?= t('server.common.torrents') ?>
            </div>
            <ul class="SidebarList SidebarItem-body Box-body">
                <?
                //Let's see if we can view requests because of reasons
                $ViewAll    = check_paranoia_here('requestsfilled_list');
                $ViewCount  = check_paranoia_here('requestsfilled_count');
                $ViewBounty = check_paranoia_here('requestsfilled_bounty');

                if ($ViewCount && !$ViewBounty && !$ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item isRequestFill" id="requst-fill-value" data-value="<?= $RequestsFilled ?>">
                        <span><?= t('server.user.requestsfilled') ?>: </span>
                        <span data-tooltip="<?= empty($RequestRank) ? '' : t('server.user.all_site') . ' ' . number_format($RequestRank) . '%' ?>"><?= number_format($RequestsFilled) ?> </span>
                    </li>
                <?  } elseif (!$ViewCount && $ViewBounty && !$ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item">
                        <span><?= t('server.user.requestsfilled') ?>: </span>
                        <?= Format::get_size($TotalBounty) ?>
                        <?= t('server.user.collected') ?>
                    </li>
                <?  } elseif ($ViewCount && $ViewBounty && !$ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item">
                        <span><?= t('server.user.requestsfilled') ?>: </span>
                        <span data-tooltip="<?= empty($RequestRank) ? '' : t('server.user.all_site') . ' ' . number_format($RequestRank) . '%' ?>"><?= number_format($RequestsFilled) ?></span>
                        <span>
                            (<?= t('server.user.for') ?>
                            <?= Format::get_size($TotalBounty) ?>)
                        </span>
                    </li>
                <?  } elseif ($ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item">
                        <span class="<?= ($ViewCount === 2 ? 'paranoia_override' : '') ?>">
                            <span><?= t('server.user.requestsfilled') ?>: </span>
                            <a href="requests.php?type=filled&amp;userid=<?= $UserID ?>" class="brackets <?= (($ViewAll === 2) ? ' paranoia_override' : '') ?>">
                                <span data-tooltip="<?= empty($RequestRank) ? '' : t('server.user.all_site') . ' ' . number_format($RequestRank) . '%' ?>"> <?= number_format($RequestsFilled) ?></span>
                            </a>
                        </span>
                        <span class="<?= ($ViewBounty === 2 ? 'paranoia_override' : '') ?>">
                            (<?= t('server.user.for') ?>
                            <?= Format::get_size($TotalBounty) ?>)
                        </span>

                    </li>
                <? } ?>

                <?
                //Let's see if we can view requests because of reasons
                $ViewAll    = check_paranoia_here('requestsvoted_list');
                $ViewCount  = check_paranoia_here('requestsvoted_count');
                $ViewBounty = check_paranoia_here('requestsvoted_bounty');

                if ($ViewCount && !$ViewBounty && !$ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item"><?= t('server.user.requestscreated') ?>: <?= number_format($RequestsCreated) ?></li>
                    <li class="SidebarList-item"><?= t('server.user.requestsvoted') ?> <?= number_format($RequestsVoted) ?></li>
                <?  } elseif (!$ViewCount && $ViewBounty && !$ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item"><?= t('server.user.requestscreated') ?>: <?= Format::get_size($RequestsCreatedSpent) ?> <?= t('server.user.spent') ?></li>
                    <li class="SidebarList-item"><?= t('server.user.requestsvoted') ?> <span data-tooltip="<?= empty($BountyRank) ? '' : t('server.user.all_site') .  ' ' . number_format($BountyRank) . '%' ?>"><?= Format::get_size($TotalSpent) ?></span> <?= t('server.user.spent') ?></li>
                <?  } elseif ($ViewCount && $ViewBounty && !$ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item"><?= t('server.user.requestscreated') ?>: <?= number_format($RequestsCreated) ?> <?= t('server.user.for') ?> <?= Format::get_size($RequestsCreatedSpent) ?></li>
                    <li class="SidebarList-item"><?= t('server.user.requestsvoted') ?> <?= number_format($RequestsVoted) ?> <?= t('server.user.for') ?><span data-tooltip="<?= empty($BountyRank) ? '' : t('server.user.all_site') .  ' ' . number_format($BountyRank) . '%' ?>"> <?= Format::get_size($TotalSpent) ?></span></li>
                <?  } elseif ($ViewAll) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item">
                        <span class="<?= ($ViewCount === 2 ? 'paranoia_override' : '') ?>"><?= t('server.user.requestscreated') ?>:
                            <a href="requests.php?type=created&amp;userid=<?= $UserID ?>" class="brackets<?= ($ViewAll === 2 ? ' paranoia_override' : '') ?>">
                                <?= number_format($RequestsCreated) ?>
                            </a>
                        </span>
                        <span class="<?= ($ViewBounty === 2 ? 'paranoia_override' : '') ?>"> (<?= t('server.user.for') ?> <?= Format::get_size($RequestsCreatedSpent) ?>)</span>
                    </li>
                    <li class="SidebarList-item">
                        <span class="<?= ($ViewCount === 2 ? 'paranoia_override' : '') ?>"><?= t('server.user.requestsvoted') ?>:
                            <a href="requests.php?type=voted&amp;userid=<?= $UserID ?>" class="brackets<?= ($ViewAll === 2 ? ' paranoia_override' : '') ?>">
                                <?= number_format($RequestsVoted) ?>
                            </a>
                        </span>
                        <span class="<?= ($ViewBounty === 2 ? 'paranoia_override' : '') ?>">(<?= t('server.user.for') ?> <?= Format::get_size($TotalSpent) ?>)</span>
                    </li>
                <? } ?>
                <?
                if ($CanViewUploads || $Override = check_paranoia_here('uploads+')) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item isUploadCount <?= $OverrideClass ?>" id="upload-count-value" data-value="<?= $Uploads ?>">
                        <?= t('server.user.comm_upload') ?>:
                        <a data-tooltip="<?= empty($UploadsRank) ? '' : t('server.user.all_site') . ' ' . number_format($UploadsRank)  . '%' ?>" class="brackets <?= $OverrideClass ?>" href="torrents.php?type=uploaded&amp;userid=<?= $UserID ?>">
                            <?= number_format($Uploads) ?>
                        </a>
                        <? if ($TotalUploads) { ?>
                            <span data-tooltip="<?= t('server.user.total_uploads_title') ?>">
                                (<?= $TotalUploads ?>)
                            </span>
                        <? } ?>
                        <? if ($CanViewUploads || $Override = check_paranoia_here('uploads')) { ?>
                            <? if (check_perms('zip_downloader')) { ?>
                                [<a class="brackets <?= $OverrideClass ?>" href="torrents.php?action=redownload&amp;type=uploads&amp;userid=<?= $UserID ?>" onclick="return confirm('<?= t('server.user.redownloading_confirm') ?>');">
                                    <?= t('server.user.community_dl') ?>
                                </a>]
                            <? } ?>
                        <? } ?>

                    </li>
                <? } ?>
                <?
                if ($CanViewUploads || $Override = check_paranoia_here('originals+')) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item isOriginalUploadCount <?= $OverrideClass ?>" id="original-upload-count-value" data-value-buy="<?= $OriginalsBuy ?>" data-value-diy="<?= $OriginalsDiy ?>">
                        <?= t('server.user.comm_originals') ?>:
                        <span data-tooltip="<?= t('server.user.self_purchase_number') ?>">
                            <?= number_format($OriginalsBuy) ?>
                        </span>
                        +
                        <span data-tooltip="<?= t('server.user.self_rip_number') ?>">
                            <?= number_format($OriginalsDiy) ?>
                        </span>
                    </li>
                <?
                }
                if ($Override = check_paranoia_here('seeding+')) {
                    $hasTorrentItem = true
                ?>
                    <li class="SidebarList-item <?= $OverrideClass ?>">
                        <?= t('server.user.comm_seeding') ?>:
                        <span class="user_commstats" id="user_commstats_seeding">
                            <a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;">
                                <?= t('server.user.community_show') ?>
                            </a>
                        </span>
                        <?
                        if ($Override = check_paranoia_here('snatched+')) { ?>
                            <span class="<?= $OverrideClass ?>"></span>
                        <?
                        }
                        if ($Override = check_paranoia_here('seeding')) {
                        ?>
                            [<a href="torrents.php?type=seeding&amp;userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                                <?= t('server.user.view') ?>
                            </a>
                            <?
                            if (check_perms('zip_downloader')) {
                            ?>
                                |<a class="brackets" href="torrents.php?action=redownload&amp;type=seeding&amp;userid=<?= $UserID ?>" onclick="return confirm('<?= t('server.user.redownloading_confirm') ?>');">
                                    <?= t('server.user.community_dl') ?>
                                </a>
                                <?
                            }
                                ?>]
                            <?
                        } ?>
                    </li>
                <?
                }
                if ($Override = check_paranoia_here('leeching+')) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item <?= $OverrideClass ?>">
                        <?= t('server.user.comm_leeching') ?>:
                        <span class="user_commstats" id="user_commstats_leeching">
                            <a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;">
                                <?= t('server.user.community_show') ?>
                            </a>
                        </span>
                        <? if ($Override = check_paranoia_here('leeching')) { ?>
                            [<a href="torrents.php?type=leeching&amp;userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                                <?= t('server.user.view') ?>
                            </a>]
                        <? } ?>
                        <? if ($DisableLeech == 0 && check_perms('users_view_ips')) { ?>
                            <strong>(Disabled)</strong>
                        <? } ?>
                    </li>
                <?
                }
                if ($Override = check_paranoia_here('snatched+')) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item <?= $OverrideClass ?>">
                        <?= t('server.user.comm_snatched') ?>:
                        <span class="user_commstats" id="user_commstats_snatched"><a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;"><?= t('server.user.community_show') ?></a></a></span>
                        <? if ($Override = check_perms('site_view_torrent_snatchlist', $Class)) { ?>
                            <span id="user_commstats_usnatched" <?= ($Override === 2 ? ' class="paranoia_override"' : '') ?>></span>
                        <?
                        }
                    }
                    if ($Override = check_paranoia_here('snatched')) {
                        $hasTorrentItem = true ?>
                        [<a href="torrents.php?type=snatched&amp;userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>"><?= t('server.user.view') ?></a>
                        <? if (check_perms('zip_downloader')) { ?>
                            |<a href="torrents.php?action=redownload&amp;type=snatches&amp;userid=<?= $UserID ?>" onclick="return confirm('<?= t('server.user.redownloading_confirm') ?>');" class="brackets"><?= t('server.user.community_dl') ?></a></a>
                            <? } ?>]
                    </li>
                <?  } ?>
                <? if (check_perms('site_view_torrent_snatchlist', $Class)) {
                    $hasTorrentItem = true ?>
                    <li class="SidebarList-item" id="comm_downloaded">
                        <?= t('server.user.comm_downloaded') ?>:
                        <span class="user_commstats" id="user_commstats_downloaded">
                            <a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;">
                                <?= t('server.user.community_show') ?>
                            </a>
                        </span>
                        <span id="user_commstats_udownloaded"></span>
                        [<a href="torrents.php?type=downloaded&amp;userid=<?= $UserID ?>" class="brackets">
                            <?= t('server.user.view') ?>
                        </a>]
                    </li>
                <? } ?>
                <?
                if (!$hasTorrentItem) {
                ?>
                    <li class="SidebarList-item"><?= t('server.common.no_results') ?></li>
                <?
                } ?>
            </ul>
            <?
            if ($OwnProfile || check_perms('users_override_paranoia')) {
                $hasTorrentItem = true ?>
                <?
                $DB->query("SELECT xs.uid, xs.tstamp, xs.fid, t.Size FROM xbt_snatched AS xs left join torrents AS t ON t.ID = xs.fid WHERE xs.uid =" . $UserID . " and xs.tstamp >= unix_timestamp(date_format(now(),'%Y-%m-01')) order by 2");
                $Requests = $DB->to_array();
                $SnatchedByUser;
                foreach ($Requests as $Request) {
                    list($userID, $Time, $TorrentID, $Size) = $Request;
                    if (!isset($SnatchedByUser[$userID][$TorrentID])) {
                        $SnatchedByUser[$userID][$TorrentID]['size'] = $Size;
                        $SnatchedByUser[$userID][$TorrentID]['free'] = 0;
                        $SnatchedByUser[$userID][$TorrentID]['unfree'] = 0;
                    }
                    $SnatchedByUser[$userID][$TorrentID]['time'][] = $Time;
                }
                $DB->query("SELECT `UserID`, `TorrentID`, unix_timestamp(`Time`)
                FROM `users_freeleeches_time`
                WHERE UserID=$UserID and unix_timestamp(Time) >= unix_timestamp(date_format(now(),'%Y-%m-01')) order by 3");
                $Requests = $DB->to_array();
                $TokenByUser;
                foreach ($Requests as $Request) {
                    list($UserID, $TorrentID, $Time) = $Request;
                    $TokenByUser[$UserID][$TorrentID][] = $Time;
                }
                $UsersCnt;
                foreach ($SnatchedByUser as $UserID => &$Torrents) {
                    $UsersCnt[$UserID]['size'] = 0;
                    $UsersCnt[$UserID]['cnt'] = 0;
                    foreach ($Torrents as $TorrentID => &$Torrent) {
                        if (isset($TokenByUser[$UserID][$TorrentID])) {
                            foreach ($Torrent['time'] as $Time) {
                                $free = false;
                                foreach ($TokenByUser[$UserID][$TorrentID] as $key => $TokenTime) {
                                    if ($Time < $TokenTime + 345600) {
                                        unset($TokenByUser[$UserID][$TorrentID][$key]);
                                        $Torrent['free'] += 1;
                                        $free = true;
                                        break;
                                    }
                                }
                                if (!$free) {
                                    $Torrent['unfree'] += 1;
                                }
                            }
                        } else {
                            $Torrent['unfree'] = 1;
                        }
                    }
                }
                $DB->query("select u.ID, u.Downloaded ND, l.Downloaded LD, TorrentCnt LT from users_main as u left join users_last_month as l on u.ID=l.ID where u.ID = $UserID");
                $Requests = $DB->to_array();
                foreach ($Requests as $User) {
                    list($ID, $ND, $LD, $LT) = $User;
                    $UsersCnt[$ID]['dt'] = $ND - $LD;
                }
                unset($Torrents, $Torrent);
                foreach ($SnatchedByUser as $UserID => $Torrents) {
                    foreach ($Torrents as $Torrent) {
                        if ($Torrent['unfree']) {
                            $UsersCnt[$UserID]['size'] += $Torrent['size'];
                            if ($Torrent['size']) {
                                $UsersCnt[$UserID]['cnt']++;
                            }
                        }
                    }
                }
                $Criteria = array();
                $Criteria[] = array('ddt' => 500 * 250 * 1024 * 1024, 'tdt' => 500, 'token' => 50, 'bonus' => 6000);
                $Criteria[] = array('ddt' => 320 * 250 * 1024 * 1024, 'tdt' => 320, 'token' => 32, 'bonus' => 2800);
                $Criteria[] = array('ddt' => 200 * 250 * 1024 * 1024, 'tdt' => 200, 'token' => 20, 'bonus' => 1300);
                $Criteria[] = array('ddt' => 120 * 250 * 1024 * 1024, 'tdt' => 120, 'token' => 12, 'bonus' => 600);
                $Criteria[] = array('ddt' => 60 * 250 * 1024 * 1024, 'tdt' => 60, 'token' => 6, 'bonus' => 240);
                $Criteria[] = array('ddt' => 25 * 250 * 1024 * 1024, 'tdt' => 25, 'token' => 2, 'bonus' => 100);
                $Criteria[] = array('ddt' => 10 * 250 * 1024 * 1024, 'tdt' => 10, 'token' => 1, 'bonus' => 0);
                foreach ($UsersCnt as $UserID => $User) {
                    $LogSize = min($User['size'], $User['dt']);
                    foreach ($Criteria as $L) {
                        if ($LogSize >= $L['ddt'] && $User['cnt'] >= $L['tdt']) {
                            break;
                        }
                    }
                }
                ?>
            <?
            }

            ?>
            <? if ($LoggedUser['AutoloadCommStats']) { ?>
                <script type="text/javascript">
                    commStats(<?= $UserID ?>);
                </script>
            <?  } ?>
        </div>
        <?
        $hasOther = false;
        ?>
        <div class="SidebarItemUserCoummunity SidebarItem Box Group-column">
            <div class="Box-header">
                <?= t('server.common.others') ?>
            </div>

            <ul class="SidebarList SidebarItem-body Box-body">
                <?
                if (($Override = check_paranoia_here('collages+')) && CONFIG['ENABLE_COLLAGES']) {
                    $hasOther = true;
                ?>
                    <li class="SidebarList-item isCollageCreateCount <?= $OverrideClass ?>" id="collage-create-count-value" data-value="<?= $NumCollages ?>">
                        <span><?= t('server.user.community_collstart') ?>: </span>
                        <? if ($Override = check_paranoia_here('collages')) { ?>
                            <a href="collages.php?userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                                <?= number_format($NumCollages) ?>
                            </a>
                        <? } else { ?>
                            <?= number_format($NumCollages) ?>
                        <? } ?>
                    </li>
                <?
                }
                if (($Override = check_paranoia_here('collagecontribs+')) && CONFIG['ENABLE_COLLAGES']) {
                    $hasOther = true; ?>
                    <li class="SidebarList-item isCollageContributeCount <?= $OverrideClass ?>" id="collage-countribute-count-value" data-value="<?= $NumCollageContribs ?>">
                        <span><?= t('server.user.community_collcontrib') ?>: </span>
                        <? if ($Override = check_paranoia_here('collagecontribs')) { ?>
                            <a href="collages.php?userid=<?= $UserID ?>&amp;contrib=1" class="brackets <?= $OverrideClass ?>">
                                <?= number_format($NumCollageContribs) ?>
                            </a>
                        <? } else { ?>
                            <?= number_format($NumCollageContribs) ?>
                        <? } ?>
                    </li>
                <? }
                ?>
                <?
                if (($Override = check_paranoia_here('artistsadded'))) {
                    $hasOther = true; ?>
                    <li class="SidebarList-item" , id="artists_added">
                        <span><?= t('server.user.u_artist') ?>: </span>
                        <span data-tooltip="<?= !empty($ArtistsRank) ? t('server.user.all_site') . ' ' . $ArtistsRank . '%' : '' ?>">
                            <?= number_format($ArtistsAdded) ?>
                        </span>
                    </li>
                <?
                }
                if ($Override = check_paranoia_here('invitedcount')) {
                    $hasOther = true;
                    $DB->query("SELECT COUNT(UserID) FROM users_info WHERE Inviter = '$UserID'");
                    list($Invited) = $DB->next_record();
                ?>
                    <li class="SidebarList-item" id="comm_invited">
                        <?= t('server.user.comm_invited') ?>:
                        <a href="user.php?action=invite&amp;userid=<?= $UserID ?>"><?= number_format($Invited) ?></a>
                    </li>
                <?
                }
                ?>
                <?
                if (!$hasOther) {
                ?>
                    <li class="SidebarList-item"><?= t('server.common.no_results') ?></li>
                <?
                }
                ?>
            </ul>
        </div>
    </div>
</div>