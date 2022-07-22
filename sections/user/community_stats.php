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
<div class="SidebarItemUserCoummunity SidebarItem Box">
    <div class="SidebarItem-header Box-header">
        <?= Lang::get('user.community') ?>
    </div>
    <ul class="SidebarList SidebarItem-body Box-body">
        <li class="SidebarList-item isForumTopicCount" id="forum-topic-count-value" data-value="<?= $ForumTopics ?>">
            <span><?= Lang::get('user.community_topic') ?>: </span>
            <?= number_format($ForumTopics) ?>
            <a class="brackets" href="userhistory.php?action=topics&amp;userid=<?= $UserID ?>">
                <?= Lang::get('user.view') ?>
            </a>
        </li>
        <li class="SidebarList-item isForumThreadCount" id="forum-thread-count-value" data-value="<?= $ForumPosts ?>">
            <span><?= Lang::get('user.community_pots') ?>: </span>
            <?= number_format($ForumPosts) ?>
            <a class="brackets" href="userhistory.php?action=posts&amp;userid=<?= $UserID ?>">
                <?= Lang::get('user.view') ?>
            </a>
        </li>
        <? if ($Override = check_paranoia_here('torrentcomments+')) { ?>
            <li class="SidebarList-item isTorrentCommentCount <?= $OverrideClass ?>" id="torrent-comment-count-value" data-value="<?= $NumComments ?>">
                <span><?= Lang::get('user.community_comms') ?>: </span>
                <?= number_format($NumComments) ?>
                <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                    <a href="comments.php?id=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                <? } ?>
            </li>
            <li class="SidebarList-item isArtistCommentCount <?= $OverrideClass ?>" id="artist-comment-count-value" data-value="<?= $NumArtistComments ?>">
                <span><?= Lang::get('user.community_arts') ?>: </span>
                <?= number_format($NumArtistComments) ?>
                <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                    <a href="comments.php?id=<?= $UserID ?>&amp;action=artist" class="brackets <?= $OverrideClass ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                <? } ?>
            </li>
            <? if (CONFIG['ENABLE_COLLAGES']) { ?>
                <li class="SidebarList-item isCollageCommentCount <?= $OverrideClass ?>" id="collage-comment-count-value" data-value="<?= $NumCollageComments ?>">
                    <span><?= Lang::get('user.community_colls') ?>: </span>
                    <?= number_format($NumCollageComments) ?>
                    <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                        <a href="comments.php?id=<?= $UserID ?>&amp;action=collages" class="brackets <?= $OverrideClass ?>">
                            <?= Lang::get('user.view') ?>
                        </a>
                    <? } ?>
                </li>
            <?  } ?>
            <li class="SidebarList-item isReqeustCommentCount <?= $OverrideClass ?>" id="request-comment-count-value" data-value="<?= $NumRequestComments ?>">
                <span><?= Lang::get('user.community_reqs') ?>: </span>
                <?= number_format($NumRequestComments) ?>
                <? if ($Override = check_paranoia_here('torrentcomments')) { ?>
                    <a href="comments.php?id=<?= $UserID ?>&amp;action=requests" class="brackets <?= $OverrideClass ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                <? } ?>
            </li>
        <? } ?>
        <?
        if (($Override = check_paranoia_here('collages+')) && CONFIG['ENABLE_COLLAGES']) { ?>
            <li class="SidebarList-item isCollageCreateCount <?= $OverrideClass ?>" id="collage-create-count-value" data-value="<?= $NumCollages ?>">
                <span><?= Lang::get('user.community_collstart') ?>: </span>
                <?= number_format($NumCollages) ?>
                <? if ($Override = check_paranoia_here('collages')) { ?>
                    <a href="collages.php?userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                <? } ?>
            </li>
        <?
        }
        if (($Override = check_paranoia_here('collagecontribs+')) && CONFIG['ENABLE_COLLAGES']) { ?>
            <li class="SidebarList-item isCollageContributeCount <?= $OverrideClass ?>" id="collage-countribute-count-value" data-value="<?= $NumCollageContribs ?>">
                <span><?= Lang::get('user.community_collcontrib') ?>: </span>
                <?= number_format($NumCollageContribs) ?>
                <? if ($Override = check_paranoia_here('collagecontribs')) { ?>
                    <a href="collages.php?userid=<?= $UserID ?>&amp;contrib=1" class="brackets <?= $OverrideClass ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                <? } ?>
            </li>
        <? } ?>

        <?
        //Let's see if we can view requests because of reasons
        $ViewAll    = check_paranoia_here('requestsfilled_list');
        $ViewCount  = check_paranoia_here('requestsfilled_count');
        $ViewBounty = check_paranoia_here('requestsfilled_bounty');

        if ($ViewCount && !$ViewBounty && !$ViewAll) { ?>
            <li class="SidebarList-item isRequestFill" id="requst-fill-value" data-value="<?= $RequestsFilled ?>">
                <span><?= Lang::get('user.requestsfilled') ?>: </span>
                <?= number_format($RequestsFilled) ?>
            </li>
        <?  } elseif (!$ViewCount && $ViewBounty && !$ViewAll) { ?>
            <li class="SidebarList-item">
                <span><?= Lang::get('user.requestsfilled') ?>: </span>
                <?= Format::get_size($TotalBounty) ?>
                <?= Lang::get('user.collected') ?>
            </li>
        <?  } elseif ($ViewCount && $ViewBounty && !$ViewAll) { ?>
            <li class="SidebarList-item">
                <span><?= Lang::get('user.requestsfilled') ?>: </span>
                <?= number_format($RequestsFilled) ?>
                <?= Lang::get('user.for') ?>
                <?= Format::get_size($TotalBounty) ?>
            </li>
        <?  } elseif ($ViewAll) { ?>
            <li class="SidebarList-item">
                <span class="<?= ($ViewCount === 2 ? 'paranoia_override' : '') ?>">
                    <span><?= Lang::get('user.requestsfilled') ?>: </span>
                    <?= number_format($RequestsFilled) ?>
                </span>
                <span class="<?= ($ViewBounty === 2 ? 'paranoia_override' : '') ?>">
                    <?= Lang::get('user.for') ?>
                    <?= Format::get_size($TotalBounty) ?>
                </span>
                <a href="requests.php?type=filled&amp;userid=<?= $UserID ?>" class="brackets <?= (($ViewAll === 2) ? ' paranoia_override' : '') ?>">
                    <?= Lang::get('user.view') ?>
                </a>
            </li>
        <? } ?>

        <?
        //Let's see if we can view requests because of reasons
        $ViewAll    = check_paranoia_here('requestsvoted_list');
        $ViewCount  = check_paranoia_here('requestsvoted_count');
        $ViewBounty = check_paranoia_here('requestsvoted_bounty');

        if ($ViewCount && !$ViewBounty && !$ViewAll) { ?>
            <li class="SidebarList-item"><?= Lang::get('user.requestscreated') ?>: <?= number_format($RequestsCreated) ?></li>
            <li class="SidebarList-item"><?= Lang::get('user.requestsvoted') ?> <?= number_format($RequestsVoted) ?></li>
        <?  } elseif (!$ViewCount && $ViewBounty && !$ViewAll) { ?>
            <li class="SidebarList-item"><?= Lang::get('user.requestscreated') ?>: <?= Format::get_size($RequestsCreatedSpent) ?> <?= Lang::get('user.spent') ?></li>
            <li class="SidebarList-item"><?= Lang::get('user.requestsvoted') ?> <?= Format::get_size($TotalSpent) ?> <?= Lang::get('user.spent') ?></li>
        <?  } elseif ($ViewCount && $ViewBounty && !$ViewAll) { ?>
            <li class="SidebarList-item"><?= Lang::get('user.requestscreated') ?>: <?= number_format($RequestsCreated) ?> <?= Lang::get('user.for') ?> <?= Format::get_size($RequestsCreatedSpent) ?></li>
            <li class="SidebarList-item"><?= Lang::get('user.requestsvoted') ?> <?= number_format($RequestsVoted) ?> <?= Lang::get('user.for') ?> <?= Format::get_size($TotalSpent) ?></li>
        <?  } elseif ($ViewAll) { ?>
            <li class="SidebarList-item">
                <span class="<?= ($ViewCount === 2 ? 'paranoia_override' : '') ?>"><?= Lang::get('user.requestscreated') ?>: <?= number_format($RequestsCreated) ?></span>
                <span class="<?= ($ViewBounty === 2 ? 'paranoia_override' : '') ?>"> <?= Lang::get('user.for') ?> <?= Format::get_size($RequestsCreatedSpent) ?></span>
                <a href="requests.php?type=created&amp;userid=<?= $UserID ?>" class="brackets<?= ($ViewAll === 2 ? ' paranoia_override' : '') ?>">
                    <?= Lang::get('user.view') ?>
                </a>
            </li>
            <li class="SidebarList-item">
                <span class="<?= ($ViewCount === 2 ? 'paranoia_override' : '') ?>"><?= Lang::get('user.requestsvoted') ?>: <?= number_format($RequestsVoted) ?></span>
                <span class="<?= ($ViewBounty === 2 ? 'paranoia_override' : '') ?>"><?= Lang::get('user.for') ?> <?= Format::get_size($TotalSpent) ?></span>
                <a href="requests.php?type=voted&amp;userid=<?= $UserID ?>" class="brackets<?= ($ViewAll === 2 ? ' paranoia_override' : '') ?>">
                    <?= Lang::get('user.view') ?>
                </a>
            </li>
        <? } ?>
        <?
        if ($CanViewUploads || $Override = check_paranoia_here('uploads+')) { ?>
            <li class="SidebarList-item isUploadCount <?= $OverrideClass ?>" id="upload-count-value" data-value="<?= $Uploads ?>">
                <?= Lang::get('user.comm_upload') ?>:
                <?= number_format($Uploads) ?>
                <? if ($TotalUploads) { ?>
                    <span data-tooltip="<?= Lang::get('user.total_uploads_title') ?>">
                        (<?= $TotalUploads ?>)
                    </span>
                <? } ?>
                <? if ($CanViewUploads || $Override = check_paranoia_here('uploads')) { ?>
                    <a class="brackets <?= $OverrideClass ?>" href="torrents.php?type=uploaded&amp;userid=<?= $UserID ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                    <? if (check_perms('zip_downloader')) { ?>
                        <a class="brackets <?= $OverrideClass ?>" href="torrents.php?action=redownload&amp;type=uploads&amp;userid=<?= $UserID ?>" onclick="return confirm('<?= Lang::get('user.redownloading_confirm') ?>');">
                            <?= Lang::get('user.community_dl') ?>
                        </a>
                    <? } ?>
                <? } ?>
            </li>
        <? } ?>
        <?
        if ($CanViewUploads || $Override = check_paranoia_here('originals+')) { ?>
            <li class="SidebarList-item isOriginalUploadCount <?= $OverrideClass ?>" id="original-upload-count-value" data-value-buy="<?= $OriginalsBuy ?>" data-value-diy="<?= $OriginalsDiy ?>">
                <?= Lang::get('user.comm_originals') ?>:
                <span data-tooltip="<?= Lang::get('user.self_purchase_number') ?>">
                    <?= number_format($OriginalsBuy) ?>
                </span>
                +
                <span data-tooltip="<?= Lang::get('user.self_rip_number') ?>">
                    <?= number_format($OriginalsDiy) ?>
                </span>
            </li>
        <?
        }
        if ($Override = check_paranoia_here('seeding+')) {
        ?>
            <li class="SidebarList-item <?= $OverrideClass ?>">
                <?= Lang::get('user.comm_seeding') ?>:
                <span class="user_commstats" id="user_commstats_seeding">
                    <a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;">
                        <?= Lang::get('user.community_show') ?>
                    </a>
                </span>
                <? if ($Override = check_paranoia_here('snatched+')) { ?>
                    <span class="<?= $OverrideClass ?>"></span>
                <? } ?>
                <? if ($Override = check_paranoia_here('seeding')) { ?>
                    <a href="torrents.php?type=seeding&amp;userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                    <? if (check_perms('zip_downloader')) { ?>
                        <a class="brackets" href="torrents.php?action=redownload&amp;type=seeding&amp;userid=<?= $UserID ?>" onclick="return confirm('<?= Lang::get('user.redownloading_confirm') ?>');">
                            <?= Lang::get('user.community_dl') ?>
                        </a>
                    <? } ?>
                <? } ?>
            </li>
        <?  } ?>
        <? if ($Override = check_paranoia_here('leeching+')) { ?>
            <li class="SidebarList-item <?= $OverrideClass ?>">
                <?= Lang::get('user.comm_leeching') ?>:
                <span class="user_commstats" id="user_commstats_leeching">
                    <a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;">
                        <?= Lang::get('user.community_show') ?>
                    </a>
                </span>
                <? if ($Override = check_paranoia_here('leeching')) { ?>
                    <a href="torrents.php?type=leeching&amp;userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>">
                        <?= Lang::get('user.view') ?>
                    </a>
                <? } ?>
                <? if ($DisableLeech == 0 && check_perms('users_view_ips')) { ?>
                    <strong>(Disabled)</strong>
                <? } ?>
            </li>
        <?  } ?>
        <? if ($Override = check_paranoia_here('snatched+')) { ?>
            <li class="SidebarList-item <?= $OverrideClass ?>">
                <?= Lang::get('user.comm_snatched') ?>:
                <span class="user_commstats" id="user_commstats_snatched"><a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;"><?= Lang::get('user.community_show') ?></a></a></span>
                <? if ($Override = check_perms('site_view_torrent_snatchlist', $Class)) { ?>
                    <span id="user_commstats_usnatched" <?= ($Override === 2 ? ' class="paranoia_override"' : '') ?>></span>
                <?
                }
            }
            if ($Override = check_paranoia_here('snatched')) { ?>
                <a href="torrents.php?type=snatched&amp;userid=<?= $UserID ?>" class="brackets <?= $OverrideClass ?>"><?= Lang::get('user.view') ?></a>
                <? if (check_perms('zip_downloader')) { ?>
                    <a href="torrents.php?action=redownload&amp;type=snatches&amp;userid=<?= $UserID ?>" onclick="return confirm('<?= Lang::get('user.redownloading_confirm') ?>');" class="brackets"><?= Lang::get('user.community_dl') ?></a></a>
                <? } ?>
            </li>
        <?  } ?>
        <? if (check_perms('site_view_torrent_snatchlist', $Class)) { ?>
            <li class="SidebarList-item" id="comm_downloaded">
                <?= Lang::get('user.comm_downloaded') ?>:
                <span class="user_commstats" id="user_commstats_downloaded">
                    <a href="#" class="brackets" onclick="commStats(<?= $UserID ?>); return false;">
                        <?= Lang::get('user.community_show') ?>
                    </a>
                </span>
                <span id="user_commstats_udownloaded"></span>
                <a href="torrents.php?type=downloaded&amp;userid=<?= $UserID ?>" class="brackets">
                    <?= Lang::get('user.view') ?>
                </a>
            </li>
        <? } ?>
        <?
        if ($Override = check_paranoia_here('invitedcount')) {
            $DB->query("
		SELECT COUNT(UserID)
		FROM users_info
		WHERE Inviter = '$UserID'");
            list($Invited) = $DB->next_record();
        ?>
            <li class="SidebarList-item" id="comm_invited">
                <?= Lang::get('user.comm_invited') ?>:
                <?= number_format($Invited) ?>
            </li>
        <? } ?>
    </ul>
    <? if ($LoggedUser['AutoloadCommStats']) { ?>
        <script type="text/javascript">
            commStats(<?= $UserID ?>);
        </script>
    <?  } ?>
</div>