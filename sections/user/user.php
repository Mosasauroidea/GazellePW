<?

use Gazelle\Manager\Donation;

include(CONFIG['SERVER_ROOT'] . '/classes/torrenttable.class.php');

if (empty($_GET['id']) || !is_number($_GET['id']) || (!empty($_GET['preview']) && !is_number($_GET['preview']))) {
    error(404);
}

$UserID = (int)$_GET['id'];
$Preview = isset($_GET['preview']) ? $_GET['preview'] : 0;
if ($UserID == $LoggedUser['ID']) {
    $OwnProfile = true;
    if ($Preview == 1) {
        $OwnProfile = false;
        $ParanoiaString = $_GET['paranoia'];
        $CustomParanoia = explode(',', $ParanoiaString);
    }
    $FL_Items = [];
} else {
    $OwnProfile = false;
    //Don't allow any kind of previewing on others' profiles
    $Preview = 0;
    $Bonus = new \Gazelle\Bonus($DB, $Cache);
    $FL_Items = $Bonus->getListOther($UserID, G::$LoggedUser['BonusPoints']);
}

// 捐赠信息
$donation = new Donation();
$donationInfo = $donation->info($UserID);
$leaderBoardRank = $donation->leaderboardRank($UserID);
$donorVisible = $donation->isVisible($UserID);
$isDonor = $donation->isDonor($UserID);
$EnableRewards = $donation->enabledRewards($UserID);
$ProfileRewards = $donation->profileRewards($UserID);
$donationHistroy = $donation->history($UserID);
$DonorRewards = $donation->rewards($UserID);
$EnabledRewards = $donation->enabledRewards($UserID);

$FA_Key = null;

$DB->query("
		SELECT
			m.Username,
			m.Email,
			m.LastAccess,
			m.IP,
			p.Level AS Class,
			m.Uploaded,
			m.Downloaded,
			m.BonusPoints,
			m.RequiredRatio,
			m.Enabled,
			m.Paranoia,
			m.Invites,
			m.Title,
			m.torrent_pass,
			m.can_leech,
			i.JoinDate,
			i.Info,
			i.Avatar,
			m.FLTokens,
			i.Donor,
			i.Found,
			i.Warned,
			COUNT(DISTINCT posts.id) AS ForumPosts,
			i.Inviter,
			i.DisableInvites,
			inviter.username,
			i.InfoTitle,
            m.BonusUploaded,
            m.TotalUploads,
            COUNT(DISTINCT t.ID) AS Uploads,
		    i.RatioWatchEnds,
            i.AdminComment
		FROM users_main AS m
			JOIN users_info AS i ON i.UserID = m.ID
			LEFT JOIN permissions AS p ON p.ID = m.PermissionID
			LEFT JOIN users_main AS inviter ON i.Inviter = inviter.ID
			LEFT JOIN forums_posts AS posts ON posts.AuthorID = m.ID
            LEFT JOIN torrents AS t ON t.UserID = m.ID
		WHERE m.ID = $UserID
		GROUP BY AuthorID");

if (!$DB->has_results()) { // If user doesn't exist
    header("Location: log.php?search=User+$UserID");
}

list(
    $Username,
    $Email,
    $LastAccess,
    $IP,
    $Class,
    $Uploaded,
    $Downloaded,
    $BonusPoints,
    $RequiredRatio,
    $Enabled,
    $Paranoia,
    $Invites,
    $CustomTitle,
    $torrent_pass,
    $DisableLeech,
    $JoinDate,
    $Info,
    $Avatar,
    $FLTokens,
    $Donor,
    $Found,
    $Warned,
    $ForumPosts,
    $InviterID,
    $DisableInvites,
    $InviterName,
    $InfoTitle,
    $BonusUploaded,
    $TotalUploads,
    $Uploads,
    $RatioWatchEnds,
    $AdminComment
) = $DB->next_record(MYSQLI_NUM, array(10, 12));


$DB->query("SELECT (SELECT IFNULL(sum(`Downloaded`), 0) FROM `users_freeleeches` WHERE `UserID`=$UserID)+(SELECT IFNULL(sum(`Downloaded`), 0) FROM `users_freetorrents` where `UserID`=$UserID)");
list($AllDownloaded) = $DB->next_record();

$AllDownloaded += $Downloaded;

$CanViewUploads = check_perms("users_view_uploaded");
if (check_paranoia_here('uploads+') || $CanViewUploads) {
    $DB->query("SELECT COUNT(ID) FROM torrents WHERE UserID = '$UserID'");
    list($Uploads) = $DB->next_record();
} else {
    $Uploads = 0;
}

$DB->query("
SELECT
	IFNULL(SUM(t.Size / (1024 * 1024 * 1024) * 1 *(
		0.025 + (
			(0.06 * LN(1 + (xfh.seedtime / (24)))) / (POW(GREATEST(t.Seeders, 1), 0.6))
		)
	)),0)
FROM
	(SELECT DISTINCT uid,fid FROM xbt_files_users WHERE active=1 AND remaining=0 AND mtime > unix_timestamp(NOW() - INTERVAL 1 HOUR) AND uid = {$UserID}) AS xfu
	JOIN xbt_files_history AS xfh ON xfh.uid = xfu.uid AND xfh.fid = xfu.fid
	JOIN torrents AS t ON t.ID = xfu.fid
WHERE
	xfu.uid = {$UserID}");
list($BonusPointsPerHour) = $DB->next_record(MYSQLI_NUM);

// Image proxy CTs
$DisplayCustomTitle = $CustomTitle;
if (check_perms('site_proxy_images') && !empty($CustomTitle)) {
    $DisplayCustomTitle = preg_replace_callback(
        '~src=("?)(http.+?)(["\s>])~',
        function ($Matches) {
            return 'src=' . $Matches[1] . ImageTools::process($Matches[2]) . $Matches[3];
        },
        $CustomTitle
    );
}

if ($Preview == 1) {
    if (strlen($ParanoiaString) == 0) {
        $Paranoia = array();
    } else {
        $Paranoia = $CustomParanoia;
    }
} else {
    $Paranoia = unserialize($Paranoia);
    if (!is_array($Paranoia)) {
        $Paranoia = array();
    }
}
$ParanoiaLevel = 0;
foreach ($Paranoia as $P) {
    $ParanoiaLevel++;
    if (strpos($P, '+') !== false) {
        $ParanoiaLevel++;
    }
}

$JoinDateHtml = time_diff($JoinDate);
$LastAccessHtml = time_diff($LastAccess);

function check_paranoia_here($Setting) {
    global $Paranoia, $Class, $UserID, $Preview;
    if ($Preview == 1) {
        return check_paranoia($Setting, $Paranoia, $Class);
    } else {
        return check_paranoia($Setting, $Paranoia, $Class, $UserID);
    }
}


if (check_paranoia_here('artistsadded')) {
    $DB->query("
		SELECT COUNT(ArtistID)
		FROM torrents_artists
		WHERE UserID = $UserID");
    list($ArtistsAdded) = $DB->next_record();
} else {
    $ArtistsAdded = 0;
}

if (check_paranoia_here('requestsvoted_count') || check_paranoia_here('requestsvoted_bounty')) {
    $DB->query("
		SELECT COUNT(RequestID), SUM(Bounty)
		FROM requests_votes
		WHERE UserID = $UserID");
    list($RequestsVoted, $TotalSpent) = $DB->next_record();
    $DB->query("
		SELECT COUNT(r.ID), SUM(rv.Bounty)
		FROM requests AS r
			LEFT JOIN requests_votes AS rv ON rv.RequestID = r.ID AND rv.UserID = r.UserID
		WHERE r.UserID = $UserID");
    list($RequestsCreated, $RequestsCreatedSpent) = $DB->next_record();
} else {
    $RequestsVoted = $TotalSpent = $RequestsCreated = $RequestsCreatedSpent = 0;
}


if (check_paranoia_here('requestsfilled_count') || check_paranoia_here('requestsfilled_bounty')) {
    $DB->query("
		SELECT
			COUNT(DISTINCT r.ID),
			SUM(rv.Bounty)
		FROM requests AS r
			LEFT JOIN requests_votes AS rv ON r.ID = rv.RequestID
		WHERE r.FillerID = $UserID");
    list($RequestsFilled, $TotalBounty) = $DB->next_record();
} else {
    $RequestsFilled = $TotalBounty = 0;
}

//Do the ranks
$UploadedRank = UserRank::get_rank('uploaded', $Uploaded);
$DownloadedRank = UserRank::get_rank('downloaded', $Downloaded);
$UploadsRank = UserRank::get_rank('uploads', $Uploads);
$RequestRank = UserRank::get_rank('requests', $RequestsFilled);
$PostRank = UserRank::get_rank('posts', $ForumPosts);
$BountyRank = UserRank::get_rank('bounty', $TotalSpent);
$ArtistsRank = UserRank::get_rank('artists', $ArtistsAdded);

if ($Downloaded == 0) {
    $Ratio = 1;
} elseif ($Uploaded == 0) {
    $Ratio = 0.5;
} else {
    $Ratio = round($Uploaded / $Downloaded, 2);
}

$OverallRank = UserRank::overall_score($UploadedRank, $DownloadedRank, $UploadsRank, $RequestRank, $PostRank, $BountyRank, $ArtistsRank, $Ratio);

$DB->query("select count(1) from thumb where ToUserID = $UserID");
list($ThumbCount) = $DB->next_record();

$ReleaseGroup = Users::get_release_group($UserID);

$IsFriend = false;
if (!$OwnProfile) {
    $DB->query("SELECT FriendID
		FROM friends
		WHERE UserID = '$LoggedUser[ID]'
			AND FriendID = '$UserID'");
    if ($DB->has_results()) {
        $IsFriend = true;
    }
}

View::show_header($Username, "jquery.imagesloaded,jquery.wookmark,user,bbcode,comments,info_paster,tiles", "PageUserShow");

?>
<div class="LayoutBody">
    <div class="BodyHeader">
        <h2 class="BodyHeader-nav"><?= Users::format_username($UserID, true, true, true, false, true, false, true) ?>
            <span class="floatright" id="thumb"><?= icon("Common/like") ?><?= $ThumbCount ? ' ' . $ThumbCount : '' ?></span>
        </h2>
        <?
        if (!empty($ReleaseGroups)) {
        ?>
            <div class="BodyHeader-subNav">
                <?= t('server.user.member_of_group', ['Values' => [$ReleaseGroups[0]['Name']]]) // temp select first 
                ?>
            </div>
        <?
        }
        ?>
    </div>
    <div class="BodyNavLinks">
        <? if (!$OwnProfile) { ?>
            <a href="inbox.php?action=compose&amp;to=<?= $UserID ?>"><?= t('server.user.compose') ?></a>
            <?
            if (!$IsFriend) {
            ?>
                <a href="friends.php?action=add&amp;friendid=<?= $UserID ?>&amp;auth=<?= $LoggedUser['AuthKey'] ?>"><?= t('server.user.add_friend') ?></a>
            <?  } ?>
            <a href="reports.php?action=report&amp;type=user&amp;id=<?= $UserID ?>"><?= t('server.user.report') ?></a>
        <?
        }
        if (check_perms('users_edit_profiles', $Class) || $LoggedUser['ID'] == $UserID) {
        ?>
            <a href="user.php?action=edit&amp;userid=<?= $UserID ?>"><?= t('server.user.setting') ?></a>
        <?
        }

        if (check_perms('admin_manage_permissions', $Class)) {
        ?>
            <a href="user.php?action=permissions&amp;userid=<?= $UserID ?>"><?= t('server.user.permissions') ?></a>
        <?
        }

        if (check_perms('users_view_ips', $Class)) { ?>
            <a href="user.php?action=sessions&amp;userid=<?= $UserID ?>"><?= t('server.user.sessions') ?></a>
            <a href="userhistory.php?action=copypaste&amp;userid=<?= $UserID ?>"><?= t('server.user.copypaste') ?></a>
        <?
        }

        if (check_perms('admin_reports', $Class)) { ?>
            <a href="reportsv2.php?view=reporter&amp;id=<?= $UserID ?>"><?= t('server.user.reporter') ?></a>
        <?
        }
        if (check_perms('admin_clear_cache', $Class)) { ?>
            <a href="user.php?action=clearcache&amp;id=<?= $UserID ?>"><?= t('server.user.clearcache') ?></a>
        <?
        }
        if (check_perms('users_mod', $Class)) { ?>
            <a href="user.php?action=linked_account&amp;id=<?= $UserID ?>"><?= t('server.user.linked_account')  ?></a>
            <a href="user.php?action=staff_tool&amp;id=<?= $UserID ?>"><?= t('server.user.staff_tools') ?></a>
        <?
        }
        ?>

    </div>
    <div class="LayoutMainSidebar">
        <div class="Sidebar LayoutMainSidebar-sidebar">
            <?
            if ($Avatar && Users::has_avatars_enabled()) {
            ?>
                <div class="SidebarItemUserAvatar SidebarItem Box">
                    <?= Users::show_avatar($Avatar, $UserID, $Username, $HeavyInfo['DisableAvatars'], 260) ?>
                </div>
            <?
            }
            if ($Enabled == 1 && (count($FL_Items))) {
            ?>
                <div class="SidebarItemUserFreeTokens SidebarItem Box">
                    <div class="SidebarItem-header Box-header">
                        <?= t('server.user.send_fltoken') ?></div>
                    <div class="SidebarItem-body Box-body fl_form" name="user" id="fl_form" action="user.php?id=<?= $UserID ?>" method="post">
                        <ul class="SidebarList SidebarItem-body Box-body">
                            <?
                            foreach ($FL_Items as $data) {
                                $label_title = t('server.user.this_costs', ['Values' => [$data['Price'], $data['After']]]);
                            ?>
                                <li class="SidebarList-item">
                                    <input type="radio" name="fltype" id="fl-<?= $data['Label'] ?>" value="fl-<?= $data['Label'] ?>" />
                                    <label data-tooltip="<?= $label_title ?>" for="fl-<?= $data['Label'] ?>"> <?= $data['Name'] ?></label>
                                </li>
                            <?
                            }
                            ?>
                            <li class="SidebarList-item"><input class="Button" type="button" name="flsubmit" onclick="sendTokens(<?= $UserID ?>, '<?= $LoggedUser['AuthKey'] ?>')" value="Send" /></li>
                        </ul>
                    </div>
                </div>
            <? } ?>
            <?
            $UserInfo = Users::user_info($UserID);
            ?>
            <div class="SidebarItemStats SidebarItem Box">
                <div class="SidebarItem-header Box-header"><?= t('server.user.statistics') ?></div>
                <ul class="SidebarList SidebarItem-body Box-body">

                    <li class="SidebarList-item isClass" id="class-value" data-value="<?= $ClassLevels[$Class]['Name'] ?>">
                        <span><?= t('server.user.p_class') ?>: <?= $ClassLevels[$Class]['Name'] ?></span>
                    </li>
                    <?
                    if (!empty($UserInfo['ExtraClasses'])) {
                        $ClassItems = [];
                        foreach ($UserInfo['ExtraClasses'] as $PermID => $Val) {
                            $ClassItems[] = '' . $Classes[$PermID]['Name'] . '';
                        }
                    ?>
                        <li class="SidebarList-item isClass" id="class-value" data-value="<?= $ClassLevels[$Class]['Name'] ?>">
                            <span><?= t('server.tools.secondary_class') ?>:
                                <span><?= implode(', ', $ClassItems); ?></span>
                            </span>
                        </li>
                    <?
                    }
                    if (($Override = check_paranoia_here('uploaded'))) {
                    ?>
                        <li class="SidebarList-item is-uploaded <?= $Override === 2 ? 'paranoia_override' : '' ?>" id="uploaded-value" data-value="<?= $Uploaded ?>">
                            <span><?= t('server.user.uploaded') ?>: </span>
                            <span data-tooltip="<?= t('server.user.uploaded') . ': ' . Format::get_size($Uploaded) . (empty($UploadsRank) ? '' : '(' . t('server.user.all_site') . ' ' . number_format($UploadedRank) . '%)') . (check_paranoia_here('uploaded+') ? ', ' .   t('server.user.true_uploaded_title')  . ': ' .  Format::get_size($Uploaded - $BonusUploaded) : '') ?>">
                                <?= Format::get_size($Uploaded) ?>
                            </span>
                        </li>
                    <?

                    }
                    if (($Override = check_paranoia_here('downloaded'))) {

                    ?>
                        <li class="SidebarList-item is-downloaded <?= $Override === 2 ? 'paranoia_override' : '' ?>" id="downloaded-value" data-value="<?= $Downloaded ?>">
                            <span><?= t('server.user.downloaded') ?>: </span>
                            <span data-tooltip="<?= t('server.user.downloaded') . ': ' . Format::get_size($Downloaded) . (empty($DownloadedRank) ? '' : '(' . t('server.user.all_site') . ' ' . number_format($DownloadedRank) . '%)') .  (check_paranoia_here('downloaded+') ?  ', ' . t('server.user.true_downloaded_title') . ': ' . Format::get_size($AllDownloaded) : '') ?>">
                                <?= Format::get_size($Downloaded) ?>
                            </span>
                        </li>
                    <?
                    }
                    if (($Override = check_paranoia_here('ratio'))) {
                        $Ratio = Format::get_ratio_html($Uploaded, $Downloaded);
                    ?>
                        <li class="SidebarList-item is-ratio <?= $Override === 2 ? 'paranoia_override' : '' ?>" id="ratio-value" data-value="<?= Format::get_ratio($Uploaded, $Downloaded) ?>">
                            <span><?= t('server.user.ratio') ?>: </span>
                            <?
                            $Tooltip = t('server.user.ratio')  . ': ' .  Format::get_ratio($Uploaded, $Downloaded);
                            if (($Override = check_paranoia_here('requiredratio')) && isset($RequiredRatio)) {
                                $Tooltip .= ', ' .  t('server.user.required_ratio') . ': ' . number_format((float)$RequiredRatio, 2);
                            }
                            ?>
                            <span data-tooltip="<?= $Tooltip ?>"><?= Format::get_ratio($Uploaded, $Downloaded) ?></span>
                        </li>
                    <?
                    }
                    if ($Override = check_paranoia_here('bonuspoints')) {
                    ?>

                        <? if (($Override = check_paranoia_here('requestsvoted_bounty'))) { ?>
                            <li class="SidebarList-item is-bountySpentRank <?= $OverrideClass ?>" id="bounty-spent-rank-value" data-value="<?= $BountyRank ?>">
                                <span><?= t('server.user.u_bounty') ?>: </span>
                                <span data-tooltip="<?= Format::get_size($TotalSpent) ?>">
                                    <?= $BountyRank === false ? 'Server busy' : number_format($BountyRank) . '%' ?>
                                </span>
                            </li>
                        <? } ?>
                        <li class="SidebarList-item is-bp <?= $Override === 2 ? 'paranoia_override' : '' ?>" id="bp-value" data-value="<?= $BonusPoints ?>">
                            <span><?= t('server.user.bonus_points') ?>: </span>
                            <?
                            $Tooltip = t('server.user.bonus_points') . ': ' . number_format($BonusPoints) . (check_paranoia_here('bonuspoints+') ? ', ' . t('server.user.bprates') . ': ' . number_format($BonusPointsPerHour) : '')
                            ?>
                            <span data-tooltip="<?= $Tooltip  ?>"><?= number_format($BonusPoints) ?></span>
                            <?

                            $BonusAction = [];
                            $FormatedBonusPoints = number_format($BonusPoints);
                            if ($OwnProfile) {
                                $BonusAction[] = "<a href='bonus.php?action=bprates'>" . t('server.user.bprates') . "</a>";
                            } else if (check_perms('users_mod')) {
                                $BonusAction[] = "<a href='bonus.php?action=bprates&userid=$UserID'>" . t('server.user.bprates') . "</a>";
                            }
                            if (check_perms('admin_bp_history')) {
                                $BonusAction[] = "<a href='bonus.php?action=history&id=$UserID'>" . t('server.user.consumed_history') . "</a>";
                            }
                            if (count($BonusAction) > 0) {
                            ?>
                                [<?= implode('|', $BonusAction) ?>]
                            <?
                            }
                            ?>
                        </li>
                    <?
                    }
                    if ($OwnProfile || ($Override = check_paranoia_here('token'))) {
                    ?>
                        <?
                        $Title = null;
                        $DB->query("select count(*), EndTime from tokens_typed where UserID=" . $UserID . " and Type='time' group by EndTime");
                        $TimeAndCnts = $DB->to_array(false, MYSQLI_NUM, false);
                        $num = 0;
                        foreach ($TimeAndCnts as $TAC) {
                            if ($num != 0) $Title = "$Title\n";
                            $num += $TAC[0];
                            $Title = $Title . $TAC[0] . " (" . $TAC[1] . t('server.user.space_expired');
                        }
                        $TokensDisplay = $num == 0 ? number_format($FLTokens) : number_format($FLTokens - $num) . '+' . number_format($num);
                        $Title = 'null';

                        ?>
                        <li class="SidebarList-item is-flTokenCount <?= $Override === 2 ? 'paranoia_override' : '' ?>" id="fl-token-count-value" data-value="<?= $FLTokens ?>" data-tooltip>
                            <?= t('server.user.token_number') ?>
                            <span>: </span>
                            <?= $TokensDisplay ?>
                            [<a href="userhistory.php?action=token_history&amp;userid=<?= $UserID ?>">
                                <?= t('server.user.consumed_history') ?>
                            </a>]
                        </li>
                    <?
                    }
                    if (check_perms('users_view_invites') || $OwnProfile) {
                        $DB->query("select count(*), EndTime from invites_typed where UserID=" . $UserID . " and Type='time' and Used=0 group by EndTime");
                        $TimeAndCnts = $DB->to_array(false, MYSQLI_NUM, false);
                        $num = 0;
                        foreach ($TimeAndCnts as $TAC) {
                            $num += $TAC[0];
                        }
                    ?>
                        <li class="SidebarList-item">
                            <span><?= t('server.user.p_invites') ?>: </span>
                            <?
                            if ($DisableInvites) {
                                echo 'X';
                            } else {
                                echo $num == 0 ? number_format($Invites) : number_format($Invites - $num) . '+' . number_format($num);
                            }
                            ?>
                            [<span><a href="<?= 'user.php?action=invite&userid=' . $UserID ?>"><?= t('server.index.details') ?></a></span>]
                        </li>
                    <?
                    }
                    if (($Override = check_paranoia_here('lastseen'))) {
                    ?>
                        <li class="SidebarList-item is-lastAccessDate <?= $Override === 2 ? 'paranoia_override' : '' ?>" id="last-access-date-value" data-value="<?= (new DateTime($LastAccess))->format(DateTime::ATOM)  ?>">
                            <span><?= t('server.user.lastaccess') ?>: </span>
                            <?= $LastAccessHtml ?>
                        </li>
                    <?
                    }
                    if (($OwnProfile || check_perms('users_mod')) && $Warned != '0000-00-00 00:00:00') { ?>
                        <li class="SidebarList-item is-warnedExpireDate" id="warned-expire-date-value" data-value="<?= (new DateTime($Warned))->format(DateTime::ATOM) ?>">
                            <span><?= t('server.user.warning_expires_in') ?>: </span>
                            <?= time_diff((date('Y-m-d H:i', strtotime($Warned)))) ?>
                        </li>
                    <?
                    }
                    ?>
                </ul>
            </div>
            <?
            if (!isset($SupportFor)) {
                $DB->query('SELECT SupportFor FROM users_info WHERE UserID = ' . $LoggedUser['ID']);
                list($SupportFor) = $DB->next_record();
            }
            if (
                check_perms('users_mod', $Class) ||
                check_perms('users_view_invites', $Class) ||
                check_perms('users_view_ips', $Class) ||
                check_perms('users_view_keys', $Class) ||
                (check_perms('users_view_email', $Class) && in_array("emailshowtotc", $Paranoia)) ||
                check_perms("users_override_paranoia", $Class) ||
                !empty($SupportFor) ||
                $OwnProfile
            ) {
            ?>
                <div class="SidebarItemUserPersonal SidebarItem Box">
                    <div class="SidebarItem-header Box-header">
                        <?= t('server.user.p_personal') ?></div>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <?
                        if (check_perms('users_mod', $Class) || $OwnProfile) {
                        ?>
                            <li class="SidebarList-item is-joinDate" id="join-date-value" data-value="<?= (new DateTime($JoinDate))->format(DateTime::ATOM) ?>">
                                <span><?= t('server.user.joineddate') ?>: </span>
                                <?= $JoinDateHtml ?>
                            </li>
                        <?
                        }
                        if (check_perms('users_view_invites', $Class) || $OwnProfile) {
                            if (!$InviterID) {
                                $Invited = '<span>Nobody</span>';
                            } else {
                                $Invited = "<a href=\"user.php?id=$InviterID\">$InviterName</a>";
                            } ?>

                            <li class="SidebarList-item isInviter" id="inviter-value" data-value="<?= $InviterName ?>">
                                <span><?= t('server.user.p_inviter') ?>: </span>
                                <?= $Invited ?>
                            </li>
                        <?
                        }

                        if ((check_perms('users_view_email', $Class) && in_array("emailshowtotc", $Paranoia)) || check_perms("users_override_paranoia", $Class) || $OwnProfile) { ?>
                            <li class="SidebarList-item"><?= t('server.user.p_email') ?>: <a href="mailto:<?= display_str($Email) ?>"><?= display_str($Email) ?></a>
                                <? if (check_perms('users_view_email', $Class)) { ?>
                                    [<a href="user.php?action=search&amp;email_history=on&amp;email=<?= display_str($Email) ?>" data-tooltip="Search">S</a>]
                                <? } ?>
                            </li>
                        <?
                        }
                        if (check_perms('users_view_ips', $Class)) {
                        ?>
                            <li class="SidebarList-item"><?= t('server.user.p_ip') ?>: <?= Tools::display_ip($IP) ?></li>
                            <li class="SidebarList-item"><?= t('server.user.p_host') ?>: <?= Tools::get_host_by_ajax($IP) ?></li>
                        <?
                        } else if ($OwnProfile) {
                        ?>
                            <li class="SidebarList-item"><?= t('server.user.p_ip') ?>: <?= $IP ?></li>
                        <?
                        }
                        if (check_perms('users_view_keys', $Class) || $OwnProfile) { ?>
                            <li class="SidebarList-item"><?= t('server.user.p_passkey') ?>:
                                <a href="#" id="passkey" onclick="globalapp.toggleAny(event, '#passkey_value');return false;">
                                    <span class="u-toggleAny-show "><?= t('server.common.show') ?></span>
                                    <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                                </a>
                                <div id="passkey_value" class="u-hidden"><?= display_str($torrent_pass) ?></div>
                            </li>
                        <?
                        }

                        if ($Override = check_perms('users_mod', $Class) || $OwnProfile || !empty($SupportFor)) {
                        ?>
                            <li class="SidebarList-item <?= (($Override === 2 || $SupportFor) ? 'paranoia_override' : '') ?>">
                                <?= t('server.user.p_clients') ?>: <?
                                                                    $DB->query("SELECT DISTINCT useragent FROM xbt_files_users WHERE uid = $UserID");
                                                                    $Clients = $DB->collect(0);
                                                                    echo implode('; ', $Clients);
                                                                    ?></li>
                        <?
                        }
                        if ($OwnProfile || check_perms('users_mod', $Class)) {
                            $DB->query("SELECT MAX(uhp.ChangeTime), ui.JoinDate FROM users_info ui LEFT JOIN users_history_passwords uhp ON uhp.UserID = $UserID WHERE ui.UserID = $UserID");
                            list($PasswordHistory, $JoinDate) = G::$DB->next_record();
                            $PasswordAge = (empty($PasswordHistory)) ? time_diff($JoinDate, 2, false, false, false, true) : time_diff($PasswordHistory, 2, false, false, false, true);
                        ?>
                            <li class="SidebarList-item"><?= t('server.user.p_passwordage') ?>: <?= $PasswordAge ?></li>
                        <? }
                        /*
                    if ($OwnProfile || check_perms('users_override_paranoia', $Class)) { ?>
                        <li class="SidebarList-item"><?= t('server.user.p_irc') ?>: <?= empty($IRCKey) ? t('server.user.irc_no') : t('server.user.irc_yes') ?></li>
                    <? } 
                    */
                        ?>
                    </ul>
                </div>
            <?
            }

            $NextLevel = null;
            foreach ($UserPromoteCriteria as $Level) {
                if ($Classes[$Level['From']]['Level'] == $Class) {
                    $NextLevel = $Level;
                    break;
                }
            }
            if (isset($NextLevel) && $OwnProfile) {
            ?>
                <div class="SidebarItemUserNextClass SidebarItem Box">
                    <div class="SidebarItem-header Box-header">
                        <?= t('server.user.next_userclass') ?></div>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <li class="SidebarList-item"><?= t('server.user.next_userclass_title1') ?>: <?= $Classes[$NextLevel['To']]['Name'] ?></li>
                        <li class="SidebarList-item"><?= t('server.user.next_userclass_title6') ?>:
                            <?
                            $p = $AllDownloaded / $NextLevel['MinDownload'] * 100;
                            echo Format::get_size($AllDownloaded) . ' / ' . Format::get_size($NextLevel['MinDownload']) . " (<span class=\"" . ($p >= 100 ? "u-colorSuccess\">" : "u-colorWarning\">") . number_format($p) . "%</span>)"
                            ?>
                        </li>
                        <li class="SidebarList-item"><?= t('server.user.next_userclass_title3') ?>:
                            <?
                            $p = $Uploaded / $Downloaded / $NextLevel['MinRatio'] * 100;
                            echo number_format($Uploaded / $Downloaded, 2) . ' / ' . $NextLevel['MinRatio'] . " (<span class=\"" . ($p >= 100 ? "u-colorSuccess\">" : "u-colorWarning\">") . number_format($p) . "%</span>)"
                            ?>
                        </li>
                        <li class="SidebarList-item"><?= t('server.user.next_userclass_title4') ?>:
                            <?
                            echo time_diff($JoinDate, $Levels = 2, $Span = true, $Lowercase = false, $StartTime = false, $HideAgo = true) . ' / ' . time_diff(time_minus(3600 * 24 * 7 * $NextLevel['Weeks']), $Levels = 2, $Span = true, $Lowercase = false, $StartTime = false, $HideAgo = true)
                            ?></li>
                        <?
                        $DeadPeriod = TORRENT_DEAD_PERIOD;
                        $DB->query("SELECT COUNT(ID) FROM torrents WHERE UserID = '$UserID' and date_sub(NOW(), INTERVAL $DeadPeriod DAY) < last_action");
                        list($MinUploads) = $DB->next_record();
                        $p = $MinUploads / $NextLevel['MinUploads'] * 100;

                        echo "<li>" . t('server.user.next_userclass_title5') . ": $MinUploads / " . $NextLevel['MinUploads'];
                        echo ($NextLevel['MinUploads'] ? (" (<span class=\"" . ($p >= 100 ? "u-colorSuccess\">" : "u-colorWarning\">") . number_format($p) . "%</span>)") : "") . "</li>";
                        ?>
                    </ul>
                </div>
            <?
            }

            // if ($OwnProfile || check_perms('users_override_paranoia', $Class)) {
            //     $DB->prepared_query("SELECT IRCKey FROM users_main WHERE ID = ?", $UserID);
            //     list($IRCKey) = $DB->next_record();
            // }

            //-----------------------History-------------------------------------------------------------
            if (check_perms('users_mod', $Class) || check_perms('users_view_ips', $Class) || check_perms('users_view_keys', $Class)) {
                $DB->query("SELECT COUNT(*) FROM users_history_passwords WHERE UserID = '$UserID'");
                list($PasswordChanges) = $DB->next_record();

                if (check_perms('users_view_keys', $Class)) {
                    $DB->query("SELECT COUNT(*) FROM users_history_passkeys WHERE UserID = '$UserID'");
                    list($PasskeyChanges) = $DB->next_record();
                }
                if (check_perms('users_view_ips', $Class)) {
                    $DB->query("SELECT COUNT(DISTINCT IP) FROM users_history_ips WHERE UserID = '$UserID'");
                    list($IPChanges) = $DB->next_record();

                    $DB->query("SELECT COUNT(DISTINCT IP) FROM xbt_snatched WHERE uid = '$UserID' AND IP != ''");
                    list($TrackerIPs) = $DB->next_record();
                }
                if (check_perms('users_view_email', $Class)) {
                    $DB->query("SELECT COUNT(*) FROM users_history_emails WHERE UserID = '$UserID'");
                    list($EmailChanges) = $DB->next_record();
                }
            ?>
                <div class="SidebarItemUserHistory SidebarItem Box">
                    <div class="SidebarItem-header Box-header">
                        <?= t('server.user.history') ?></div>
                    <ul class="SidebarList SidebarItem-body Box-body">
                        <?
                        if (check_perms('users_view_email', $Class)) {
                        ?>
                            <li class="SidebarList-item">
                                <?= t('server.user.emails') ?>:
                                <a href="userhistory.php?action=email2&amp;userid=<?= $UserID ?>">
                                    <?= number_format($EmailChanges) ?>
                                </a>
                            </li>
                        <?
                        }
                        if (check_perms('users_view_ips', $Class)) {
                        ?>
                            <li class="SidebarList-item">
                                IPs:
                                <a href="userhistory.php?action=ips&amp;userid=<?= $UserID ?>">
                                    <?= number_format($IPChanges) ?>
                                </a>
                            </li>
                            <? if (check_perms('users_view_ips', $Class) && check_perms('users_mod', $Class)) { ?>
                                <li class="SidebarList-item">
                                    Tracker IPs:
                                    <a href="userhistory.php?action=tracker_ips&amp;userid=<?= $UserID ?>">
                                        <?= number_format($TrackerIPs) ?>
                                    </a>
                                </li>
                            <?
                            }
                        }
                        if (check_perms('users_view_keys', $Class)) {
                            ?>
                            <li class="SidebarList-item">
                                <?= t('server.user.passkeys') ?>:
                                <a href="userhistory.php?action=passkeys&amp;userid=<?= $UserID ?>">
                                    <?= number_format($PasskeyChanges) ?>
                                </a>
                            </li>
                        <?
                        }
                        if (check_perms('users_mod', $Class)) {
                        ?>
                            <li class="SidebarList-item">
                                <?= t('server.user.passwords') ?>:
                                <a href="userhistory.php?action=passwords&amp;userid=<?= $UserID ?>">
                                    <?= number_format($PasswordChanges) ?>
                                </a>
                            </li>
                        <?      } ?>
                    </ul>
                </div>
            <?  }
            DonationsView::render_donor_stats($OwnProfile, $donationInfo, $leaderBoardRank, $donorVisible, $isDonor);
            ?>
        </div>
        <div class="LayoutMainSidebar-main">
            <?
            if (
                $RatioWatchEnds != '0000-00-00 00:00:00'
                && (time() < strtotime($RatioWatchEnds))
                && ($Downloaded * $RequiredRatio) > $Uploaded
            ) {
                $RatioWatchText = t(
                    'server.user.ratio_watch_text',
                    [
                        'Values' =>
                        [
                            time_diff($RatioWatchEnds, 2, false),
                            Format::get_size(($Downloaded * $RequiredRatio) - $Uploaded),
                            Format::get_size($Downloaded - $RatioWatchDownload),
                        ]
                    ]
                );
            ?>
                <div class="Group" id="ratio_watch">
                    <div class="Group-headerTitle">
                        <div class="Group-header"><?= t('server.user.ratio_watch') ?></div>
                    </div>
                    <div class="Group-body"><?= $RatioWatchText ?></div>
                </div>
            <?
            }
            if (CONFIG['ENABLE_BADGE']) {
                $WearOrDisplay = Badges::get_wear_badges($UserID);
                $BadgesLabels = Badges::get_badge_labels();
                $BadgesByUserID = Badges::get_badges_by_userid($UserID);
                $MaxBadges = array();
                foreach ($BadgesByUserID as $BadgeID => $BadgeInfo) {
                    $Badge = Badges::get_badges_by_id($BadgeID);
                    if (isset($MaxBadges[$Badge['Label']])) {
                        if ($MaxBadges[$Badge['Label']]['Level'] < $Badge['Level']) {
                            $MaxBadges[$Badge['Label']] = array('ID' => $BadgeID, 'Level' => $Badge['Level']);
                        }
                    } else {
                        $MaxBadges[$Badge['Label']] = array('ID' => $BadgeID, 'Level' => $Badge['Level']);
                    }
                }
            ?>
                <div class="Group">
                    <script>
                        var i = 0;

                        function badgesDisplay() {
                            switch (i) {
                                case 0:
                                    i++
                                    $(".badge_display").show()
                                    break
                                case 1:
                                    $("#badge_display_all").hide();
                                    i++
                                    break
                                case 2:
                                    $(".badge_display").hide()
                                    i = 0
                                    break
                            }
                        }
                    </script>

                    <div class="Group-header">
                        <div class="Group-headerTitle"><?= t('server.user.badge_center') ?></div>
                        <div class="Group-headerActions">
                            <span>
                                <a href="badges.php"><?= t('server.common.see_full') ?></a>
                            </span> -
                            <span><a href="#" onclick="badgesDisplay()"><?= t('server.common.hide') ?></a></span>
                        </div>
                    </div>
                    <div id="badge_display_head" class="Group-body badge_display">
                        <?

                        foreach ($WearOrDisplay['Profile'] as $BadgeID) {
                            $Badge = Badges::get_badges_by_id($BadgeID);
                        ?>
                            <div class="badge_container">
                                <img src="<?= $Badge['BigImage'] ?>" data-tooltip="<?= Badges::get_text($Badge['Label'], "badge_name") ?>">
                            </div>

                        <?
                        }
                        if ($OwnProfile && count($WearOrDisplay['Profile']) == 0) {
                            if (count($BadgesByUserID) == 0) {
                                echo '<span>' . t('server.badges.you_do_not_have_any_badge') . '</span>';
                            } else {
                                echo '<span>' . t('server.badges.you_do_not_display_any_badge') . '</span>';
                            }
                        }
                        ?>
                    </div>
                    <div id="badge_display_all" class="Box-body badge_display" style="display: none;">
                        <?
                        if (check_paranoia_here('badgedisplay') || check_perms('users_override_paranoia')) {
                            foreach ($MaxBadges as $Label => $BadgeInfo) {
                                if (in_array($BadgeInfo['ID'], $WearOrDisplay['Profile'])) continue;
                                $Badge = Badges::get_badges_by_id($BadgeInfo['ID']);
                        ?>
                                <div class="badge_container">
                                    <img src="<?= $Badge['BigImage'] ?>" data-tooltip="<?= Badges::get_text($Label, "badge_name") ?>">
                                </div>

                        <?
                            }
                        }
                        ?>
                    </div>
                </div>
            <?
            }
            ?>

            <div class="Group">
                <div class="Group-body">
                    <div class="Post">
                        <div class="Post-header">
                            <div class="Post-headerLeft">
                                <div class="Post-headerTitle"><?= !empty($InfoTitle) ? $InfoTitle : t('server.user.infotitle'); ?>
                                </div>
                            </div>
                            <div class="Post-headerActions">
                                <a href="#" onclick="globalapp.toggleAny(event, '#profilediv');return false;">
                                    <span class="u-toggleAny-show u-hidden"><?= t('server.common.show') ?></span>
                                    <span class="u-toggleAny-hide"><?= t('server.common.hide') ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="Post-body HtmlText PostArticle profileinfo" id="profilediv">
                            <?
                            if (!$Info) {
                            ?>
                                <?= t('server.user.no_infotitle') ?>
                            <?
                            } else {
                                echo Text::full_format($Info);
                            }
                            ?>
                        </div>
                    </div>
                    <?
                    DonationsView::render_profile_rewards($EnabledRewards, $ProfileRewards);
                    ?>
                </div>
            </div>
            <?


            // community stats
            ?>
            <div class="Group" id="community_stats">
                <div class="Group-header">
                    <div class="Group-headerTitle">
                        <?= t('server.user.community') ?>
                    </div>
                </div>
                <div id="community_stats_table" class="Group-body">
                    <?
                    include(CONFIG['SERVER_ROOT'] . '/sections/user/community_stats.php');
                    ?>
                </div>
            </div>
            <?
            if (check_paranoia_here('snatched')) {
                $RecentSnatches = $Cache->get_value("recent_snatches_$UserID");
                if ($RecentSnatches === false) {
                    $DB->query("SELECT
                            t.ID AS TorrentID,
        				    g.ID,
        				    g.Name,
        				    g.WikiImage,
                            g.SubName,
                            g.Year,
                            g.IMDBRating,
                            GROUP_CONCAT(DISTINCT tags.Name ORDER BY `TagID` SEPARATOR ' ') as TagList
        			    FROM xbt_snatched AS s
        			    	INNER JOIN torrents AS t ON t.ID = s.fid
        			    	INNER JOIN torrents_group AS g ON t.GroupID = g.ID
                            LEFT JOIN torrents_tags AS tt ON tt.GroupID = g.ID
                            LEFT JOIN tags ON tags.ID = tt.TagID
        			    WHERE s.uid = '$UserID'
        			    	AND t.UserID != '$UserID'
        			    	AND g.CategoryID = '1'
        			    	AND g.WikiImage != ''
        			    GROUP BY t.ID
        			    ORDER BY s.tstamp DESC
        			    LIMIT 5");
                    $RecentSnatches = $DB->to_array();
                    $Cache->cache_value("recent_snatches_$UserID", $RecentSnatches, 86400); //inf cache
                }
                if (!empty($RecentSnatches)) {
            ?>
                    <div class="Group" id="recent_snatches">
                        <div class="Group-header">
                            <div class="Group-headerTitle">
                                <?= t('server.user.last_torrents') ?>
                            </div>
                        </div>
                        <div class="Group-body">
                            <?
                            $tableRender = new TorrentGroupCoverTableView($RecentSnatches);
                            $tableRender->render(['UseTorrentID' => true]);
                            ?>
                        </div>
                    </div>
                <?
                }
            }

            if (check_paranoia_here('uploads')) {
                $RecentUploads = $Cache->get_value("recent_uploads_$UserID");
                if ($RecentUploads === false) {
                    $DB->query("SELECT
                            t.ID AS TorrentID,
            				g.ID,
            				g.Name,
                            g.SubName,
                            g.Year,
            				g.WikiImage,
                            g.IMDBRating,
                            GROUP_CONCAT(DISTINCT tags.Name ORDER BY `TagID` SEPARATOR ' ') as TagList
            			FROM torrents_group AS g
            				INNER JOIN torrents AS t ON t.GroupID = g.ID
                            LEFT JOIN torrents_tags AS tt ON tt.GroupID = g.ID
                            LEFT JOIN tags ON tags.ID = tt.TagID
            			WHERE t.UserID = '$UserID'
            				AND g.CategoryID = '1'
            				AND g.WikiImage != ''
            			GROUP BY t.ID
            			ORDER BY t.Time DESC
            			LIMIT 5");
                    $RecentUploads = $DB->to_array();
                    $Cache->cache_value("recent_uploads_$UserID", $RecentUploads, 86400); //inf cache
                }
                if (!empty($RecentUploads)) {
                ?>
                    <div class="Group" id="recent_uploads">
                        <div class="Group-header">
                            <div class="Group-headerTitle">
                                <?= t('server.user.last_uploads') ?>
                            </div>
                        </div>
                        <div class="Group-body">
                            <?
                            $tableRender = new TorrentGroupCoverTableView($RecentUploads);
                            $tableRender->render(['UseTorrentID' => true]);
                            ?>
                        </div>
                    </div>
                <?
                }
            }
            $DB->query("SELECT ID, Name FROM collages WHERE UserID = '$UserID' AND CategoryID = '$PersonalCollageCategoryCat' AND Deleted = '0' ORDER BY Featured DESC, Name ASC");
            $Collages = $DB->to_array(false, MYSQLI_NUM, false);
            $FirstCol = true;
            foreach ($Collages as $CollageInfo) {
                list($CollageID, $CName) = $CollageInfo;
                $DB->query("SELECT ct.GroupID FROM collages_torrents AS ct WHERE ct.CollageID = '$CollageID' ORDER BY ct.Sort LIMIT 5");
                $GroupIDs = $DB->collect('GroupID');
                if (count($GroupIDs) == 0) {
                    continue;
                }
                $Groups = Torrents::get_groups($GroupIDs, true, false, false);
                ?>
                <div class="Group" id="collage<?= $CollageID ?>_box">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <?= $CName ?>
                        </div>
                        <div class="Group-headerActions">
                            <span>
                                <a href="collages.php?id=<?= $CollageID ?>"><?= t('server.common.see_full') ?></a>
                            </span> - <span>
                                <a href="#" onclick="globalapp.toggleAny(event, '#collage<?= $CollageID ?>_box .Group-body');return false;">
                                    <span class="u-toggleAny-show <?= $FirstCol ? 'u-hidden' : '' ?>"><?= t('server.common.show') ?></span>
                                    <span class="u-toggleAny-hide <?= $FirstCol ? '' : 'u-hidden' ?>"><?= t('server.common.hide') ?></span>
                                </a>
                            </span>
                        </div>
                    </div>
                    <div class="Group-body <?= $FirstCol ? '' : 'u-hidden' ?>">
                        <?
                        $tableRender = new TorrentGroupCoverTableView($Groups);
                        $tableRender->render();
                        ?>
                    </div>
                </div>
                <?
                $FirstCol = false;
            }
            if (empty($LoggedUser['DisableRequests']) && check_paranoia_here('requestsvoted_list')) {
                $SphQL = new SphinxqlQuery();
                $SphQLResult = $SphQL->select('id, votes, bounty')
                    ->from('requests, requests_delta')
                    ->where('userid', $UserID)
                    ->where('torrentid', 0)
                    ->order_by('votes', 'desc')
                    ->order_by('bounty', 'desc')
                    ->limit(0, 100, 100) // Limit to 100 requests
                    ->query();
                if ($SphQLResult->has_results()) {
                    $SphRequests = $SphQLResult->to_array('id', MYSQLI_ASSOC);
                ?>
                    <div class="Group" id="requests_box">
                        <div class="Group-header">
                            <div class="Group-headerTitle">
                                <?= t('server.common.requests') ?>
                            </div>
                            <div class="Group-headerActions">
                                <a href="#" onclick="globalapp.toggleAny(event, '#requests');return false;">
                                    <span class="u-toggleAny-show"><?= t('server.common.show') ?></span>
                                    <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="Group-body TableCotainer u-hidden" id="requests">
                            <table class="Table TableRequest" cellpadding="6" cellspacing="1" border="0" width="100%">
                                <tr class="Table-rowHeader">
                                    <td class="Table-cell">
                                        <?= t('server.user.name') ?>
                                    </td>
                                    <td class="Table-cell">
                                        <?= t('server.requests.request_type') ?>
                                    </td>
                                    <td class="Table-cell TableRequest-cellValue">
                                        <?= t('server.user.vote') ?>
                                    </td>
                                    <td class="Table-cell TableRequest-cellValue">
                                        <?= t('server.user.bounty') ?>
                                    </td>
                                    <td class="Table-cell TableRequest-cellValue">
                                        <?= t('server.user.add_time') ?>
                                    </td>
                                </tr>
                                <?
                                $Requests = Requests::get_requests(array_keys($SphRequests));
                                foreach ($SphRequests as $RequestID => $SphRequest) {
                                    $Request = $Requests[$RequestID];
                                    $RequestType = $Request['RequestType'];

                                    $RequestVotes = Requests::get_votes_array($Request['ID']);
                                    $RequestID = $Request['ID'];
                                    $RequestName = Torrents::group_name($Request, false);
                                    $FullName = "<a href=\"requests.php?action=view&amp;id=$RequestID\">$RequestName</a>";

                                ?>
                                    <tr class="Table-row">
                                        <td class="TableRequest-cellName Table-cell">
                                            <?= $FullName ?>
                                            <div class="torrent_info">
                                                <?
                                                if ($RequestType == 2) {
                                                ?>
                                                    <a href="<?= $Request['SourceTorrent'] ?>"><?= str_replace('|', ', ', $Request['CodecList']) . ' / ' . str_replace('|', ', ', $Request['SourceList']) . ' / ' . str_replace('|', ', ', $Request['ResolutionList']) . ' / ' . str_replace('|', ', ', $Request['ContainerList']) ?></a>
                                                <?
                                                } else {
                                                ?>
                                                    <?= str_replace('|', ', ', $Request['CodecList']) . ' / ' . str_replace('|', ', ', $Request['SourceList']) . ' / ' . str_replace('|', ', ', $Request['ResolutionList']) . ' / ' . str_replace('|', ', ', $Request['ContainerList']) ?>
                                                <?
                                                }
                                                ?>
                                            </div>
                                        </td>
                                        <td class="TableRequest-cellType Table-cell">
                                            <?= $RequestType  == 2 ? t('server.requests.seed_torrent') : t('server.requests.new_torrent') ?>
                                        </td>
                                        <td class="TableRequest-cellVotes Table-cell TableRequest-cellValue">
                                            <span id="vote_count_<?= $Request['ID'] ?>"><?= count($RequestVotes['Voters']) ?></span>
                                            <? if (check_perms('site_vote')) { ?>
                                                &nbsp;&nbsp; <a href="javascript:globalapp.requestVote(0, <?= $Request['ID'] ?>)" class="brackets">+</a>
                                            <?          } ?>
                                        </td>
                                        <td class="TableRequest-cellBounty Table-cell TableRequest-cellValue">
                                            <?= Format::get_size($RequestVotes['TotalBounty']) ?>
                                        </td>
                                        <td class="TableRequest-cellCreatedAt TableRequest-cellValue Table-cell">
                                            <?= time_diff($Request['TimeAdded'], 1) ?>
                                        </td>
                                    </tr>
                                <?  } ?>
                            </table>
                        </div>
                    </div>
                <?
                }
            }

            if ((check_perms('users_view_invites')) && $Invited > 0) {
                include(CONFIG['SERVER_ROOT'] . '/classes/invite_tree.class.php');
                $Tree = new INVITE_TREE($UserID, array('visible' => false));
                ?>
                <div class="Group" id="invitetree_box">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <?= t('server.user.invite_tree') ?>
                        </div>
                        <div class="Group-headerActions">
                            <a href="#" onclick="globalapp.toggleAny(event, '#invitetree');return false;">
                                <span class="u-toggleAny-show"><?= t('server.common.show') ?></span>
                                <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                            </a>
                        </div>
                    </div>
                    <div id="invitetree" class="u-hidden Group-body">
                        <? $Tree->make_tree(); ?>
                    </div>
                </div>
                <?
            }

            if (check_perms('users_mod')) {
                DonationsView::render_donation_history($donation->history($UserID));
            }
            $IsFLS = isset($LoggedUser['ExtraClasses'][CONFIG['USER_CLASS']['FLS_TEAM']]);
            if (check_perms('users_mod', $Class) || $IsFLS) {
                $UserLevel = $LoggedUser['EffectiveClass'];
                $DB->query("SELECT
            			SQL_CALC_FOUND_ROWS
            			spc.ID,
            			spc.Subject,
            			spc.Status,
            			spc.Level,
            			spc.AssignedToUser,
            			spc.Date,
            			COUNT(spm.ID) AS Resplies,
            			spc.ResolverID
            		FROM staff_pm_conversations AS spc
            		JOIN staff_pm_messages spm ON spm.ConvID = spc.ID
            		WHERE spc.UserID = $UserID
            			AND (spc.Level <= $UserLevel OR spc.AssignedToUser = '" . $LoggedUser['ID'] . "')
            		GROUP BY spc.ID
            		ORDER BY spc.Date DESC");
                if ($DB->has_results()) {
                    $StaffPMs = $DB->to_array();
                ?>
                    <div class="Group" id="staffpms_box">
                        <div class="Group-header">
                            <div class="Group-headerTitle">
                                <?= t('server.user.staff_pm') ?>
                            </div>
                            <div class="Group-headerActions">
                                <a href="#" onclick="globalapp.toggleAny(event, '#staffpms');return false;">
                                    <span class="u-toggleAny-show"><?= t('server.common.show') ?></span>
                                    <span class="u-toggleAny-hide u-hidden"><?= t('server.common.hide') ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="Group-body u-hidden" id="staffpms">
                            <table class="Table TableUserInbox">
                                <tr class="Table-rowHeader">
                                    <td class="Table-cell"><?= t('server.user.subject') ?></td>
                                    <td class="Table-cell"><?= t('server.user.date') ?></td>
                                    <td class="Table-cell"><?= t('server.user.assigned_to') ?></td>
                                    <td class="Table-cell"><?= t('server.user.replies') ?></td>
                                    <td class="Table-cell"><?= t('server.user.resolved_by') ?></td>
                                </tr>
                                <?
                                foreach ($StaffPMs as $StaffPM) {
                                    list($ID, $Subject, $Status, $Level, $AssignedToUser, $Date, $Replies, $ResolverID) = $StaffPM;
                                    // Get assigned
                                    if ($AssignedToUser == '') {
                                        // Assigned to class
                                        $Assigned = ($Level == 0) ? 'First Line Support' : $ClassLevels[$Level]['Name'];
                                        // No + on Sysops
                                        if ($Assigned != 'Sysop') {
                                            $Assigned .= '+';
                                        }
                                    } else {
                                        // Assigned to user
                                        $Assigned = Users::format_username($UserID, true, true, true, true);
                                    }

                                    if ($ResolverID) {
                                        $Resolver = Users::format_username($ResolverID, true, true, true, true);
                                    } else {
                                        $Resolver = '(unresolved)';
                                    }

                                ?>
                                    <tr class="Table-row">
                                        <td class="Table-cell"><a href="staffpm.php?action=viewconv&amp;id=<?= $ID ?>"><?= display_str($Subject) ?></a></td>
                                        <td class="Table-cell"><?= time_diff($Date, 2, true) ?></td>
                                        <td class="Table-cell"><?= $Assigned ?></td>
                                        <td class="Table-cell"><?= $Replies - 1 ?></td>
                                        <td class="Table-cell"><?= $Resolver ?></td>
                                    </tr>
                                <? } ?>
                            </table>
                        </div>
                    </div>
                <?
                }
            }

            if (check_perms('users_mod') && check_perms('users_warn', $Class)) {
                $DB->query("SELECT Comment FROM users_warnings_forums WHERE UserID = '$UserID'");
                list($ForumWarnings) = $DB->next_record();
                if ($DB->has_results()) {
                ?>
                    <div class="Post">
                        <div class="Post-header">
                            <div class="Post-headerTitle">
                                <?= t('server.user.forum_warnings') ?></div>
                        </div>
                        <div class="Post-body">
                            <div id="forumwarningslinks" class="HtmlText AdminComment" style="width: 98%;">
                                <?= Text::full_format($ForumWarnings) ?>
                            </div>
                        </div>
                    </div>
                <?
                }
            }

            if (check_perms('users_mod', $Class)) {
                ?>
                <div class="Group" id="staff_notes_box">
                    <div class="Group-header">
                        <div class="Group-headerTitle">
                            <?= t('server.user.staff_note') ?>
                        </div>
                    </div>
                    <div id="staffnotes" class="Group-body">
                        <div id="admincommentlinks" class="HtmlText AdminComment" style="width: 98%;">
                            <?= Text::full_format($AdminComment) ?>
                        </div>
                    </div>
                </div>
            <?
            }
            ?>
        </div>
    </div>
</div>
<? View::show_footer(); ?>